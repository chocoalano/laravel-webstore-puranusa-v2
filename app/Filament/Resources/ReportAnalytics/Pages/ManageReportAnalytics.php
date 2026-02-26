<?php

namespace App\Filament\Resources\ReportAnalytics\Pages;

use App\Filament\Resources\ReportAnalytics\ReportAnalyticResource;
use App\Filament\Resources\ReportAnalytics\Widgets\ReportAnalyticBrutoChart;
use App\Filament\Resources\ReportAnalytics\Widgets\ReportAnalyticCallouts;
use App\Filament\Resources\ReportAnalytics\Widgets\ReportAnalyticDistribusiChart;
use App\Filament\Resources\ReportAnalytics\Widgets\ReportAnalyticStatsOverview;
use Filament\Resources\Pages\ManageRecords;

class ManageReportAnalytics extends ManageRecords
{
    protected static string $resource = ReportAnalyticResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ReportAnalyticCallouts::class,
            ReportAnalyticStatsOverview::class,
            ReportAnalyticBrutoChart::class,
            ReportAnalyticDistribusiChart::class,
        ];
    }

    /**
     * Menentukan jumlah kolom grid di halaman ini.
     */
    public function getHeaderWidgetsColumns(): int|array
    {
        return 2;
    }
}
