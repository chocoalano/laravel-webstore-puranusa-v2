<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model PaymentMethod (Metode Pembayaran).
 *
 * Daftar metode pembayaran yang tersedia (e-wallet, transfer bank, dll).
 *
 * @property int $id
 * @property string $code Kode unik metode
 * @property string $name Nama metode
 * @property bool $is_active Apakah metode aktif
 */
class PaymentMethod extends BaseModel
{
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'code',
        'name',
        'is_active',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Pembayaran yang menggunakan metode ini.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'method_id');
    }
}
