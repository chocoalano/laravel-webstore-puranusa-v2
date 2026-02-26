<?php

namespace App\Filament\Resources\CustomerBonusMatchings\Pages;

use App\Filament\Resources\CustomerBonusMatchings\CustomerBonusMatchingResource;
use App\Filament\Resources\CustomerBonusMatchings\Widgets\CustomerBonusMatchingOverview;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageCustomerBonusMatchings extends ManageRecords
{
    protected static string $resource = CustomerBonusMatchingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            CustomerBonusMatchingOverview::class,
        ];
    }
}
