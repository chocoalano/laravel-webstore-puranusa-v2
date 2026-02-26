<?php

namespace App\Filament\Resources\CustomerBonusPairings\Pages;

use App\Filament\Resources\CustomerBonusPairings\CustomerBonusPairingResource;
use App\Filament\Resources\CustomerBonusPairings\Widgets\CustomerBonusPairingOverview;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageCustomerBonusPairings extends ManageRecords
{
    protected static string $resource = CustomerBonusPairingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            CustomerBonusPairingOverview::class,
        ];
    }
}
