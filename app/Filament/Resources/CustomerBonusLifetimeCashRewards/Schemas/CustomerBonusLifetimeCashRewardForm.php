<?php

namespace App\Filament\Resources\CustomerBonusLifetimeCashRewards\Schemas;

use App\Models\Customer;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class CustomerBonusLifetimeCashRewardForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(12)
                ->schema([
                    Select::make('member_id')
                        ->label('Member Penerima')
                        ->relationship('member', 'name', fn (Builder $query): Builder => $query->latest('id'))
                        ->getOptionLabelFromRecordUsing(fn (Customer $record): string => self::customerLabel($record))
                        ->searchable(['name', 'email', 'ref_code', 'username'])
                        ->preload()
                        ->required()
                        ->placeholder('Pilih member penerima reward...')
                        ->helperText('Lifetime cash reward akan dicatat untuk member ini.')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 6,
                        ]),

                    TextInput::make('reward_name')
                        ->label('Nama Reward')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('Contoh: Zenner Elite')
                        ->helperText('Nama tingkatan reward yang diraih member.')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 6,
                        ]),

                    TextInput::make('reward')
                        ->label('Nilai Target Reward')
                        ->required()
                        ->numeric()
                        ->minValue(0)
                        ->default(0)
                        ->prefix('Rp')
                        ->placeholder('Contoh: 15000000')
                        ->helperText('Nilai reward target yang harus dicapai.')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 4,
                        ]),

                    TextInput::make('amount')
                        ->label('Nominal Diterima')
                        ->required()
                        ->numeric()
                        ->minValue(0)
                        ->default(0)
                        ->prefix('Rp')
                        ->placeholder('Contoh: 15000000')
                        ->helperText('Nominal cash reward yang diterima member.')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 4,
                        ]),

                    TextInput::make('bv')
                        ->label('Business Volume (BV)')
                        ->required()
                        ->numeric()
                        ->minValue(0)
                        ->default(0)
                        ->placeholder('Contoh: 350000000')
                        ->helperText('Jumlah BV syarat untuk mendapatkan reward ini.')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 4,
                        ]),

                    Select::make('status')
                        ->label('Status Reward')
                        ->required()
                        ->native(false)
                        ->options(self::statusOptions())
                        ->default(0)
                        ->dehydrateStateUsing(fn (mixed $state): int => (int) $state)
                        ->helperText('Status reward: menunggu pencairan atau sudah dirilis.')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 4,
                        ]),

                    Textarea::make('description')
                        ->label('Keterangan')
                        ->rows(4)
                        ->placeholder('Contoh: Reward lifetime level Elite bulan Januari')
                        ->helperText('Catatan tambahan untuk audit lifetime cash reward.')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 8,
                        ]),
                ])
                ->columnSpanFull(),
        ]);
    }

    private static function customerLabel(Customer $customer): string
    {
        $refCode = $customer->ref_code ? ' (' . $customer->ref_code . ')' : '';

        return $customer->name . $refCode;
    }

    private static function statusOptions(): array
    {
        return [
            0 => 'Menunggu Pencairan',
            1 => 'Sudah Dirilis',
        ];
    }
}
