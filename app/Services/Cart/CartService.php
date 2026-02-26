<?php

namespace App\Services\Cart;

use App\Models\CartItem;
use App\Models\Customer;
use App\Models\Product;
use App\Repositories\Cart\Contracts\CartRepositoryInterface;
use Illuminate\Validation\ValidationException;

class CartService
{
    public function __construct(
        protected CartRepositoryInterface $cartRepository
    ) {}

    /**
     * Tambah produk ke keranjang customer.
     * Jika produk sudah ada, qty digabung (tidak melebihi stok).
     * Jika stok habis, lempar ValidationException.
     *
     * @throws ValidationException
     */
    public function addItem(Customer $customer, Product $product, int $qty): void
    {
        if ($product->stock < 1) {
            throw ValidationException::withMessages([
                'product_id' => 'Stok produk sedang habis.',
            ]);
        }

        $cart = $this->cartRepository->findOrCreateForCustomer(
            $customer->id,
            $product->currency ?? 'IDR'
        );

        $existingItem = $this->cartRepository->findItemByProduct($cart, $product->id);

        if ($existingItem) {
            $newQty = min($existingItem->qty + $qty, $product->stock);
            $this->cartRepository->updateItemQty($existingItem, $newQty);
        } else {
            $safeQty = min($qty, $product->stock);
            $this->cartRepository->createItem($cart, $product, $safeQty);
        }

        $this->cartRepository->recalculate($cart);
    }

    /**
     * Perbarui qty item keranjang, dibatasi oleh stok produk.
     */
    public function updateQty(CartItem $item, int $qty): void
    {
        $product = $item->product;

        if ($product && $qty > $product->stock) {
            $qty = max(1, $product->stock);
        }

        $this->cartRepository->updateItemQty($item, $qty);
        $this->cartRepository->recalculate($item->cart);
    }

    /**
     * Hapus satu item dari keranjang dan hitung ulang total.
     */
    public function removeItem(CartItem $item): void
    {
        $cart = $item->cart;

        $this->cartRepository->removeItem($item);
        $this->cartRepository->recalculate($cart);
    }

    /**
     * Kosongkan semua item keranjang customer.
     */
    public function clearCart(Customer $customer): void
    {
        $cart = $this->cartRepository->findForCustomer($customer->id);

        if (! $cart) {
            return;
        }

        $this->cartRepository->clearItems($cart);
    }
}
