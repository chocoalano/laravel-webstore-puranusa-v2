<?php

namespace App\Filament\Resources\CustomerTopups\Pages;

use App\Filament\Resources\CustomerTopups\CustomerTopupResource;
use App\Filament\Resources\CustomerTopups\Widgets\CustomerTopupOverview;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageCustomerTopups extends ManageRecords
{
    protected static string $resource = CustomerTopupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            CustomerTopupOverview::class,
        ];
    }
}
