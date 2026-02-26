<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

/**
 * Model CustomerBonusPairing (Bonus Pairing / Binary).
 *
 * Bonus dari pasangan omset kaki kiri dan kanan dalam binary tree.
 * Dibatasi oleh max_daily_pairing per hari.
 *
 * @property int $id
 * @property int|null $member_id Member penerima bonus pairing
 * @property int $source_member_id Member sumber
 * @property int $pairing_count Jumlah pair yang tercapai
 * @property float $amount Nominal bonus
 * @property float $index_value Nilai index
 * @property int $status 0=pending, 1=released
 * @property string|null $description Catatan
 * @property \Illuminate\Support\Carbon|null $pairing_date Tanggal pairing
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class CustomerBonusPairing extends BaseModel
{
    use HasFactory;

    public const CACHE_KEY_OVERVIEW = 'customer_bonus_pairing_overview';

    protected static function booted(): void
    {
        $flush = fn () => Cache::forget(self::CACHE_KEY_OVERVIEW);

        static::created($flush);
        static::updated($flush);
        static::deleted($flush);
    }

    /** @var list<string> */
    protected $fillable = [
        'member_id',
        'source_member_id',
        'pairing_count',
        'amount',
        'index_value',
        'status',
        'description',
        'pairing_date',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'index_value' => 'decimal:2',
            'pairing_date' => 'date',
        ];
    }

    /**
     * Member penerima bonus.
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'member_id');
    }

    /**
     * Member sumber pairing.
     */
    public function sourceMember(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'source_member_id');
    }
}
