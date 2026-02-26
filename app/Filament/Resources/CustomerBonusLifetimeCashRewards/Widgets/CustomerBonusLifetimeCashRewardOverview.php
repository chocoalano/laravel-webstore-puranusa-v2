<?php

namespace App\Filament\Resources\CustomerBonusLifetimeCashRewards\Widgets;

use App\Models\CustomerBonusLifetimeCashReward;
use Filament\Support\Enums\IconPosition;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Collection;

class CustomerBonusLifetimeCashRewardOverview extends StatsOverviewWidget
{
    protected ?string $heading = 'Insight Lifetime Cash Reward';

    protected ?string $description = 'Ringkasan distribusi reward cash lifetime untuk monitoring cepat.';

    protected ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $data = $this->computeStats();

        return [
            Stat::make('Total Catatan Reward', $this->formatNumber($data['totalRecords']))
                ->description('Akumulasi nominal: ' . $this->formatCurrencyIdr($data['totalAmount']))
                ->descriptionIcon('heroicon-m-sparkles', IconPosition::Before)
                ->color('primary')
                ->icon('heroicon-o-trophy')
                ->chart($data['chart'])
                ->extraAttributes([
                    'class' => 'cp-zinc-stat cp-zinc-stat--north',
                ]),

            Stat::make('Menunggu Pencairan', $this->formatNumber($data['pendingCount']))
                ->description('Nominal tertahan: ' . $this->formatCurrencyIdr($data['pendingAmount']))
                ->descriptionIcon('heroicon-m-clock', IconPosition::Before)
                ->color('primary')
                ->icon('heroicon-o-exclamation-triangle')
                ->extraAttributes([
                    'class' => 'cp-zinc-stat cp-zinc-stat--graphite',
                ]),

            Stat::make('Sudah Dirilis', $this->formatNumber($data['releasedCount']))
                ->description('Nominal cair: ' . $this->formatCurrencyIdr($data['releasedAmount']))
                ->descriptionIcon('heroicon-m-check-badge', IconPosition::Before)
                ->color('primary')
                ->icon('heroicon-o-check-badge')
                ->extraAttributes([
                    'class' => 'cp-zinc-stat cp-zinc-stat--alloy',
                ]),

            Stat::make('Member Penerima Unik', $this->formatNumber($data['uniqueMembers']))
                ->description('Rata-rata reward: ' . $this->formatCurrencyIdr($data['averageAmount']))
                ->descriptionIcon('heroicon-m-users', IconPosition::Before)
                ->color('primary')
                ->icon('heroicon-o-users')
                ->extraAttributes([
                    'class' => 'cp-zinc-stat cp-zinc-stat--chrome',
                ]),
        ];
    }

    /**
     * @return array{totalRecords: int, totalAmount: float, pendingCount: int, pendingAmount: float, releasedCount: int, releasedAmount: float, uniqueMembers: int, averageAmount: float, chart: array<int, int>}
     */
    protected function computeStats(): array
    {
        $rewardQuery = CustomerBonusLifetimeCashReward::query();

        return [
            'totalRecords' => (clone $rewardQuery)->count(),
            'totalAmount' => (float) (clone $rewardQuery)->sum('amount'),
            'pendingCount' => (clone $rewardQuery)->where('status', 0)->count(),
            'pendingAmount' => (float) (clone $rewardQuery)->where('status', 0)->sum('amount'),
            'releasedCount' => (clone $rewardQuery)->where('status', 1)->count(),
            'releasedAmount' => (float) (clone $rewardQuery)->where('status', 1)->sum('amount'),
            'uniqueMembers' => (clone $rewardQuery)->whereNotNull('member_id')->distinct()->count('member_id'),
            'averageAmount' => (float) (clone $rewardQuery)->avg('amount'),
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
        $dailyCounts = CustomerBonusLifetimeCashReward::query()
            ->selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->whereNotNull('created_at')
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

    protected function formatCurrencyIdr(float|int $number): string
    {
        return 'Rp ' . number_format($number, 2, ',', '.');
    }
}
