<?php

namespace App\Filament\Resources\CustomerBonusLifetimeCashRewards\Pages;

use App\Filament\Resources\CustomerBonusLifetimeCashRewards\CustomerBonusLifetimeCashRewardResource;
use App\Filament\Resources\CustomerBonusLifetimeCashRewards\Widgets\CustomerBonusLifetimeCashRewardOverview;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageCustomerBonusLifetimeCashRewards extends ManageRecords
{
    protected static string $resource = CustomerBonusLifetimeCashRewardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            CustomerBonusLifetimeCashRewardOverview::class,
        ];
    }
}
