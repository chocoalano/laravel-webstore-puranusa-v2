<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\RepeatableEntry\TableColumn;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // =========================================================
                // IDENTITAS PRODUK
                // =========================================================
                Section::make('Identitas Produk')
                    ->description('Informasi dasar produk untuk identifikasi dan penelusuran.')
                    ->schema([
                        TextEntry::make('sku')
                            ->label('SKU')
                            ->placeholder('-'),

                        TextEntry::make('slug')
                            ->label('Slug')
                            ->placeholder('-'),

                        TextEntry::make('name')
                            ->label('Nama Produk')
                            ->placeholder('-'),

                        TextEntry::make('brand')
                            ->label('Merek')
                            ->placeholder('-'),

                        IconEntry::make('is_active')
                            ->label('Status Aktif')
                            ->boolean(),
                    ])->columnSpan(1),

                // =========================================================
                // HARGA & STOK
                // =========================================================
                Section::make('Harga & Stok')
                    ->description('Harga dasar, mata uang, dan ketersediaan stok.')
                    ->schema([
                        TextEntry::make('base_price')
                            ->label('Harga Dasar')
                            ->money('IDR')
                            ->placeholder('-'),

                        TextEntry::make('currency')
                            ->label('Mata Uang')
                            ->placeholder('-'),

                        TextEntry::make('stock')
                            ->label('Stok')
                            ->numeric()
                            ->placeholder('-'),

                        TextEntry::make('warranty_months')
                            ->label('Garansi (Bulan)')
                            ->numeric()
                            ->placeholder('-'),
                    ])->columnSpan(2),

                // =========================================================
                // DESKRIPSI
                // =========================================================
                Section::make('Deskripsi')
                    ->description('Ringkasan dan deskripsi lengkap produk.')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('short_desc')
                            ->label('Deskripsi Singkat')
                            ->placeholder('-')
                            ->html()
                            ->columnSpanFull(),

                        TextEntry::make('long_desc')
                            ->label('Deskripsi Lengkap')
                            ->placeholder('-')
                            ->html()
                            ->columnSpanFull(),
                    ])->columnSpanFull(),

                // =========================================================
                // RELASI VARIAN / KATEGORI & GAMBAR
                // =========================================================
                Section::make('Varian & Gambar Terkait')
                    ->description('Relasi varian/kategori dan media gambar produk.')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('categories.name')
                            ->label('Varian / Kategori')
                            ->badge()
                            ->listWithLineBreaks()
                            ->limitList(8)
                            ->expandableLimitedList()
                            ->placeholder('-')
                            ->columnSpanFull(),

                        RepeatableEntry::make('primaryMedia')
                            ->label('Gambar Utama')
                            ->placeholder('Belum ada gambar utama.')
                            ->contained(false)
                            ->columns(2)
                            ->schema([
                                ImageEntry::make('url')
                                    ->label('Preview')
                                    ->disk('public')
                                    ->imageHeight(180)
                                    ->square(),
                                TextEntry::make('type')
                                    ->label('Tipe')
                                    ->badge(),
                                TextEntry::make('alt_text')
                                    ->label('Alt Text')
                                    ->placeholder('-'),
                                TextEntry::make('sort_order')
                                    ->label('Urutan')
                                    ->numeric()
                                    ->placeholder('-'),
                            ])
                            ->columnSpan(1),

                        RepeatableEntry::make('media')
                            ->label('Galeri Media')
                            ->placeholder('Belum ada media tambahan.')
                            ->contained(false)
                            ->schema([
                                ImageEntry::make('url')
                                    ->label('Preview')
                                    ->disk('public')
                                    ->imageHeight(80)
                                    ->square(),
                                TextEntry::make('type')
                                    ->label('Tipe')
                                    ->badge(),
                                TextEntry::make('alt_text')
                                    ->label('Alt Text')
                                    ->placeholder('-')
                                    ->limit(40),
                                TextEntry::make('sort_order')
                                    ->label('Urutan')
                                    ->numeric()
                                    ->placeholder('-'),
                                IconEntry::make('is_primary')
                                    ->label('Utama')
                                    ->boolean(),
                            ])
                            ->table([
                                TableColumn::make('Preview')->width('92px'),
                                TableColumn::make('Tipe'),
                                TableColumn::make('Alt Text')->wrapHeader(),
                                TableColumn::make('Urutan'),
                                TableColumn::make('Utama'),
                            ])
                            ->columnSpan(2),
                    ])->columnSpanFull(),

                // =========================================================
                // DIMENSI & BERAT
                // =========================================================
                Section::make('Dimensi & Berat')
                    ->description('Ukuran fisik untuk kebutuhan pengiriman (opsional).')
                    ->schema([
                        TextEntry::make('weight_gram')
                            ->label('Berat (gram)')
                            ->numeric()
                            ->placeholder('-'),

                        TextEntry::make('length_mm')
                            ->label('Panjang (mm)')
                            ->numeric()
                            ->placeholder('-'),

                        TextEntry::make('width_mm')
                            ->label('Lebar (mm)')
                            ->numeric()
                            ->placeholder('-'),

                        TextEntry::make('height_mm')
                            ->label('Tinggi (mm)')
                            ->numeric()
                            ->placeholder('-'),
                    ]),

                // =========================================================
                // BONUS / INSENTIF (MLM / REWARD)
                // =========================================================
                Section::make('Bonus & Insentif')
                    ->description('Nilai bonus/insentif yang melekat pada produk.')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('bv')
                            ->label('BV')
                            ->numeric()
                            ->placeholder('-'),

                        TextEntry::make('b_sponsor')
                            ->label('Bonus Sponsor')
                            ->numeric()
                            ->placeholder('-'),

                        TextEntry::make('b_matching')
                            ->label('Bonus Matching')
                            ->numeric()
                            ->placeholder('-'),

                        TextEntry::make('b_pairing')
                            ->label('Bonus Pairing')
                            ->numeric()
                            ->placeholder('-'),

                        TextEntry::make('b_cashback')
                            ->label('Bonus Cashback')
                            ->numeric()
                            ->placeholder('-'),

                        TextEntry::make('b_retail')
                            ->label('Bonus Retail')
                            ->numeric()
                            ->placeholder('-'),

                        TextEntry::make('b_stockist')
                            ->label('Bonus Stockist')
                            ->numeric()
                            ->placeholder('-'),
                    ]),

                // =========================================================
                // INFORMASI SISTEM
                // =========================================================
                Section::make('Informasi Sistem')
                    ->description('Metadata pembuatan dan pembaruan data.')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Dibuat Pada')
                            ->dateTime()
                            ->placeholder('-'),

                        TextEntry::make('updated_at')
                            ->label('Diperbarui Pada')
                            ->dateTime()
                            ->placeholder('-'),
                    ]),
            ])
            ->columns(3);
    }
}
