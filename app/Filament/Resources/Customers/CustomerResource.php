<?php

namespace App\Filament\Resources\Customers;

use App\Filament\Resources\Customers\Pages\CreateCustomer;
use App\Filament\Resources\Customers\Pages\EditCustomer;
use App\Filament\Resources\Customers\Pages\ListCustomers;
use App\Filament\Resources\Customers\Pages\ViewCustomer;
use App\Filament\Resources\Customers\RelationManagers\AddressesRelationManager;
use App\Filament\Resources\Customers\RelationManagers\BinaryChildrenRelationManager;
use App\Filament\Resources\Customers\RelationManagers\BonusCashbacksRelationManager;
use App\Filament\Resources\Customers\RelationManagers\BonusesRelationManager;
use App\Filament\Resources\Customers\RelationManagers\BonusLifetimeCashRewardsRelationManager;
use App\Filament\Resources\Customers\RelationManagers\BonusMatchingsRelationManager;
use App\Filament\Resources\Customers\RelationManagers\BonusPairingsRelationManager;
use App\Filament\Resources\Customers\RelationManagers\BonusRetailsRelationManager;
use App\Filament\Resources\Customers\RelationManagers\BonusRewardsRelationManager;
use App\Filament\Resources\Customers\RelationManagers\BonusSponsorsRelationManager;
use App\Filament\Resources\Customers\RelationManagers\BvRewardsRelationManager;
use App\Filament\Resources\Customers\RelationManagers\DownlinesRelationManager;
use App\Filament\Resources\Customers\RelationManagers\NetworkMatrixesRelationManager;
use App\Filament\Resources\Customers\RelationManagers\NetworksRelationManager;
use App\Filament\Resources\Customers\RelationManagers\OrdersRelationManager;
use App\Filament\Resources\Customers\RelationManagers\ProductReviewsRelationManager;
use App\Filament\Resources\Customers\RelationManagers\RewardsRelationManager;
use App\Filament\Resources\Customers\RelationManagers\WalletTransactionsRelationManager;
use App\Filament\Resources\Customers\RelationManagers\WishlistsRelationManager;
use App\Filament\Resources\Customers\Schemas\CustomerForm;
use App\Filament\Resources\Customers\Schemas\CustomerInfolist;
use App\Filament\Resources\Customers\Tables\CustomersTable;
use App\Models\Customer;
use BackedEnum;
use Filament\Resources\RelationManagers\RelationGroup;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Pelanggan';
    protected static ?string $navigationLabel = 'Pelanggan';
    protected static ?string $modelLabel = 'Pelanggan';
    protected static ?string $pluralModelLabel = 'Pelanggan';
    protected static string | UnitEnum | null $navigationGroup = 'Kelola';

    public static function form(Schema $schema): Schema
    {
        return CustomerForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CustomerInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CustomersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RelationGroup::make('Network & Downline', [
                DownlinesRelationManager::class,
                BinaryChildrenRelationManager::class,
                NetworksRelationManager::class,
                NetworkMatrixesRelationManager::class,
            ]),

            RelationGroup::make('E-Commerce', [
                OrdersRelationManager::class,
                AddressesRelationManager::class,
                WishlistsRelationManager::class,
                ProductReviewsRelationManager::class,
            ]),

            RelationGroup::make('Wallet & Bonus', [
                WalletTransactionsRelationManager::class,
                BonusesRelationManager::class,
                BonusSponsorsRelationManager::class,
                BonusMatchingsRelationManager::class,
                BonusPairingsRelationManager::class,
                BonusRetailsRelationManager::class,
                BonusCashbacksRelationManager::class,
                BonusRewardsRelationManager::class,
                BonusLifetimeCashRewardsRelationManager::class,
            ]),

            RelationGroup::make('Rewards', [
                RewardsRelationManager::class,
                BvRewardsRelationManager::class,
            ]),
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCustomers::route('/'),
            'create' => CreateCustomer::route('/create'),
            'view' => ViewCustomer::route('/{record}'),
            'edit' => EditCustomer::route('/{record}/edit'),
        ];
    }
}
