<?php

namespace App\Filament\Resources\CustomerNetworks\Widgets;

use App\Models\CustomerNetwork;
use Filament\Support\Enums\IconPosition;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Collection;

class CustomerNetworkOverview extends StatsOverviewWidget
{
    protected ?string $heading = 'Insight Binary Jaringan';

    protected ?string $description = 'Ringkasan distribusi node binary dan kedalaman jaringan.';

    protected ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $data = $this->computeStats();

        return [
            Stat::make('Total Node Binary', $this->formatNumber($data['totalRecords']))
                ->description('Aktif: ' . $this->formatNumber($data['activeCount']) . ' | Nonaktif: ' . $this->formatNumber($data['inactiveCount']))
                ->descriptionIcon('heroicon-m-squares-2x2', IconPosition::Before)
                ->color('primary')
                ->icon('heroicon-o-squares-2x2')
                ->chart($data['chart'])
                ->extraAttributes([
                    'class' => 'cp-zinc-stat cp-zinc-stat--north',
                ]),

            Stat::make('Node Aktif', $this->formatNumber($data['activeCount']))
                ->description($this->formatPercent($data['activePercentage']) . ' aktif dari total node')
                ->descriptionIcon('heroicon-m-check-badge', IconPosition::Before)
                ->color('primary')
                ->icon('heroicon-o-check-badge')
                ->extraAttributes([
                    'class' => 'cp-zinc-stat cp-zinc-stat--graphite',
                ]),

            Stat::make('Distribusi Posisi', $this->formatNumber($data['leftCount'] + $data['rightCount']))
                ->description('Kiri: ' . $this->formatNumber($data['leftCount']) . ' | Kanan: ' . $this->formatNumber($data['rightCount']))
                ->descriptionIcon('heroicon-m-arrows-right-left', IconPosition::Before)
                ->color('primary')
                ->icon('heroicon-o-arrows-right-left')
                ->extraAttributes([
                    'class' => 'cp-zinc-stat cp-zinc-stat--alloy',
                ]),

            Stat::make('Kedalaman Jaringan', $this->formatNumber($data['maxLevel']))
                ->description('Rata-rata level: ' . $this->formatNumber($data['averageLevel'], 2) . ' | Root: ' . $this->formatNumber($data['rootCount']))
                ->descriptionIcon('heroicon-m-chart-bar', IconPosition::Before)
                ->color('primary')
                ->icon('heroicon-o-chart-bar')
                ->extraAttributes([
                    'class' => 'cp-zinc-stat cp-zinc-stat--chrome',
                ]),
        ];
    }

    /**
     * @return array{totalRecords: int, activeCount: int, inactiveCount: int, activePercentage: float, leftCount: int, rightCount: int, rootCount: int, averageLevel: float, maxLevel: int, chart: array<int, int>}
     */
    protected function computeStats(): array
    {
        $query = CustomerNetwork::query();
        $totalRecords = (clone $query)->count();
        $activeCount = (clone $query)->where('status', 1)->count();

        return [
            'totalRecords' => $totalRecords,
            'activeCount' => $activeCount,
            'inactiveCount' => (clone $query)->where('status', 0)->count(),
            'activePercentage' => $totalRecords > 0 ? ($activeCount / $totalRecords) * 100 : 0.0,
            'leftCount' => (clone $query)->where('position', 'left')->count(),
            'rightCount' => (clone $query)->where('position', 'right')->count(),
            'rootCount' => (clone $query)->whereNull('upline_id')->count(),
            'averageLevel' => (float) ((clone $query)->avg('level') ?? 0),
            'maxLevel' => (int) ((clone $query)->max('level') ?? 0),
            'chart' => $this->getLast7DaysTrend(),
        ];
    }

    /**
     * @return array<int, int>
     */
    protected function getLast7DaysTrend(): array
    {
        $startDate = now()->subDays(6)->startOfDay();

        /** @var Collection<string, int> $dailyCounts */
        $dailyCounts = CustomerNetwork::query()
            ->selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->where('created_at', '>=', $startDate)
            ->groupBy('date')
            ->pluck('total', 'date')
            ->map(fn (mixed $total): int => (int) $total);

        return collect(range(0, 6))
            ->map(
                fn (int $dayOffset): int => $dailyCounts->get(
                    now()->subDays(6 - $dayOffset)->toDateString(),
                    0,
                ),
            )
            ->all();
    }

    protected function formatNumber(float|int $number, int $precision = 0): string
    {
        return number_format($number, $precision, ',', '.');
    }

    protected function formatPercent(float $number): string
    {
        return number_format($number, 1, ',', '.') . '%';
    }
}
