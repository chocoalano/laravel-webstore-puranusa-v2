<?php

namespace App\Filament\Resources\Customers\RelationManagers;

use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RewardsRelationManager extends RelationManager
{
    protected static string $relationship = 'rewards';

    protected static ?string $title = 'Pencapaian Reward';

    protected static ?string $modelLabel = 'pencapaian reward';

    protected static ?string $pluralModelLabel = 'pencapaian reward';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('reward')
                    ->required()
                    ->maxLength(225),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('reward')
            ->columns([
                TextColumn::make('reward')
                    ->searchable(),
                TextColumn::make('total_bv_achieved')
                    ->label('BV Dicapai')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn (int $state): string => match ($state) {
                        0 => 'Periode',
                        1 => 'Permanen',
                        default => (string) $state,
                    }),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (int $state): string => match ($state) {
                        0 => 'Pending',
                        1 => 'Achieved',
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
