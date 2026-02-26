<?php

namespace App\Filament\Resources\CustomerBonusPairings;

use App\Filament\Resources\CustomerBonusPairings\Pages\ManageCustomerBonusPairings;
use App\Filament\Resources\CustomerBonusPairings\Schemas\CustomerBonusPairingForm;
use App\Filament\Resources\CustomerBonusPairings\Schemas\CustomerBonusPairingInfolist;
use App\Filament\Resources\CustomerBonusPairings\Tables\CustomerBonusPairingsTable;
use App\Models\CustomerBonusPairing;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class CustomerBonusPairingResource extends Resource
{
    protected static ?string $model = CustomerBonusPairing::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'CustomerBonusPairing';
    protected static ?string $navigationLabel = 'Bonus Pairing';
    protected static ?string $modelLabel = 'Bonus Pairing';
    protected static ?string $pluralModelLabel = 'Bonus Pairing';
    protected static string | UnitEnum | null $navigationGroup = 'Bonus & Komisi MLM';

    public static function form(Schema $schema): Schema
    {
        return CustomerBonusPairingForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CustomerBonusPairingInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CustomerBonusPairingsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageCustomerBonusPairings::route('/'),
        ];
    }
}
