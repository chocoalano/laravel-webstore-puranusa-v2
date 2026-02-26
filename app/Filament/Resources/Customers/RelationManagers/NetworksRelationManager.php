<?php

namespace App\Filament\Resources\Customers\RelationManagers;

use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class NetworksRelationManager extends RelationManager
{
    protected static string $relationship = 'networks';

    protected static ?string $title = 'Jaringan Binary';

    protected static ?string $modelLabel = 'jaringan binary';

    protected static ?string $pluralModelLabel = 'jaringan binary';

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
                TextColumn::make('upline.name')
                    ->label('Upline')
                    ->searchable(),
                TextColumn::make('position')
                    ->badge(),
                TextColumn::make('level')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('status')
                    ->boolean(),
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
