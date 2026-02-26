<?php

namespace App\Filament\Resources\WhatsAppBroadcasts\Tables;

use App\Models\WhatsAppBroadcast;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class WhatsAppBroadcastTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->description(fn (WhatsAppBroadcast $record) => $record->template_id ? "Template: {$record->template_id}" : null),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (?string $state) => match ($state) {
                        'draft' => 'gray',
                        'processing' => 'warning',
                        'sent' => 'success',
                        'partial' => 'warning',
                        'failed' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),

                // ✅ FIX: progress dibuat string langsung, description ambil dari record
                TextColumn::make('progress')
                    ->label('Progress')
                    ->state(function (WhatsAppBroadcast $r): string {
                        $total = max(0, (int) $r->total_recipients);
                        $ok = max(0, (int) $r->success_recipients);

                        if ($total === 0) {
                            return '0/0 (0%)';
                        }

                        $pct = (int) round(($ok / $total) * 100);
                        return "{$ok}/{$total} ({$pct}%)";
                    })
                    ->description(function (WhatsAppBroadcast $r): ?string {
                        $fail = max(0, (int) $r->failed_recipients);
                        return $fail > 0 ? "Gagal: {$fail}" : null;
                    })
                    ->sortable(query: function ($query, string $direction) {
                        $query->orderBy('success_recipients', $direction);
                    }),

                TextColumn::make('creator.name')
                    ->label('Dibuat Oleh')
                    ->placeholder('-')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('sent_at')
                    ->label('Terkirim')
                    ->dateTime()
                    ->placeholder('-')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Diubah')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'draft' => 'Draft',
                        'processing' => 'Processing',
                        'sent' => 'Sent',
                        'partial' => 'Partial',
                        'failed' => 'Failed',
                    ]),

                SelectFilter::make('created_by')
                    ->label('Dibuat Oleh')
                    ->relationship('creator', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                Action::make('resend')
                    ->icon(Heroicon::OutlinedArrowRightEndOnRectangle)
                    ->action(fn() => dd('resend')),
                ViewAction::make(),

                EditAction::make()
                    ->visible(fn (WhatsAppBroadcast $record) => in_array($record->status, ['draft', 'failed'], true))
                    ->tooltip('Hanya Draft/Failed yang boleh diubah.'),

                DeleteAction::make()
                    ->visible(fn (WhatsAppBroadcast $record) => $record->status === 'draft')
                    ->tooltip('Hanya Draft yang boleh dihapus.'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Hapus Terpilih')
                        ->requiresConfirmation(),
                ]),
            ]);
    }
}
