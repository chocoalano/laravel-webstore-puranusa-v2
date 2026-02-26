<?php

namespace App\Filament\Resources\Customers\RelationManagers;

use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BonusPairingsRelationManager extends RelationManager
{
    protected static string $relationship = 'bonusPairings';

    protected static ?string $title = 'Bonus Pairing';

    protected static ?string $modelLabel = 'bonus pairing';

    protected static ?string $pluralModelLabel = 'bonus pairing';

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
            ->defaultSort('pairing_date', 'desc')
            ->columns([
                TextColumn::make('pairing_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('sourceMember.name')
                    ->label('Sumber Member')
                    ->searchable(),
                TextColumn::make('pairing_count')
                    ->label('Jumlah Pair')
                    ->numeric()
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
            ])
            ->filters([])
            ->headerActions([])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([]);
    }
}
