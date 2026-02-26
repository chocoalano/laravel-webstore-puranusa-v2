<?php

namespace App\Filament\Resources\CustomerWithdrawals\Pages;

use App\Filament\Resources\CustomerWithdrawals\CustomerWithdrawalResource;
use App\Filament\Resources\CustomerWithdrawals\Widgets\CustomerWithdrawalOverview;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageCustomerWithdrawals extends ManageRecords
{
    protected static string $resource = CustomerWithdrawalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            CustomerWithdrawalOverview::class,
        ];
    }
}
