<?php

namespace App\Filament\Resources\Customers\RelationManagers;

use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BonusMatchingsRelationManager extends RelationManager
{
    protected static string $relationship = 'bonusMatchings';

    protected static ?string $title = 'Bonus Matching';

    protected static ?string $modelLabel = 'bonus matching';

    protected static ?string $pluralModelLabel = 'bonus matching';

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
                TextColumn::make('fromMember.name')
                    ->label('Dari Member')
                    ->searchable(),
                TextColumn::make('level')
                    ->badge(),
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
