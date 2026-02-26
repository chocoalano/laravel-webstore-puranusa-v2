<?php

namespace App\Filament\Resources\Customers\RelationManagers;

use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BonusesRelationManager extends RelationManager
{
    protected static string $relationship = 'bonuses';

    protected static ?string $title = 'Ringkasan Bonus';

    protected static ?string $modelLabel = 'ringkasan bonus';

    protected static ?string $pluralModelLabel = 'ringkasan bonus';

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
            ->defaultSort('date', 'desc')
            ->columns([
                TextColumn::make('date')
                    ->date()
                    ->sortable(),
                TextColumn::make('amount')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('tax_netto')
                    ->label('Netto')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('tax_percent')
                    ->label('Pajak %')
                    ->suffix('%'),
                TextColumn::make('tax_value')
                    ->label('Nilai Pajak')
                    ->money('IDR'),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (int $state): string => match ($state) {
                        0 => 'Pending',
                        1 => 'Released',
                        default => (string) $state,
                    }),
            ])
            ->filters([])
            ->headerActions([])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([]);
    }
}
