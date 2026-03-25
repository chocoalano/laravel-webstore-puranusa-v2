<?php

namespace App\Filament\Resources\BugReports\Schemas;

use App\Enums\Platform;
use App\Models\BugReport;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontFamily;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;

class BugReportInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            // ──────────────────────────────────────────────────────────────────
            // CALLOUT: Bug kritis / security
            // ──────────────────────────────────────────────────────────────────
            Callout::make(fn (BugReport $record): string => match ($record->severity?->value) {
                'critical' => 'Bug Kritis — Perlu Penanganan Segera',
                default => '',
            })
                ->description(fn (BugReport $record): string => match ($record->severity?->value) {
                    'critical' => 'Bug ini memiliki severity Critical. Segera assign ke developer dan tangani secepatnya.',
                    default => '',
                })
                ->color('danger')
                ->icon('heroicon-o-exclamation-triangle')
                ->visible(fn (BugReport $record): bool => $record->severity?->value === 'critical'),

            // ──────────────────────────────────────────────────────────────────
            // HEADER: Ringkasan Bug
            // ──────────────────────────────────────────────────────────────────
            Section::make('Ringkasan')
                ->description('Informasi utama laporan bug.')
                ->icon('heroicon-o-bug-ant')
                ->columns(['default' => 2, 'lg' => 4])
                ->schema([
                    TextEntry::make('title')
                        ->label('Judul Bug')
                        ->weight(FontWeight::Bold)
                        ->size(TextSize::Large)
                        ->columnSpanFull(),

                    TextEntry::make('status')
                        ->label('Status')
                        ->badge()
                        ->placeholder('-'),

                    TextEntry::make('severity')
                        ->label('Severity')
                        ->badge()
                        ->placeholder('-'),

                    TextEntry::make('priority')
                        ->label('Prioritas')
                        ->badge()
                        ->placeholder('-'),

                    TextEntry::make('error_category')
                        ->label('Kategori Error')
                        ->badge()
                        ->placeholder('Belum dikategorikan'),

                    TextEntry::make('platform')
                        ->label('Platform')
                        ->badge()
                        ->placeholder('-'),

                    TextEntry::make('source')
                        ->label('Sumber')
                        ->badge()
                        ->placeholder('-'),

                    TextEntry::make('assignee.name')
                        ->label('Ditugaskan Kepada')
                        ->icon('heroicon-o-user')
                        ->placeholder('Belum ditugaskan'),

                    TextEntry::make('reporter_type')
                        ->label('Tipe Pelapor')
                        ->badge()
                        ->placeholder('-'),
                ])->columnSpanFull(),

            // ──────────────────────────────────────────────────────────────────
            // TABS
            // ──────────────────────────────────────────────────────────────────
            Tabs::make('Detail')
                ->id('bug-report-infolist-tabs')
                ->persistTab()
                ->scrollable(false)
                ->contained(false)
                ->tabs([
                    self::tabDetail(),
                    self::tabPlatform(),
                    self::tabReporter(),
                    self::tabTimeline(),
                ])->columnSpanFull(),
        ]);
    }

    private static function tabDetail(): Tab
    {
        return Tab::make('Detail Bug')
            ->icon('heroicon-o-document-text')
            ->schema([
                Section::make('Deskripsi')
                    ->description('Penjelasan lengkap tentang bug yang dilaporkan.')
                    ->icon('heroicon-o-chat-bubble-left-ellipsis')
                    ->schema([
                        TextEntry::make('description')
                            ->label('Deskripsi Bug')
                            ->markdown()
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ]),

                Section::make('Reproduksi & Perilaku')
                    ->description('Langkah reproduksi dan perbedaan perilaku yang diharapkan vs aktual.')
                    ->icon('heroicon-o-arrow-path')
                    ->columns(['default' => 1, 'lg' => 2])
                    ->schema([
                        TextEntry::make('steps_to_reproduce')
                            ->label('Langkah Reproduksi')
                            ->markdown()
                            ->placeholder('-'),

                        TextEntry::make('expected_behavior')
                            ->label('Perilaku yang Diharapkan')
                            ->markdown()
                            ->placeholder('-'),

                        TextEntry::make('actual_behavior')
                            ->label('Perilaku yang Terjadi')
                            ->markdown()
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ]),

                Section::make('Resolusi')
                    ->description('Catatan penyelesaian atau penolakan bug.')
                    ->icon('heroicon-o-check-badge')
                    ->compact()
                    ->schema([
                        TextEntry::make('resolution_note')
                            ->label('Catatan Resolusi')
                            ->markdown()
                            ->placeholder('Belum ada catatan resolusi.')
                            ->columnSpanFull(),

                        TextEntry::make('duplicateOf.title')
                            ->label('Duplikat Dari')
                            ->icon('heroicon-o-document-duplicate')
                            ->placeholder('Bukan duplikat'),
                    ])
                    ->visible(fn (BugReport $record): bool => filled($record->resolution_note) || filled($record->duplicate_of_id)),
            ]);
    }

    private static function tabPlatform(): Tab
    {
        return Tab::make('Platform & Lingkungan')
            ->icon('heroicon-o-cpu-chip')
            ->columns(['default' => 1, 'lg' => 2])
            ->schema([
                Section::make('Konteks Platform')
                    ->description('Informasi dari mana bug ini dilaporkan.')
                    ->icon('heroicon-o-computer-desktop')
                    ->compact()
                    ->inlineLabel()
                    ->schema([
                        TextEntry::make('platform')
                            ->label('Platform')
                            ->badge()
                            ->placeholder('-'),

                        TextEntry::make('source')
                            ->label('Sumber Aplikasi')
                            ->badge()
                            ->placeholder('-'),

                        TextEntry::make('web_screen')
                            ->label('Ukuran Layar')
                            ->placeholder('-')
                            ->visible(fn (BugReport $record): bool => $record->platform === Platform::Web),

                        TextEntry::make('mobile_type')
                            ->label('OS Mobile')
                            ->badge()
                            ->placeholder('-')
                            ->visible(fn (BugReport $record): bool => $record->platform === Platform::Mobile),

                        TextEntry::make('page_url')
                            ->label('URL Halaman')
                            ->icon('heroicon-o-link')
                            ->copyable()
                            ->placeholder('-'),
                    ]),

                Section::make('Spesifikasi Teknis')
                    ->description('Detail perangkat dan lingkungan saat bug terjadi.')
                    ->icon('heroicon-o-wrench-screwdriver')
                    ->compact()
                    ->inlineLabel()
                    ->schema([
                        TextEntry::make('browser')
                            ->label('Browser')
                            ->placeholder('-')
                            ->formatStateUsing(fn (BugReport $record): string => trim(
                                implode(' ', array_filter([$record->browser, $record->browser_version]))
                            ) ?: '-'),

                        TextEntry::make('os')
                            ->label('Sistem Operasi')
                            ->placeholder('-')
                            ->formatStateUsing(fn (BugReport $record): string => trim(
                                implode(' ', array_filter([$record->os, $record->os_version]))
                            ) ?: '-'),

                        TextEntry::make('device_model')
                            ->label('Model Perangkat')
                            ->placeholder('-'),

                        TextEntry::make('screen_resolution')
                            ->label('Resolusi Layar')
                            ->fontFamily(FontFamily::Mono)
                            ->placeholder('-'),

                        TextEntry::make('app_version')
                            ->label('Versi Aplikasi')
                            ->badge()
                            ->placeholder('-'),
                    ]),
            ]);
    }

    private static function tabReporter(): Tab
    {
        return Tab::make('Pelapor')
            ->icon('heroicon-o-user')
            ->schema([
                Section::make('Identitas Pelapor')
                    ->description('Informasi tentang orang yang melaporkan bug.')
                    ->icon('heroicon-o-identification')
                    ->compact()
                    ->inlineLabel()
                    ->columns(['default' => 1, 'lg' => 2])
                    ->schema([
                        TextEntry::make('reporter_type')
                            ->label('Tipe Pelapor')
                            ->badge(),

                        TextEntry::make('reporterCustomer.name')
                            ->label('Nama Customer')
                            ->icon('heroicon-o-user')
                            ->placeholder('-')
                            ->visible(fn (BugReport $record): bool => $record->reporter_type?->value === 'customer'),

                        TextEntry::make('reporterCustomer.email')
                            ->label('Email Customer')
                            ->icon('heroicon-o-envelope')
                            ->copyable()
                            ->placeholder('-')
                            ->visible(fn (BugReport $record): bool => $record->reporter_type?->value === 'customer'),

                        TextEntry::make('reporterUser.name')
                            ->label('Nama User Internal')
                            ->icon('heroicon-o-user')
                            ->placeholder('-')
                            ->visible(fn (BugReport $record): bool => $record->reporter_type?->value === 'user'),

                        TextEntry::make('reporter_name')
                            ->label('Nama Pelapor')
                            ->placeholder('-')
                            ->visible(fn (BugReport $record): bool => $record->reporter_type?->value === 'anonymous'),

                        TextEntry::make('reporter_email')
                            ->label('Email Pelapor')
                            ->icon('heroicon-o-envelope')
                            ->copyable()
                            ->placeholder('-')
                            ->visible(fn (BugReport $record): bool => $record->reporter_type?->value === 'anonymous'),
                    ]),
            ]);
    }

    private static function tabTimeline(): Tab
    {
        return Tab::make('Timeline')
            ->icon('heroicon-o-clock')
            ->schema([
                Section::make('Jejak Waktu')
                    ->description('Timeline penanganan bug dari awal hingga selesai.')
                    ->icon('heroicon-o-calendar')
                    ->compact()
                    ->inlineLabel()
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Dilaporkan')
                            ->icon('heroicon-o-plus-circle')
                            ->dateTime()
                            ->placeholder('-'),

                        TextEntry::make('resolved_at')
                            ->label('Diselesaikan')
                            ->icon('heroicon-o-check-circle')
                            ->dateTime()
                            ->placeholder('Belum diselesaikan'),

                        TextEntry::make('closed_at')
                            ->label('Ditutup')
                            ->icon('heroicon-o-x-circle')
                            ->dateTime()
                            ->placeholder('Belum ditutup'),

                        TextEntry::make('updated_at')
                            ->label('Terakhir Diperbarui')
                            ->icon('heroicon-o-pencil-square')
                            ->dateTime()
                            ->placeholder('-'),
                    ]),
            ]);
    }
}
