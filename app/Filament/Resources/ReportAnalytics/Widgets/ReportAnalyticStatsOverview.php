<?php

namespace App\Filament\Resources\ReportAnalytics\Widgets;

use App\Models\ReportAnalytic;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ReportAnalyticStatsOverview extends BaseWidget
{
    protected ?string $pollingInterval = null;

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $total = ReportAnalytic::count();
        $totalBruto = (float) ReportAnalytic::sum('jumlah_bruto');
        $totalPph21 = (float) ReportAnalytic::sum('pph21');
        $withNpwp = ReportAnalytic::whereNotNull('npwp')->where('npwp', '!=', '')->count();

        $monthlyBruto = ReportAnalytic::query()
            ->selectRaw('MONTH(tanggal) as bulan, SUM(jumlah_bruto) as total')
            ->whereYear('tanggal', now()->year)
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->pluck('total', 'bulan')
            ->toArray();

        $brutoChart = [];
        for ($m = 1; $m <= 12; $m++) {
            $brutoChart[] = (float) ($monthlyBruto[$m] ?? 0);
        }

        return [
            Stat::make('Total Wajib Pajak', number_format($total, 0, ',', '.'))
                ->description('Data PPh21 bonus aktif')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary')
                ->extraAttributes(['class' => 'cp-zinc-stat cp-zinc-stat--north']),

            Stat::make('Total Jumlah Bruto', 'Rp ' . number_format($totalBruto, 0, ',', '.'))
                ->description('Akumulasi seluruh bruto bonus')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart($brutoChart)
                ->color('success')
                ->extraAttributes(['class' => 'cp-zinc-stat cp-zinc-stat--graphite']),

            Stat::make('Total PPh21', 'Rp ' . number_format(abs($totalPph21), 0, ',', '.'))
                ->description($totalPph21 < 0 ? 'Nilai negatif — perlu ditinjau' : 'Akumulasi potongan pajak')
                ->descriptionIcon($totalPph21 < 0 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-arrow-trending-down')
                ->color($totalPph21 < 0 ? 'danger' : 'warning')
                ->extraAttributes(['class' => 'cp-zinc-stat cp-zinc-stat--alloy']),

            Stat::make('Tanpa NPWP', number_format($total - $withNpwp, 0, ',', '.') . ' / ' . number_format($total, 0, ',', '.'))
                ->description($withNpwp === 0 ? 'Tidak ada yang ber-NPWP' : 'Wajib pajak tanpa NPWP')
                ->descriptionIcon('heroicon-m-exclamation-circle')
                ->color($withNpwp === 0 ? 'warning' : 'info')
                ->extraAttributes(['class' => 'cp-zinc-stat cp-zinc-stat--chrome']),
        ];
    }
}
