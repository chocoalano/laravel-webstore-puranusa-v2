<?php

namespace App\Filament\Resources\Customers\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AddressesRelationManager extends RelationManager
{
    protected static string $relationship = 'addresses';

    protected static ?string $title = 'Alamat';

    protected static ?string $modelLabel = 'alamat';

    protected static ?string $pluralModelLabel = 'alamat';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('label')
                    ->maxLength(255),
                TextInput::make('recipient_name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('recipient_phone')
                    ->required()
                    ->maxLength(255),
                TextInput::make('address_line1')
                    ->required(),
                TextInput::make('address_line2'),
                TextInput::make('province_label')
                    ->required()
                    ->maxLength(100),
                TextInput::make('city_label')
                    ->required()
                    ->maxLength(100),
                TextInput::make('postal_code')
                    ->maxLength(20),
                Toggle::make('is_default'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('recipient_name')
            ->columns([
                TextColumn::make('label'),
                TextColumn::make('recipient_name')
                    ->searchable(),
                TextColumn::make('recipient_phone'),
                TextColumn::make('address_line1')
                    ->limit(40),
                TextColumn::make('province_label'),
                TextColumn::make('city_label'),
                TextColumn::make('postal_code'),
                IconColumn::make('is_default')
                    ->boolean(),
            ])
            ->filters([])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
