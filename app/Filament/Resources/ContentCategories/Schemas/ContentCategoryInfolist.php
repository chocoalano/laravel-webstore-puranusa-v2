<?php

namespace App\Filament\Resources\ContentCategories\Schemas;

use Filament\Infolists\Components\ColorEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ContentCategoryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(12)->schema([

                Section::make('Informasi Kursus')
                    ->icon('heroicon-o-academic-cap')
                    ->schema([
                        TextEntry::make('name')
                            ->label('Nama Kursus'),

                        TextEntry::make('slug')
                            ->label('Slug'),

                        TextEntry::make('parent.name')
                            ->label('Kategori Induk')
                            ->placeholder('—'),

                        TextEntry::make('sort_order')
                            ->label('Urutan Tampil'),

                        TextEntry::make('contents_count')
                            ->label('Jumlah Modul')
                            ->state(fn ($record) => $record->contents()->count()),

                        TextEntry::make('created_at')
                            ->label('Dibuat')
                            ->dateTime()
                            ->placeholder('—'),

                        TextEntry::make('updated_at')
                            ->label('Diperbarui')
                            ->dateTime()
                            ->placeholder('—'),
                    ])
                    ->columns(2)
                    ->columnSpan(['default' => 12, 'lg' => 8]),

                Section::make('Tampilan UI')
                    ->icon('heroicon-o-paint-brush')
                    ->schema([
                        TextEntry::make('icon_key')
                            ->label('Kunci Ikon')
                            ->placeholder('—'),

                        ColorEntry::make('accent_hex')
                            ->label('Warna Aksen')
                            ->placeholder('—'),

                        ImageEntry::make('thumbnail_url')
                            ->label('Thumbnail')
                            ->columnSpanFull(),
                    ])
                    ->columnSpan(['default' => 12, 'lg' => 4]),

            ])->columnSpanFull(),
        ]);
    }
}
