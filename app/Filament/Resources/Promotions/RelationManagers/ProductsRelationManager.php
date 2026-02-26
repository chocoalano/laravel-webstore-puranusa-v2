<?php

namespace App\Filament\Resources\Promotions\RelationManagers;

use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProductsRelationManager extends RelationManager
{
    protected static string $relationship = 'products';

    protected static ?string $title = 'Produk';

    protected static ?string $modelLabel = 'produk';

    protected static ?string $pluralModelLabel = 'produk';

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
            ->defaultSort('name')
            ->columns([
                TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable(),
                TextColumn::make('name')
                    ->label('Produk')
                    ->searchable(),
                TextColumn::make('base_price')
                    ->label('Harga Dasar')
                    ->money('IDR')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
                TextColumn::make('pivot.min_qty')
                    ->label('Min Qty')
                    ->numeric(),
                TextColumn::make('pivot.discount_value')
                    ->label('Diskon Nominal')
                    ->money('IDR'),
                TextColumn::make('pivot.discount_percent')
                    ->label('Diskon %')
                    ->suffix('%')
                    ->numeric(),
                TextColumn::make('pivot.bundle_price')
                    ->label('Harga Bundle')
                    ->money('IDR'),
            ])
            ->filters([])
            ->headerActions([
                AttachAction::make()
                    ->preloadRecordSelect()
                    ->recordSelectSearchColumns(['name', 'sku'])
                    ->schema(fn (AttachAction $action): array => [
                        $action->getRecordSelect(),
                        TextInput::make('min_qty')
                            ->numeric()
                            ->required()
                            ->default(1)
                            ->minValue(1),
                        TextInput::make('discount_value')
                            ->numeric()
                            ->minValue(0),
                        TextInput::make('discount_percent')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100),
                        TextInput::make('bundle_price')
                            ->numeric()
                            ->minValue(0),
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
                DetachAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make(),
                ]),
            ]);
    }
}
