<?php

namespace App\Filament\Resources\CustomerBonusRetails;

use App\Filament\Resources\CustomerBonusRetails\Pages\ManageCustomerBonusRetails;
use App\Filament\Resources\CustomerBonusRetails\Schemas\CustomerBonusRetailForm;
use App\Filament\Resources\CustomerBonusRetails\Schemas\CustomerBonusRetailInfolist;
use App\Filament\Resources\CustomerBonusRetails\Tables\CustomerBonusRetailsTable;
use App\Models\CustomerBonusRetail;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class CustomerBonusRetailResource extends Resource
{
    protected static ?string $model = CustomerBonusRetail::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'CustomerBonusRetail';
    protected static ?string $navigationLabel = 'Bonus Retail';
    protected static ?string $modelLabel = 'Bonus Retail';
    protected static ?string $pluralModelLabel = 'Bonus Retail';
    protected static string | UnitEnum | null $navigationGroup = 'Bonus & Komisi MLM';

    public static function form(Schema $schema): Schema
    {
        return CustomerBonusRetailForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CustomerBonusRetailInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CustomerBonusRetailsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageCustomerBonusRetails::route('/'),
        ];
    }
}
