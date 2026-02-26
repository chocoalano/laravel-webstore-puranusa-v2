<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Model ReportTaxSummary (Ringkasan PPh21 per Tahun/Bulan).
 *
 * Sumber data dari view SQL `vw_customer_bonus_pph21`.
 * Digunakan dalam dua konteks:
 * - Table Filament: query dimodifikasi dengan GROUP BY (menghasilkan kolom aggregate)
 * - Widget/Stats: query langsung ke view untuk aggregasi ad-hoc
 *
 * @property string $id               Synthetic key (format YYYYMM) saat dipakai GROUP BY bulanan
 * @property int $tahun_pajak
 * @property int|null $bulan          Nomor bulan (1–12), tersedia saat GROUP BY bulanan
 * @property float $total_bruto       Tersedia dalam aggregated query
 * @property float $total_pph21       Tersedia dalam aggregated query
 * @property int $total_transaksi     Tersedia dalam aggregated query
 * @property float|null $jumlah_bruto Tersedia dalam direct query (non-aggregated)
 * @property float|null $pph21        Tersedia dalam direct query (non-aggregated)
 */
class ReportTaxSummary extends BaseModel
{
    use HasFactory;

    protected $table = 'vw_customer_bonus_pph21';

    protected $primaryKey = 'id';

    public $incrementing = false;

    public $keyType = 'string';

    public $timestamps = false;

    protected $guarded = [];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tahun_pajak' => 'integer',
            'bulan' => 'integer',
            'total_bruto' => 'decimal:2',
            'total_pph21' => 'decimal:2',
            'total_transaksi' => 'integer',
            'jumlah_bruto' => 'decimal:2',
            'pph21' => 'decimal:2',
        ];
    }
}
