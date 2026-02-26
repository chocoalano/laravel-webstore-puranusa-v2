<?php

namespace App\Filament\Resources\WhatsAppBroadcasts\Schemas;

use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Hidden;
use Filament\Schemas\Components\Section;
use Illuminate\Support\Str;

class WhatsAppBroadcastForm
{
    public static function configure($schema)
    {
        return $schema->components([
            // Seksi Utama: Pengaturan API Qontak
            Section::make('Konfigurasi API Qontak')
                ->description('Hubungkan pesan dengan Template ID yang terdaftar di Dashboard Qontak.')
                ->schema([
                    Placeholder::make('qontak_info')
                        ->label('Informasi Gateway')
                        ->content('Penyedia: Qontak WA Gateway (API Based)')
                        ->extraAttributes(['class' => 'text-primary-600 font-bold']),

                    TextInput::make('template_id')
                        ->label('Template ID Qontak')
                        ->helperText('Masukkan UUID template yang didapat dari Qontak (Contoh: 52cfcf36-cd75-...)')
                        ->placeholder('Isi ID template untuk pesan resmi (HSM)')
                        ->maxLength(100)
                        ->live() // Update otomatis ke display bawah
                        ->columnSpanFull()
                        // Memberikan aksi tombol di dalam field
                        ->suffixAction(
                            Action::make('open_qontak')
                                ->label('Buka Qontak')
                                ->icon('heroicon-m-arrow-top-right-on-square')
                                ->url('https://dashboard.qontak.com/')
                                ->openUrlInNewTab()
                        ),
                ])
                ->icon('heroicon-o-key')
                ->collapsible(),
            Section::make('Informasi Teknis Broadcast')
                ->description('Detail teknis yang akan dikirimkan ke server gateway.')
                ->schema([
                    // Menggunakan Hint visual untuk mengarahkan user
                    Placeholder::make('technical_hint')
                        ->label('Status Validasi')
                        ->content('Sistem akan mengirimkan pesan menggunakan Template ID di bawah ini.')
                        ->columnSpanFull(),

                    TextInput::make('display_template_id')
                        ->label('ID Terdeteksi')
                        ->default('52cfcf36-cd75-44a3-8708-2eafe53e6f14')
                        // Mengambil nilai dari input template_id di atas secara real-time
                        ->formatStateUsing(fn ($get, $state) => $get('template_id') ?: $state)
                        ->disabled()
                        ->dehydrated()
                        ->columnSpanFull()
                        ->extraInputAttributes(['class' => 'bg-gray-50 font-mono text-xs']),

                    Hidden::make('created_by')
                        ->default(fn () => auth()->id())
                        ->dehydrated(),
                ])
                ->icon('heroicon-o-information-circle')
                ->columns(2)
                ->compact(),

            Section::make('Konten Pesan')
                ->description('Susun pesan yang akan dikirimkan ke pelanggan.')
                ->schema([
                    TextInput::make('title')
                        ->label('Nama Kampanye / Judul')
                        ->placeholder('Misal: Promo Ramadhan 2024')
                        ->helperText('Hanya untuk keperluan arsip internal.')
                        ->required()
                        ->columnSpanFull()
                        ->live(debounce: 300)
                        ->afterStateUpdated(fn ($state, $set) => $set('title', trim((string) $state))),

                    Textarea::make('message')
                        ->label('Isi Pesan WhatsApp')
                        ->helperText('Pastikan variabel (seperti {{1}}) sesuai dengan yang didaftarkan di Qontak.')
                        ->required()
                        ->rows(6)
                        ->columnSpanFull()
                        ->live(debounce: 300),

                    Placeholder::make('message_stats')
                        ->label('Statistik Karakter')
                        ->content(function (callable $get): string {
                            $len = Str::length((string) ($get('message') ?? ''));
                            return "{$len} karakter terpakai.";
                        })
                        ->extraAttributes(['class' => 'text-sm text-gray-500']),
                ])->columnSpanFull(),
        ]);
    }
}
