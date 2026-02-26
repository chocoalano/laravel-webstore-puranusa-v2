<?php

namespace App\Filament\Resources\ProductReviews\RelationManagers;

use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrderItemRelationManager extends RelationManager
{
    protected static string $relationship = 'orderItem';

    protected static ?string $title = 'Item Pesanan';

    protected static ?string $modelLabel = 'item pesanan';

    protected static ?string $pluralModelLabel = 'item pesanan';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('order.order_no')
                    ->label('No. Order')
                    ->placeholder('-')
                    ->searchable(),
                TextColumn::make('name')
                    ->label('Nama Item')
                    ->searchable(),
                TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable(),
                TextColumn::make('qty')
                    ->label('Qty')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('unit_price')
                    ->label('Harga Satuan')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('row_total')
                    ->label('Total')
                    ->money('IDR')
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
