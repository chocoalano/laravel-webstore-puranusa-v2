<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Model Order (Pesanan).
 *
 * Pesanan pelanggan yang berisi item, pembayaran, pengiriman,
 * dan komponen bonus MLM (bv, sponsor, matching, pairing, cashback, retail).
 *
 * @property int $id
 * @property string $order_no Nomor order unik
 * @property int $customer_id Customer pemesan
 * @property string $currency Mata uang
 * @property string $status Status order (pending|PAID|shipped|delivered|cancelled)
 * @property float $subtotal_amount Subtotal
 * @property float $discount_amount Total diskon
 * @property float $shipping_amount Ongkos kirim
 * @property float $tax_amount Pajak
 * @property float $grand_total Total bayar
 * @property int|null $shipping_address_id Alamat pengiriman
 * @property int|null $billing_address_id Alamat penagihan
 * @property array|null $applied_promos Promo yang diaplikasikan (JSON)
 * @property string|null $notes Catatan order
 * @property float|null $bv_amount Business value total
 * @property float|null $sponsor_amount Komponen bonus sponsor
 * @property float|null $match_amount Komponen bonus matching
 * @property float|null $pairing_amount Komponen bonus pairing
 * @property float $retail_amount Komponen bonus retail
 * @property float|null $cashback_amount Komponen bonus cashback
 * @property float $stockist_amount Komponen bonus stockist
 * @property string $type Tipe order (planA|planB)
 * @property bool $bonus_generated Apakah bonus sudah dihitung
 * @property \Illuminate\Support\Carbon|null $processed_at
 * @property \Illuminate\Support\Carbon|null $placed_at
 * @property \Illuminate\Support\Carbon|null $paid_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Order extends BaseModel
{
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'order_no',
        'customer_id',
        'currency',
        'status',
        'subtotal_amount',
        'discount_amount',
        'shipping_amount',
        'tax_amount',
        'grand_total',
        'shipping_address_id',
        'billing_address_id',
        'applied_promos',
        'notes',
        'bv_amount',
        'sponsor_amount',
        'match_amount',
        'pairing_amount',
        'retail_amount',
        'cashback_amount',
        'stockist_amount',
        'type',
        'bonus_generated',
        'processed_at',
        'placed_at',
        'paid_at',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'subtotal_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'shipping_amount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'grand_total' => 'decimal:2',
            'bv_amount' => 'decimal:2',
            'sponsor_amount' => 'decimal:2',
            'match_amount' => 'decimal:2',
            'pairing_amount' => 'decimal:2',
            'retail_amount' => 'decimal:2',
            'cashback_amount' => 'decimal:2',
            'stockist_amount' => 'decimal:2',
            'applied_promos' => 'array',
            'bonus_generated' => 'boolean',
            'processed_at' => 'datetime',
            'placed_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    /**
     * Customer yang membuat order.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Alamat pengiriman order.
     */
    public function shippingAddress(): BelongsTo
    {
        return $this->belongsTo(CustomerAddress::class, 'shipping_address_id');
    }

    /**
     * Alamat penagihan order.
     */
    public function billingAddress(): BelongsTo
    {
        return $this->belongsTo(CustomerAddress::class, 'billing_address_id');
    }

    /**
     * Item-item dalam order.
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Pembayaran untuk order ini.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Pengiriman untuk order ini.
     */
    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class);
    }

    /**
     * Refund yang terkait order.
     */
    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class);
    }

    /**
     * Return/retur untuk order ini.
     */
    public function returns(): HasMany
    {
        return $this->hasMany(ReturnOrder::class);
    }

    /**
     * Bonus cashback yang dipicu oleh order ini.
     */
    public function bonusCashbacks(): HasMany
    {
        return $this->hasMany(CustomerBonusCashback::class);
    }
}
