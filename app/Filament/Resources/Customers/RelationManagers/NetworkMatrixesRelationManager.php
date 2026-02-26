<?php

namespace App\Filament\Resources\Customers\RelationManagers;

use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class NetworkMatrixesRelationManager extends RelationManager
{
    protected static string $relationship = 'networkMatrixes';

    protected static ?string $title = 'Matrix Sponsor';

    protected static ?string $modelLabel = 'matrix sponsor';

    protected static ?string $pluralModelLabel = 'matrix sponsor';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('level')
                    ->required()
                    ->numeric(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('level')
            ->columns([
                TextColumn::make('sponsor.name')
                    ->label('Sponsor')
                    ->searchable(),
                TextColumn::make('level')
                    ->numeric()
                    ->sortable(),
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
