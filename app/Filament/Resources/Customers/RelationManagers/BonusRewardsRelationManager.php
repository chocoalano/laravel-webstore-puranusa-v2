<?php

namespace App\Filament\Resources\Customers\RelationManagers;

use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BonusRewardsRelationManager extends RelationManager
{
    protected static string $relationship = 'bonusRewards';

    protected static ?string $title = 'Bonus Reward';

    protected static ?string $modelLabel = 'bonus reward';

    protected static ?string $pluralModelLabel = 'bonus reward';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('reward')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('reward')
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('reward_type')
                    ->label('Tipe')
                    ->badge(),
                TextColumn::make('reward'),
                TextColumn::make('bv')
                    ->label('BV Target')
                    ->numeric(),
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
