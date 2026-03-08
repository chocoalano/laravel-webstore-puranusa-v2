<?php

namespace App\Filament\Resources\Promotions\Schemas;

use App\Support\Media\PublicMediaUrl;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\RepeatableEntry\TableColumn;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PromotionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informasi Promosi')
                ->columns(2)
                ->schema([
                    TextEntry::make('code')
                        ->label('Kode'),
                    TextEntry::make('name')
                        ->label('Nama'),
                    TextEntry::make('type')
                        ->label('Tipe')
                        ->badge(),
                    TextEntry::make('landing_slug')
                        ->label('Landing Slug')
                        ->placeholder('-'),
                    TextEntry::make('description')
                        ->label('Deskripsi')
                        ->placeholder('-')
                        ->columnSpanFull(),
                    ImageEntry::make('image')
                        ->label('Gambar')
                        ->state(function (object $record): ?string {
                            $imageUrl = PublicMediaUrl::resolve($record->image);

                            if (! filled($imageUrl)) {
                                return null;
                            }

                            if (
                                str_starts_with($imageUrl, 'http://')
                                || str_starts_with($imageUrl, 'https://')
                                || str_starts_with($imageUrl, 'data:')
                            ) {
                                return $imageUrl;
                            }

                            return url($imageUrl);
                        })
                        ->placeholder('-'),
                    TextEntry::make('start_at')
                        ->label('Mulai')
                        ->dateTime(),
                    TextEntry::make('end_at')
                        ->label('Berakhir')
                        ->dateTime(),
                    IconEntry::make('is_active')
                        ->label('Aktif')
                        ->boolean(),
                    TextEntry::make('priority')
                        ->label('Prioritas')
                        ->numeric(),
                    TextEntry::make('max_redemption')
                        ->label('Maks. Redeem')
                        ->numeric()
                        ->placeholder('-'),
                    TextEntry::make('per_user_limit')
                        ->label('Batas per User')
                        ->numeric()
                        ->placeholder('-'),
                    TextEntry::make('conditions_json')
                        ->label('Kondisi')
                        ->placeholder('-')
                        ->columnSpanFull(),
                    TextEntry::make('show_on')
                        ->label('Tampil di')
                        ->placeholder('-'),
                    TextEntry::make('custom_html')
                        ->label('Custom HTML')
                        ->placeholder('-')
                        ->columnSpanFull(),
                    TextEntry::make('page')
                        ->label('Halaman')
                        ->placeholder('-'),
                    TextEntry::make('created_at')
                        ->label('Dibuat')
                        ->dateTime()
                        ->placeholder('-'),
                    TextEntry::make('updated_at')
                        ->label('Diperbarui')
                        ->dateTime()
                        ->placeholder('-'),
                ]),

            Section::make('Produk Terkait')
                ->description('Relasi produk dari promosi ini.')
                ->schema([
                    TextEntry::make('products_count')
                        ->label('Total Produk')
                        ->state(fn ($record): int => $record->products()->count())
                        ->numeric(),

                    RepeatableEntry::make('products')
                        ->label('')
                        ->contained(false)
                        ->table([
                            TableColumn::make('SKU'),
                            TableColumn::make('Produk'),
                            TableColumn::make('Harga Dasar'),
                            TableColumn::make('Aktif'),
                            TableColumn::make('Min Qty'),
                            TableColumn::make('Diskon Nominal'),
                            TableColumn::make('Diskon %'),
                            TableColumn::make('Harga Bundle'),
                        ])
                        ->schema([
                            TextEntry::make('sku')
                                ->label('SKU')
                                ->placeholder('-'),
                            TextEntry::make('name')
                                ->label('Produk')
                                ->placeholder('-'),
                            TextEntry::make('base_price')
                                ->label('Harga Dasar')
                                ->money('IDR')
                                ->placeholder('-'),
                            IconEntry::make('is_active')
                                ->label('Aktif')
                                ->boolean(),
                            TextEntry::make('pivot.min_qty')
                                ->label('Min Qty')
                                ->numeric()
                                ->placeholder('-'),
                            TextEntry::make('pivot.discount_value')
                                ->label('Diskon Nominal')
                                ->money('IDR')
                                ->placeholder('-'),
                            TextEntry::make('pivot.discount_percent')
                                ->label('Diskon %')
                                ->suffix('%')
                                ->numeric()
                                ->placeholder('-'),
                            TextEntry::make('pivot.bundle_price')
                                ->label('Harga Bundle')
                                ->money('IDR')
                                ->placeholder('-'),
                        ]),
                ]),
        ]);
    }
}
