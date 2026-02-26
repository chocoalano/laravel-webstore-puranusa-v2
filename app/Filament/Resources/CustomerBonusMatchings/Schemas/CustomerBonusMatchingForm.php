<?php

namespace App\Filament\Resources\CustomerBonusMatchings\Schemas;

use App\Models\Customer;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class CustomerBonusMatchingForm
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
                        ->required()
                        ->placeholder('Pilih member penerima bonus...')
                        ->helperText('Bonus matching akan dicatat untuk member ini.')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 6,
                        ]),

                    Select::make('from_member_id')
                        ->label('Sumber Omzet (Downline)')
                        ->relationship('fromMember', 'name', fn (Builder $query): Builder => $query->latest('id'))
                        ->getOptionLabelFromRecordUsing(fn (Customer $record): string => self::customerLabel($record))
                        ->searchable(['name', 'email', 'ref_code', 'username'])
                        ->preload()
                        ->required()
                        ->different('member_id')
                        ->placeholder('Pilih member sumber...')
                        ->helperText('Member yang memicu bonus matching.')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 6,
                        ]),

                    TextInput::make('level')
                        ->label('Level Kedalaman')
                        ->required()
                        ->numeric()
                        ->minValue(1)
                        ->default(1)
                        ->placeholder('Contoh: 1')
                        ->helperText('Level jaringan sponsor yang menghasilkan bonus matching.')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 4,
                        ]),

                    TextInput::make('amount')
                        ->label('Nominal Bonus')
                        ->required()
                        ->numeric()
                        ->minValue(0)
                        ->default(0)
                        ->prefix('Rp')
                        ->placeholder('Contoh: 25000')
                        ->helperText('Nominal bonus matching yang diterima member.')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 4,
                        ]),

                    TextInput::make('index_value')
                        ->label('Nilai Index')
                        ->required()
                        ->numeric()
                        ->minValue(0)
                        ->default(0)
                        ->placeholder('Contoh: 1.2')
                        ->helperText('Nilai index perhitungan bonus matching.')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 4,
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
                            'md' => 4,
                        ]),

                    Textarea::make('description')
                        ->label('Keterangan')
                        ->rows(4)
                        ->placeholder('Contoh: Matching bonus level 2 plan A')
                        ->helperText('Catatan tambahan untuk audit bonus matching.')
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
