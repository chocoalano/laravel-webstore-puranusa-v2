<?php

namespace App\Filament\Resources\WhatsAppBroadcasts\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class RecipientsRelationManager extends RelationManager
{
    protected static string $relationship = 'recipients';
    protected static ?string $title = 'Recipients';

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('sent_at', 'desc')
            ->columns([
                TextColumn::make('customer_name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->description(fn ($record) => $record->customer_id ? "ID: {$record->customer_id}" : null),

                TextColumn::make('phone')
                    ->label('Phone')
                    ->searchable()
                    ->copyable()
                    ->toggleable(),

                TextColumn::make('normalized_phone')
                    ->label('Normalized')
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (?string $state) => match ($state) {
                        'queued' => 'gray',
                        'processing' => 'warning',
                        'sent' => 'success',
                        'failed' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('sent_at')
                    ->label('Sent At')
                    ->dateTime()
                    ->placeholder('-')
                    ->sortable(),

                TextColumn::make('response_message')
                    ->label('Response')
                    ->wrap()
                    ->limit(60)
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'queued' => 'Queued',
                        'processing' => 'Processing',
                        'sent' => 'Sent',
                        'failed' => 'Failed',
                    ]),
            ]);
    }
}
