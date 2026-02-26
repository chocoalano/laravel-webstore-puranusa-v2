<?php

namespace App\Filament\Resources\WhatsAppBroadcasts\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class WhatsAppBroadcastInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Ringkasan')
                ->columns(2)
                ->schema([
                    TextEntry::make('title')->label('Judul'),
                    TextEntry::make('status')
                        ->label('Status')
                        ->badge()
                        ->color(fn (?string $state) => match ($state) {
                            'draft' => 'gray',
                            'processing' => 'warning',
                            'sent' => 'success',
                            'partial' => 'warning',
                            'failed' => 'danger',
                            default => 'gray',
                        }),

                    TextEntry::make('template_id')->label('Template ID')->placeholder('-'),
                    TextEntry::make('creator.name')->label('Dibuat Oleh')->placeholder('-'),
                    TextEntry::make('sent_at')->label('Waktu Kirim')->dateTime()->placeholder('-'),
                    TextEntry::make('created_at')->label('Dibuat')->dateTime()->placeholder('-'),
                    TextEntry::make('updated_at')->label('Diubah')->dateTime()->placeholder('-'),
                ]),

            Section::make('Pesan')
                ->schema([
                    TextEntry::make('message')->label('Isi Pesan')->columnSpanFull(),
                ]),

            Section::make('Statistik Penerima')
                ->schema([
                    Grid::make(3)->schema([
                        TextEntry::make('total_recipients')->label('Total')->numeric(),
                        TextEntry::make('success_recipients')->label('Berhasil')->numeric(),
                        TextEntry::make('failed_recipients')->label('Gagal')->numeric(),
                    ]),
                    TextEntry::make('last_error')->label('Error Terakhir')->placeholder('-')->columnSpanFull(),
                ]),
        ]);
    }
}
