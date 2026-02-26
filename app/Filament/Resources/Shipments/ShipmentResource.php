<?php

namespace App\Filament\Resources\Shipments;

use App\Filament\Resources\Shipments\Pages\ManageShipments;
use App\Models\Shipment;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class ShipmentResource extends Resource
{
    protected static ?string $model = Shipment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Pengiriman Pesanan';
    protected static ?string $modelLabel = 'Pengiriman Pesanan';
    protected static ?string $pluralModelLabel = 'Pengiriman Pesanan';
    protected static string | UnitEnum | null $navigationGroup = 'Pesanan';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('order_id')
                    ->relationship('order', 'order_no')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('courier_id')
                    ->label('Kurir (RajaOngkir)')
                    ->options(fn (): array => Shipment::courierOptions())
                    ->searchable()
                    ->preload()
                    ->native(false)
                    ->required(),
                TextInput::make('tracking_no'),
                TextInput::make('status')
                    ->required()
                    ->default('pending'),
                DateTimePicker::make('shipped_at'),
                DateTimePicker::make('delivered_at'),
                TextInput::make('shipping_fee')
                    ->required()
                    ->numeric()
                    ->default(0.0),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('order.order_no')
                    ->label('Order'),
                TextEntry::make('courier_id')
                    ->label('Kurir')
                    ->formatStateUsing(fn (?string $state): string => Shipment::courierOptions()[$state] ?? (string) ($state ?: '-'))
                    ->placeholder('-'),
                TextEntry::make('tracking_no')
                    ->placeholder('-'),
                TextEntry::make('status'),
                TextEntry::make('shipped_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('delivered_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('shipping_fee')
                    ->numeric(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('Shipment')
            ->columns([
                TextColumn::make('order.order_no')
                    ->searchable(),
                TextColumn::make('courier_id')
                    ->label('Kurir')
                    ->formatStateUsing(fn (?string $state): string => Shipment::courierOptions()[$state] ?? (string) ($state ?: '-'))
                    ->placeholder('-')
                    ->searchable(),
                TextColumn::make('tracking_no')
                    ->searchable(),
                TextColumn::make('status')
                    ->searchable(),
                TextColumn::make('shipped_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('delivered_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('shipping_fee')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                // DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageShipments::route('/'),
        ];
    }
}
