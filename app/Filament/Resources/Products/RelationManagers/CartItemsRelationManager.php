<?php

namespace App\Filament\Resources\Products\RelationManagers;

use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CartItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'cartItems';

    protected static ?string $title = 'Item Keranjang';

    protected static ?string $modelLabel = 'item keranjang';

    protected static ?string $pluralModelLabel = 'item keranjang';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('product_name')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('product_name')
            ->defaultSort('updated_at', 'desc')
            ->columns([
                TextColumn::make('cart.customer.name')
                    ->label('Customer')
                    ->placeholder('-')
                    ->searchable(),
                TextColumn::make('product_name')
                    ->label('Produk')
                    ->searchable(),
                TextColumn::make('product_sku')
                    ->label('SKU')
                    ->searchable(),
                TextColumn::make('qty')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('unit_price')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('row_total')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([])
            ->headerActions([])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([]);
    }
}
