<?php

namespace App\Filament\Resources\CustomerWithdrawals\Pages;

use App\Filament\Resources\CustomerWithdrawals\CustomerWithdrawalResource;
use App\Filament\Resources\CustomerWithdrawals\Exports\CustomerWithdrawalExporter;
use App\Filament\Resources\CustomerWithdrawals\Imports\CustomerWithdrawalImporter;
use App\Filament\Resources\CustomerWithdrawals\Widgets\CustomerWithdrawalOverview;
use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ImportAction;
use Filament\Resources\Pages\ManageRecords;

class ManageCustomerWithdrawals extends ManageRecords
{
    protected static string $resource = CustomerWithdrawalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            ImportAction::make()
                ->label('Import CSV/XLSX')
                ->importer(CustomerWithdrawalImporter::class)
                ->fileRules([
                    'max:10240',
                ])
                ->chunkSize(250),
            ExportAction::make()
                ->label('Export CSV/XLSX')
                ->exporter(CustomerWithdrawalExporter::class),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            CustomerWithdrawalOverview::class,
        ];
    }
}
