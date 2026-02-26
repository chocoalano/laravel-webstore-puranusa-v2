<?php

namespace App\Filament\Resources\Wishlists\RelationManagers;

use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $title = 'Item Wishlist';

    protected static ?string $modelLabel = 'item wishlist';

    protected static ?string $pluralModelLabel = 'item wishlist';

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
                TextColumn::make('product.name')
                    ->label('Produk Master')
                    ->placeholder('-')
                    ->searchable(),
                TextColumn::make('product_name')
                    ->label('Produk Snapshot')
                    ->searchable(),
                TextColumn::make('product_sku')
                    ->label('SKU')
                    ->searchable(),
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
