<?php

namespace App\Filament\Resources\ShippingTargets\Pages;

use App\Filament\Resources\ShippingTargets\Exports\ShippingTargetExporter;
use App\Filament\Resources\ShippingTargets\Imports\ShippingTargetImporter;
use App\Filament\Resources\ShippingTargets\ShippingTargetResource;
use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ImportAction;
use Filament\Resources\Pages\ManageRecords;

class ManageShippingTargets extends ManageRecords
{
    protected static string $resource = ShippingTargetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            ImportAction::make()
                ->label('Import CSV')
                ->importer(ShippingTargetImporter::class)
                ->fileRules([
                    'max:10240',
                ])
                ->chunkSize(250),
            ExportAction::make()
                ->label('Export CSV/XLSX')
                ->exporter(ShippingTargetExporter::class),
        ];
    }
}
