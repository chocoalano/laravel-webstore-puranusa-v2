<?php

namespace App\Filament\Resources\CustomerNetworks\Pages;

use App\Filament\Resources\CustomerNetworks\CustomerNetworkResource;
use App\Filament\Resources\CustomerNetworks\Widgets\CustomerNetworkOverview;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageCustomerNetworks extends ManageRecords
{
    protected static string $resource = CustomerNetworkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            CustomerNetworkOverview::class,
        ];
    }
}
