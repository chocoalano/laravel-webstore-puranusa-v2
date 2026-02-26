<?php

namespace App\Filament\Resources\CustomerNetworks;

use App\Filament\Resources\CustomerNetworks\Pages\ManageCustomerNetworks;
use App\Filament\Resources\CustomerNetworks\Schemas\CustomerNetworkForm;
use App\Filament\Resources\CustomerNetworks\Schemas\CustomerNetworkInfolist;
use App\Filament\Resources\CustomerNetworks\Tables\CustomerNetworksTable;
use App\Models\CustomerNetwork;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class CustomerNetworkResource extends Resource
{
    protected static ?string $model = CustomerNetwork::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'id';
    protected static ?string $navigationLabel = 'Binary Jaringan';
    protected static ?string $modelLabel = 'Binary Jaringan';
    protected static ?string $pluralModelLabel = 'Binary Jaringan';
    protected static string|UnitEnum|null $navigationGroup = 'Affiliate';

    public static function form(Schema $schema): Schema
    {
        return CustomerNetworkForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CustomerNetworkInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CustomerNetworksTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageCustomerNetworks::route('/'),
        ];
    }
}
