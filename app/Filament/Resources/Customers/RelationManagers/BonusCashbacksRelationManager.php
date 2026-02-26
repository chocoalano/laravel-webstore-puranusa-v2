<?php

namespace App\Filament\Resources\Customers\RelationManagers;

use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BonusCashbacksRelationManager extends RelationManager
{
    protected static string $relationship = 'bonusCashbacks';

    protected static ?string $title = 'Bonus Cashback';

    protected static ?string $modelLabel = 'bonus cashback';

    protected static ?string $pluralModelLabel = 'bonus cashback';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('amount')
                    ->required()
                    ->numeric(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('amount')
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('order_id')
                    ->label('Order ID')
                    ->sortable(),
                TextColumn::make('amount')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('index_value')
                    ->label('Index')
                    ->numeric(),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (int $state): string => match ($state) {
                        0 => 'Pending',
                        1 => 'Released',
                        default => (string) $state,
                    }),
                TextColumn::make('created_at')
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
