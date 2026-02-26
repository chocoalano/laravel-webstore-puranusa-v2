<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model CustomerBonusLifetimeCashReward.
 *
 * Cash reward permanen (lifetime) yang diraih berdasarkan akumulasi BV.
 * Tidak memiliki periode, berlaku selamanya.
 *
 * @property int $id
 * @property int $member_id Member penerima
 * @property string $reward_name Nama reward (Silver, Gold, Platinum, dll)
 * @property float $reward Nilai target reward
 * @property float $amount Nominal yang diterima
 * @property float $bv Business Volume yang dibutuhkan
 * @property int $status 0=pending, 1=released
 * @property string|null $description Catatan
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class CustomerBonusLifetimeCashReward extends BaseModel
{
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'member_id',
        'reward_name',
        'reward',
        'amount',
        'bv',
        'status',
        'description',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'reward' => 'decimal:2',
            'amount' => 'decimal:2',
            'bv' => 'decimal:2',
        ];
    }

    /**
     * Member penerima lifetime cash reward.
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'member_id');
    }
}
