<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

/**
 * Model CustomerBonusMatching (Bonus Matching / Plan A).
 *
 * Bonus dari selisih peringkat (level) dalam jaringan sponsor.
 * Dihitung berdasarkan level member (Associate, Senior Associate, Executive, Director).
 *
 * @property int $id
 * @property int|null $member_id Member penerima bonus matching
 * @property int|null $from_member_id Member sumber omset
 * @property int $level Level kedalaman
 * @property float $amount Nominal bonus
 * @property float $index_value Nilai index
 * @property int $status 0=pending, 1=released
 * @property string|null $description Catatan
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class CustomerBonusMatching extends BaseModel
{
    use HasFactory;

    public const CACHE_KEY_OVERVIEW = 'customer_bonus_matching_overview';

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
        'from_member_id',
        'level',
        'amount',
        'index_value',
        'status',
        'description',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'index_value' => 'decimal:2',
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
     * Member sumber omset yang memicu bonus.
     */
    public function fromMember(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'from_member_id');
    }
}
