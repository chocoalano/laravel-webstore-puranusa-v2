<?php

namespace App\Services\Checkout;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Repositories\Checkout\Contracts\CheckoutRepositoryInterface;
use App\Services\Shipping\LionParcelService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CheckoutService
{
    public function __construct(
        protected CheckoutRepositoryInterface $checkoutRepository,
        protected LionParcelService $lionParcelService,
    ) {}

    /**
     * Data halaman checkout: items, totals, addresses, saldo, midtrans config.
     *
     * @return array{items: list<array>, cart: array<string,float>|null, addresses: list<array>, saldo: float, midtrans: array{env: string, client_key: string}}
     */
    public function getPageData(Customer $customer): array
    {
        $cart      = $this->checkoutRepository->getCartWithItems($customer->id);
        $addresses = $this->checkoutRepository->getCustomerAddresses($customer->id);

        return [
            'items'     => $cart ? $this->formatItems($cart) : [],
            'cart'      => $cart ? $this->formatCart($cart) : null,
            'addresses' => $this->formatAddresses($addresses),
            'saldo'     => (float) ($customer->ewallet_saldo ?? 0),
            'midtrans'  => [
                'env'        => config('services.midtrans.env', 'sandbox'),
                'client_key' => config('services.midtrans.client_key', ''),
            ],
        ];
    }

    /**
     * Hitung tarif pengiriman Lion Parcel berdasarkan berat dan dimensi keranjang customer.
     *
     * @return list<array{product: string, total_tariff: int, estimasi_sla: string}>
     */
    public function calculateShippingRates(Customer $customer, string $destinationDistrictLion): array
    {
        $cart = $this->checkoutRepository->getCartWithItems($customer->id);

        if (! $cart || $cart->items->isEmpty()) {
            return [];
        }

        $totalWeightGram = $cart->items->sum(
            fn (CartItem $item) => $item->qty * ($item->product?->weight_gram ?? 200)
        );

        $weightKg = max(1.0, round($totalWeightGram / 1000, 1));

        $lengthCm = (int) max(10, (int) ceil(
            $cart->items->max(fn (CartItem $item) => $item->product?->length_mm ?? 100) / 10
        ));

        $widthCm = (int) max(10, (int) ceil(
            $cart->items->max(fn (CartItem $item) => $item->product?->width_mm ?? 100) / 10
        ));

        $heightCm = (int) max(10, (int) ceil(
            $cart->items->max(fn (CartItem $item) => $item->product?->height_mm ?? 100) / 10
        ));

        return $this->lionParcelService->getRates(
            $destinationDistrictLion,
            $weightKg,
            $lengthCm,
            $widthCm,
            $heightCm,
        );
    }

    /**
     * Buat order + payment, potong saldo ewallet customer.
     *
     * @param array<string, mixed> $addressData
     * @throws ValidationException
     */
    public function payWithSaldo(Customer $customer, array $addressData): Order
    {
        $cart = $this->checkoutRepository->getCartWithItems($customer->id);
        $this->assertCartNotEmpty($cart);

        $orderType = (string) ($addressData['order_type'] ?? 'planA');
        $shippingAmount = (float) ($addressData['shipping_cost'] ?? 0);
        $total          = (float) $cart->subtotal_amount
            + $shippingAmount
            + (float) $cart->tax_amount
            - (float) $cart->discount_amount;

        if ((float) ($customer->ewallet_saldo ?? 0) < $total) {
            throw ValidationException::withMessages([
                'payment' => 'Saldo ewallet tidak mencukupi untuk membayar total pesanan.',
            ]);
        }

        $order = DB::transaction(function () use ($customer, $cart, $addressData, $total, $shippingAmount, $orderType): Order {
            $shippingAddressId = $this->resolveShippingAddressId($customer, $addressData);
            $order             = $this->buildOrder($customer, $cart, $shippingAddressId, 'processing', $shippingAmount, $orderType);

            $order->update(['paid_at' => now()]);

            Payment::create([
                'order_id'  => $order->id,
                'method_id' => PaymentMethod::where('code', 'p-002')->value('id'),
                'status'    => 'paid',
                'amount'    => $total,
                'currency'  => $cart->currency,
            ]);

            $customer->decrement('ewallet_saldo', $total);
            $this->checkoutRepository->clearCart($cart);

            return $order;
        });

        $this->syncOrderRetailAndStockistAmounts($order);
        $this->runBonusEngineForOrder($order);

        return $order;
    }

    /**
     * Buat order pending + payment pending, siapkan data untuk Midtrans Snap.
     *
     * @param array<string, mixed> $addressData
     * @return array{order: Order, cart: Cart}
     * @throws ValidationException
     */
    public function prepareMidtransOrder(Customer $customer, array $addressData): array
    {
        $cart = $this->checkoutRepository->getCartWithItems($customer->id);
        $this->assertCartNotEmpty($cart);

        $orderType = (string) ($addressData['order_type'] ?? 'planA');
        $shippingAmount = (float) ($addressData['shipping_cost'] ?? 0);

        $result = DB::transaction(function () use ($customer, $cart, $addressData, $shippingAmount, $orderType): array {
            $shippingAddressId = $this->resolveShippingAddressId($customer, $addressData);
            $order             = $this->buildOrder($customer, $cart, $shippingAddressId, 'pending', $shippingAmount, $orderType);

            $grandTotal = (float) $cart->subtotal_amount
                + $shippingAmount
                + (float) $cart->tax_amount
                - (float) $cart->discount_amount;

            Payment::create([
                'order_id'  => $order->id,
                'method_id' => PaymentMethod::where('code', 'p-001')->value('id'),
                'status'    => 'pending',
                'amount'    => $grandTotal,
                'currency'  => $cart->currency,
            ]);

            return ['order' => $order, 'cart' => $cart];
        });

        $this->syncOrderRetailAndStockistAmounts($result['order']);

        return $result;
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private function assertCartNotEmpty(?Cart $cart): void
    {
        if (! $cart || $cart->items->isEmpty()) {
            throw ValidationException::withMessages([
                'cart' => 'Keranjang belanja kosong.',
            ]);
        }
    }

    /**
     * @param array<string, mixed> $addressData
     */
    private function resolveShippingAddressId(Customer $customer, array $addressData): int
    {
        if ($addressData['address_mode'] === 'saved') {
            $addressId = (int) $addressData['address_id'];

            $isOwnedByCustomer = CustomerAddress::query()
                ->whereKey($addressId)
                ->where('customer_id', $customer->id)
                ->exists();

            if (! $isOwnedByCustomer) {
                throw ValidationException::withMessages([
                    'address_id' => 'Alamat tidak valid untuk customer saat ini.',
                ]);
            }

            return $addressId;
        }

        $provinceId = (int) ($addressData['province_id'] ?? $customer->province_id ?? 0);
        $cityId     = (int) ($addressData['city_id'] ?? $customer->city_id ?? 0);

        $address = CustomerAddress::create([
            'customer_id'     => $customer->id,
            'label'           => 'Checkout',
            'recipient_name'  => $addressData['recipient_name'],
            'recipient_phone' => $addressData['phone'],
            'address_line1'   => $addressData['address_line'],
            'province_label'  => $addressData['province'],
            'province_id'     => $provinceId,
            'city_label'      => $addressData['city'],
            'city_id'         => $cityId,
            'postal_code'     => $addressData['postal_code'] ?? null,
            'description'     => $addressData['notes'] ?? null,
            'country'         => 'Indonesia',
        ]);

        return $address->id;
    }

    private function buildOrder(
        Customer $customer,
        Cart $cart,
        int $shippingAddressId,
        string $status,
        float $shippingAmount = 0.0,
        string $orderType = 'planA',
    ): Order {
        $normalizedOrderType = in_array($orderType, ['planA', 'planB'], true) ? $orderType : 'planA';

        $grandTotal = (float) $cart->subtotal_amount
            + $shippingAmount
            + (float) $cart->tax_amount
            - (float) $cart->discount_amount;

        $order = Order::create([
            'order_no'            => 'ORD-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6)),
            'customer_id'         => $customer->id,
            'currency'            => $cart->currency,
            'status'              => $status,
            'type'                => $normalizedOrderType,
            'subtotal_amount'     => $cart->subtotal_amount,
            'discount_amount'     => $cart->discount_amount,
            'shipping_amount'     => $shippingAmount,
            'tax_amount'          => $cart->tax_amount,
            'grand_total'         => $grandTotal,
            'shipping_address_id' => $shippingAddressId,
            'placed_at'           => now(),
        ]);

        foreach ($cart->items as $item) {
            OrderItem::create([
                'order_id'    => $order->id,
                'product_id'  => $item->product_id,
                'name'        => $item->product_name,
                'sku'         => $item->product_sku,
                'qty'         => $item->qty,
                'unit_price'  => $item->unit_price,
                'row_total'   => $item->row_total,
                'weight_gram' => $item->product?->weight_gram,
                'length_mm'   => $item->product?->length_mm,
                'width_mm'    => $item->product?->width_mm,
                'height_mm'   => $item->product?->height_mm,
            ]);
        }

        return $order;
    }

    private function syncOrderRetailAndStockistAmounts(Order $order): void
    {
        try {
            DB::statement('CALL sp_accumulation_stockist_retail_amount_orders(?)', [$order->id]);
        } catch (\Throwable $exception) {
            Log::error('Failed to sync stockist and retail accumulation for order.', [
                'order_id' => $order->id,
                'order_no' => $order->order_no,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    private function runBonusEngineForOrder(Order $order): void
    {
        if ((bool) ($order->bonus_generated ?? false)) {
            return;
        }

        try {
            $this->checkoutRepository->callBonusEngine((int) $order->id);
            $this->checkoutRepository->markOrderBonusGenerated($order);
        } catch (\Throwable $exception) {
            Log::error('Failed to run bonus engine for ewallet checkout.', [
                'order_id' => $order->id,
                'order_no' => $order->order_no,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    /** @return list<array<string, mixed>> */
    private function formatItems(Cart $cart): array
    {
        return $cart->items->map(function (CartItem $item): array {
            return [
                'id'          => $item->id,
                'product_id'  => $item->product_id,
                'name'        => $item->product_name,
                'sku'         => $item->product_sku,
                'variant'     => $item->meta_json['variant'] ?? null,
                'price'       => (float) $item->unit_price,
                'qty'         => $item->qty,
                'row_total'   => (float) $item->row_total,
                'image'       => ($url = $item->product?->primaryMedia->first()?->url) ? Storage::url($url) : null,
                'weight_gram' => $item->product?->weight_gram,
            ];
        })->toArray();
    }

    /** @return array<string, float> */
    private function formatCart(Cart $cart): array
    {
        return [
            'subtotal' => (float) $cart->subtotal_amount,
            'discount' => (float) $cart->discount_amount,
            'shipping' => (float) $cart->shipping_amount,
            'tax'      => (float) $cart->tax_amount,
            'total'    => (float) $cart->grand_total,
        ];
    }

    /**
     * @param Collection<int, CustomerAddress> $addresses
     * @return list<array<string, mixed>>
     */
    private function formatAddresses(Collection $addresses): array
    {
        return $addresses->map(fn (CustomerAddress $a): array => [
            'id'             => $a->id,
            'label'          => $a->label,
            'recipient_name' => $a->recipient_name,
            'phone'          => $a->recipient_phone,
            'address_line'   => $a->address_line1,
            'address_line2'  => $a->address_line2,
            'province'       => $a->province_label,
            'province_id'    => $a->province_id,
            'city'           => $a->city_label,
            'city_id'        => $a->city_id,
            'postal_code'    => $a->postal_code,
            'description'    => $a->description,
            'is_default'     => $a->is_default,
        ])->toArray();
    }
}
