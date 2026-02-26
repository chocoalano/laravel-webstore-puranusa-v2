<?php

namespace App\Filament\Resources\ShippingTargets\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class ShippingTargetForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(12)
                ->schema([
                    TextInput::make('three_lc_code')
                        ->label('3LC Code')
                        ->required()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true)
                        ->placeholder('Contoh: CGK')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 3,
                        ]),

                    TextInput::make('country')
                        ->label('Negara')
                        ->required()
                        ->default('Indonesia')
                        ->maxLength(255)
                        ->columnSpan([
                            'default' => 12,
                            'md' => 3,
                        ]),

                    TextInput::make('province_id')
                        ->label('Province ID')
                        ->numeric()
                        ->minValue(1)
                        ->placeholder('Auto')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 3,
                        ]),

                    TextInput::make('city_id')
                        ->label('City ID')
                        ->numeric()
                        ->minValue(1)
                        ->placeholder('Auto')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 3,
                        ]),

                    TextInput::make('province')
                        ->label('Provinsi')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('Contoh: BANTEN')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 6,
                        ]),

                    TextInput::make('city')
                        ->label('Kota / Kabupaten')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('Contoh: TANGERANG')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 6,
                        ]),

                    TextInput::make('district')
                        ->label('Kecamatan')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('Contoh: CIKUPA')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 6,
                        ]),

                    TextInput::make('district_lion')
                        ->label('Kecamatan (Lion)')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('Contoh: CIKUPA')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 6,
                        ]),
                ])
                ->columnSpanFull(),
        ]);
    }
}
