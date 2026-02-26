<?php

namespace App\Filament\Resources\Products\RelationManagers;

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

class PromotionsRelationManager extends RelationManager
{
    protected static string $relationship = 'promotions';

    protected static ?string $title = 'Promosi';

    protected static ?string $modelLabel = 'promosi';

    protected static ?string $pluralModelLabel = 'promosi';

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
            ->defaultSort('start_at', 'desc')
            ->columns([
                TextColumn::make('code')
                    ->searchable(),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('type')
                    ->badge(),
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
                IconColumn::make('is_active')
                    ->boolean(),
                TextColumn::make('start_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('end_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([])
            ->headerActions([
                AttachAction::make()
                    ->preloadRecordSelect()
                    ->schema(fn (AttachAction $action): array => [
                        $action->getRecordSelect(),
                        TextInput::make('min_qty')
                            ->numeric()
                            ->required()
                            ->default(1),
                        TextInput::make('discount_value')
                            ->numeric(),
                        TextInput::make('discount_percent')
                            ->numeric(),
                        TextInput::make('bundle_price')
                            ->numeric(),
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
