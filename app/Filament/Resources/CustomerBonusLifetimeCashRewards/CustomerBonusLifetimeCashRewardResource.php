<?php

namespace App\Filament\Resources\CustomerBonusLifetimeCashRewards;

use App\Filament\Resources\CustomerBonusLifetimeCashRewards\Pages\ManageCustomerBonusLifetimeCashRewards;
use App\Filament\Resources\CustomerBonusLifetimeCashRewards\Schemas\CustomerBonusLifetimeCashRewardForm;
use App\Filament\Resources\CustomerBonusLifetimeCashRewards\Schemas\CustomerBonusLifetimeCashRewardInfolist;
use App\Filament\Resources\CustomerBonusLifetimeCashRewards\Tables\CustomerBonusLifetimeCashRewardsTable;
use App\Models\CustomerBonusLifetimeCashReward;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class CustomerBonusLifetimeCashRewardResource extends Resource
{
    protected static ?string $model = CustomerBonusLifetimeCashReward::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'CustomerBonusLifetimeCashReward';
    protected static ?string $navigationLabel = 'Bonus Lifetime Cash Reward';
    protected static ?string $modelLabel = 'Bonus Lifetime Cash Reward';
    protected static ?string $pluralModelLabel = 'Bonus Lifetime Cash Reward';
    protected static string | UnitEnum | null $navigationGroup = 'Bonus & Komisi MLM';

    public static function form(Schema $schema): Schema
    {
        return CustomerBonusLifetimeCashRewardForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CustomerBonusLifetimeCashRewardInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CustomerBonusLifetimeCashRewardsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageCustomerBonusLifetimeCashRewards::route('/'),
        ];
    }
}
