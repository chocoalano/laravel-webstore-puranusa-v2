<?php

namespace App\Filament\Resources\CustomerEwallets\Pages;

use App\Filament\Resources\CustomerEwallets\CustomerEwalletResource;
use App\Filament\Resources\CustomerEwallets\Widgets\CustomerEwalletOverview;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageCustomerEwallets extends ManageRecords
{
    protected static string $resource = CustomerEwalletResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            CustomerEwalletOverview::class,
        ];
    }
}
