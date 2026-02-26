<?php

namespace App\Filament\Resources\Customers\RelationManagers;

use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BvRewardsRelationManager extends RelationManager
{
    protected static string $relationship = 'bvRewards';

    protected static ?string $title = 'Tracking BV Reward';

    protected static ?string $modelLabel = 'tracking BV reward';

    protected static ?string $pluralModelLabel = 'tracking BV reward';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('omzet_left')
                    ->required()
                    ->numeric(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('omzet_left')
            ->columns([
                TextColumn::make('reward.name')
                    ->label('Reward')
                    ->searchable(),
                TextColumn::make('omzet_left')
                    ->label('Omset Kiri')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('omzet_right')
                    ->label('Omset Kanan')
                    ->money('IDR')
                    ->sortable(),
                IconColumn::make('status')
                    ->boolean(),
                TextColumn::make('created_on')
                    ->label('Dibuat')
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
