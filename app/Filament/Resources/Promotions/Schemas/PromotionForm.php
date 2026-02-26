<?php

namespace App\Filament\Resources\Promotions\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PromotionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(12)->schema([
                    Section::make('Informasi Promosi')
                        ->description('Data utama campaign promo.')
                        ->columns(12)
                        ->schema([
                            TextInput::make('code')
                                ->label('Kode Promo')
                                ->required()
                                ->maxLength(100)
                                ->unique(ignoreRecord: true)
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn ($state, callable $set) => $set('code', Str::upper(str_replace(' ', '-', (string) $state))))
                                ->columnSpan([
                                    'default' => 12,
                                    'lg' => 4,
                                ]),

                            TextInput::make('name')
                                ->label('Nama Promosi')
                                ->required()
                                ->maxLength(255)
                                ->live(onBlur: true)
                                ->afterStateUpdated(function ($state, callable $set, callable $get): void {
                                    if (blank($get('landing_slug')) && filled($state)) {
                                        $set('landing_slug', Str::slug((string) $state));
                                    }
                                })
                                ->columnSpan([
                                    'default' => 12,
                                    'lg' => 8,
                                ]),

                            Select::make('type')
                                ->label('Tipe')
                                ->options([
                                    'bundle' => 'Bundle',
                                    'flash_sale' => 'Flash Sale',
                                    'discount' => 'Discount',
                                ])
                                ->native(false)
                                ->required()
                                ->columnSpan([
                                    'default' => 12,
                                    'lg' => 4,
                                ]),

                            Toggle::make('is_active')
                                ->label('Aktif')
                                ->default(true)
                                ->required()
                                ->columnSpan([
                                    'default' => 12,
                                    'lg' => 4,
                                ]),

                            TextInput::make('priority')
                                ->label('Prioritas')
                                ->numeric()
                                ->default(0)
                                ->required()
                                ->columnSpan([
                                    'default' => 12,
                                    'lg' => 4,
                                ]),

                            TextInput::make('landing_slug')
                                ->label('Landing Slug')
                                ->maxLength(255)
                                ->helperText('URL slug untuk halaman promo, misal: promo-ramadan-2026.')
                                ->columnSpan([
                                    'default' => 12,
                                    'lg' => 6,
                                ]),

                            TextInput::make('show_on')
                                ->label('Ditampilkan Di')
                                ->maxLength(100)
                                ->placeholder('Contoh: homepage / product / cart / checkout / all')
                                ->columnSpan([
                                    'default' => 12,
                                    'lg' => 3,
                                ]),

                            TextInput::make('page')
                                ->label('Halaman Target')
                                ->maxLength(255)
                                ->placeholder('Contoh: /promo/ramadan')
                                ->columnSpan([
                                    'default' => 12,
                                    'lg' => 3,
                                ]),

                            DateTimePicker::make('start_at')
                                ->label('Mulai Berlaku')
                                ->seconds(false)
                                ->required()
                                ->columnSpan([
                                    'default' => 12,
                                    'lg' => 6,
                                ]),

                            DateTimePicker::make('end_at')
                                ->label('Berakhir')
                                ->seconds(false)
                                ->required()
                                ->afterOrEqual('start_at')
                                ->columnSpan([
                                    'default' => 12,
                                    'lg' => 6,
                                ]),
                        ])
                        ->columnSpan([
                            'default' => 12,
                            'lg' => 8,
                        ]),

                    Section::make('Konten & Aturan')
                        ->description('Deskripsi promo, batas penggunaan, dan kondisi khusus.')
                        ->columns(12)
                        ->schema([
                            FileUpload::make('image')
                                ->label('Gambar Promo')
                                ->image()
                                ->directory('promotions')
                                ->columnSpanFull(),

                            Textarea::make('description')
                                ->label('Deskripsi')
                                ->rows(4)
                                ->columnSpanFull(),

                            TextInput::make('max_redemption')
                                ->label('Maksimal Redemption Total')
                                ->numeric()
                                ->minValue(1)
                                ->placeholder('Kosongkan jika tidak dibatasi')
                                ->columnSpan([
                                    'default' => 12,
                                    'lg' => 6,
                                ]),

                            TextInput::make('per_user_limit')
                                ->label('Batas Per User')
                                ->numeric()
                                ->minValue(1)
                                ->placeholder('Kosongkan jika tidak dibatasi')
                                ->columnSpan([
                                    'default' => 12,
                                    'lg' => 6,
                                ]),

                            KeyValue::make('conditions_json')
                                ->label('Kondisi Promo (JSON)')
                                ->keyLabel('Kunci')
                                ->valueLabel('Nilai')
                                ->addActionLabel('Tambah Kondisi')
                                ->columnSpanFull(),

                            Textarea::make('custom_html')
                                ->label('Custom HTML')
                                ->rows(6)
                                ->columnSpanFull(),
                        ])
                        ->columnSpan([
                            'default' => 12,
                            'lg' => 4,
                        ]),
                ])->columnSpanFull(),
            ]);
    }
}
