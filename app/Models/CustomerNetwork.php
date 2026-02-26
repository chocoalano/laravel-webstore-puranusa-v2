<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model CustomerNetwork (Jaringan Binary Tree).
 *
 * Menyimpan relasi binary tree antara upline dan downline.
 * Setiap record menggambarkan satu node dalam binary tree.
 *
 * @property int $id
 * @property int|null $member_id Member/downline
 * @property int|null $upline_id Upline yang menaungi
 * @property string $position Posisi di binary tree (left|right)
 * @property int $status Status aktif (1=aktif, 0=tidak aktif)
 * @property int $level Level kedalaman dari upline
 * @property string|null $description Catatan
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class CustomerNetwork extends BaseModel
{
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'member_id',
        'upline_id',
        'position',
        'status',
        'level',
        'description',
    ];

    /**
     * Member yang berada di jaringan.
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'member_id');
    }

    /**
     * Upline yang menaungi member ini.
     */
    public function upline(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'upline_id');
    }
}
