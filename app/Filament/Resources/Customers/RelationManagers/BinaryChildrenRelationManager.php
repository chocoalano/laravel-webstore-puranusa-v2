<?php

namespace App\Filament\Resources\Customers\RelationManagers;

use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BinaryChildrenRelationManager extends RelationManager
{
    protected static string $relationship = 'binaryChildren';

    protected static ?string $title = 'Anak Binary';

    protected static ?string $modelLabel = 'anak binary';

    protected static ?string $pluralModelLabel = 'anak binary';

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
            ->columns([
                TextColumn::make('username')
                    ->searchable(),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('position')
                    ->badge(),
                TextColumn::make('level')
                    ->badge(),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (int $state): string => match ($state) {
                        1 => 'Prospek',
                        2 => 'Pasif',
                        3 => 'Aktif',
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
