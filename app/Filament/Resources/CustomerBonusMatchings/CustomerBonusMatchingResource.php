<?php

namespace App\Filament\Resources\CustomerBonusMatchings;

use App\Filament\Resources\CustomerBonusMatchings\Pages\ManageCustomerBonusMatchings;
use App\Filament\Resources\CustomerBonusMatchings\Schemas\CustomerBonusMatchingForm;
use App\Filament\Resources\CustomerBonusMatchings\Schemas\CustomerBonusMatchingInfolist;
use App\Filament\Resources\CustomerBonusMatchings\Tables\CustomerBonusMatchingsTable;
use App\Models\CustomerBonusMatching;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class CustomerBonusMatchingResource extends Resource
{
    protected static ?string $model = CustomerBonusMatching::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'CustomerBonusMatching';
    protected static ?string $navigationLabel = 'Bonus Matching';
    protected static ?string $modelLabel = 'Bonus Matching';
    protected static ?string $pluralModelLabel = 'Bonus Matching';
    protected static string | UnitEnum | null $navigationGroup = 'Bonus & Komisi MLM';

    public static function form(Schema $schema): Schema
    {
        return CustomerBonusMatchingForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CustomerBonusMatchingInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CustomerBonusMatchingsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageCustomerBonusMatchings::route('/'),
        ];
    }
}
