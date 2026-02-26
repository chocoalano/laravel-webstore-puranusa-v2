<?php

namespace App\Filament\Resources\Customers\RelationManagers;

use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BonusLifetimeCashRewardsRelationManager extends RelationManager
{
    protected static string $relationship = 'bonusLifetimeCashRewards';

    protected static ?string $title = 'Bonus Lifetime Cash Reward';

    protected static ?string $modelLabel = 'bonus lifetime cash reward';

    protected static ?string $pluralModelLabel = 'bonus lifetime cash reward';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('reward_name')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('reward_name')
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('reward_name')
                    ->label('Reward'),
                TextColumn::make('reward')
                    ->label('Nilai Target')
                    ->money('IDR'),
                TextColumn::make('amount')
                    ->label('Diterima')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('bv')
                    ->label('BV')
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
