<?php

namespace App\Filament\Resources\ContentCategories\RelationManagers;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ContentsRelationManager extends RelationManager
{
    protected static string $relationship = 'contents';

    protected static ?string $title = 'Daftar Modul';

    public function form(Schema $form): Schema
    {
        return $form->schema([
            TextInput::make('sort_order')
                ->label('Urutan')
                ->numeric()
                ->integer()
                ->default(0)
                ->minValue(0)
                ->required(),

            TextInput::make('title')
                ->label('Judul Modul')
                ->required()
                ->maxLength(255)
                ->columnSpanFull(),

            Select::make('content_type')
                ->label('Tipe Konten')
                ->options([
                    'video' => 'Video',
                    'article' => 'Artikel',
                    'pdf' => 'PDF',
                ])
                ->native(false)
                ->required(),

            Select::make('status')
                ->label('Status')
                ->options([
                    'draft' => 'Draft',
                    'published' => 'Published',
                    'archived' => 'Archived',
                ])
                ->native(false)
                ->required(),

            TextInput::make('duration_sec')
                ->label('Durasi (detik)')
                ->numeric()
                ->integer()
                ->minValue(0)
                ->placeholder('0')
                ->helperText('Isi untuk konten bertipe video.'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                TextColumn::make('sort_order')
                    ->label('#')
                    ->sortable()
                    ->width('50px'),

                TextColumn::make('title')
                    ->label('Judul Modul')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('content_type')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'video' => 'info',
                        'article' => 'success',
                        'pdf' => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'published' => 'success',
                        'draft' => 'gray',
                        'archived' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('duration_sec')
                    ->label('Durasi')
                    ->formatStateUsing(fn (?int $state): string => $state
                        ? gmdate('i:s', $state)
                        : '—'
                    )
                    ->alignCenter(),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make(),
                    DeleteAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
