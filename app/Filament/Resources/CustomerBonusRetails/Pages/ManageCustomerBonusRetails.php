<?php

namespace App\Filament\Resources\CustomerBonusRetails\Pages;

use App\Filament\Resources\CustomerBonusRetails\CustomerBonusRetailResource;
use App\Filament\Resources\CustomerBonusRetails\Widgets\CustomerBonusRetailOverview;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageCustomerBonusRetails extends ManageRecords
{
    protected static string $resource = CustomerBonusRetailResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            CustomerBonusRetailOverview::class,
        ];
    }
}
