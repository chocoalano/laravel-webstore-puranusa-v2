<?php

namespace App\Filament\Resources\CustomerBonusPairings\Schemas;

use App\Models\Customer;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class CustomerBonusPairingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(12)
                ->schema([
                    Select::make('member_id')
                        ->label('Member Penerima Bonus')
                        ->relationship('member', 'name', fn (Builder $query): Builder => $query->latest('id'))
                        ->getOptionLabelFromRecordUsing(fn (Customer $record): string => self::customerLabel($record))
                        ->searchable(['name', 'email', 'ref_code', 'username'])
                        ->preload()
                        ->placeholder('Pilih member penerima bonus...')
                        ->helperText('Bonus pairing akan dicatat untuk member ini.')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 6,
                        ]),

                    Select::make('source_member_id')
                        ->label('Sumber Pairing')
                        ->relationship('sourceMember', 'name', fn (Builder $query): Builder => $query->latest('id'))
                        ->getOptionLabelFromRecordUsing(fn (Customer $record): string => self::customerLabel($record))
                        ->searchable(['name', 'email', 'ref_code', 'username'])
                        ->preload()
                        ->required()
                        ->different('member_id')
                        ->placeholder('Pilih member sumber pairing...')
                        ->helperText('Member yang menjadi sumber pairing.')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 6,
                        ]),

                    TextInput::make('pairing_count')
                        ->label('Jumlah Pair')
                        ->required()
                        ->numeric()
                        ->minValue(0)
                        ->default(0)
                        ->placeholder('Contoh: 3')
                        ->helperText('Jumlah pasangan (pair) yang tercapai.')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 3,
                        ]),

                    TextInput::make('amount')
                        ->label('Nominal Bonus')
                        ->required()
                        ->numeric()
                        ->minValue(0)
                        ->default(0)
                        ->prefix('Rp')
                        ->placeholder('Contoh: 50000')
                        ->helperText('Nominal bonus pairing yang diterima.')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 3,
                        ]),

                    TextInput::make('index_value')
                        ->label('Nilai Index')
                        ->required()
                        ->numeric()
                        ->minValue(0)
                        ->default(0)
                        ->placeholder('Contoh: 1.5')
                        ->helperText('Nilai index perhitungan bonus pairing.')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 3,
                        ]),

                    Select::make('status')
                        ->label('Status Bonus')
                        ->required()
                        ->native(false)
                        ->options(self::statusOptions())
                        ->default(0)
                        ->dehydrateStateUsing(fn (mixed $state): int => (int) $state)
                        ->helperText('Status bonus: menunggu pencairan atau sudah dirilis.')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 3,
                        ]),

                    DatePicker::make('pairing_date')
                        ->label('Tanggal Pairing')
                        ->placeholder('Pilih tanggal...')
                        ->helperText('Tanggal terjadinya pairing.')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 4,
                        ]),

                    Textarea::make('description')
                        ->label('Keterangan')
                        ->rows(4)
                        ->placeholder('Contoh: Pairing bonus binary kaki kiri-kanan')
                        ->helperText('Catatan tambahan untuk audit bonus pairing.')
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
