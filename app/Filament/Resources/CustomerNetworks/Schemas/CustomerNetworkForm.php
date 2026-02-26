<?php

namespace App\Filament\Resources\CustomerNetworks\Schemas;

use App\Models\Customer;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class CustomerNetworkForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(12)
                ->schema([
                    Select::make('member_id')
                        ->label('Member')
                        ->relationship('member', 'name', fn (Builder $query): Builder => $query->latest('id'))
                        ->getOptionLabelFromRecordUsing(fn (Customer $record): string => $record->name . ($record->ref_code ? ' (' . $record->ref_code . ')' : ''))
                        ->searchable(['name', 'email', 'ref_code', 'username'])
                        ->preload()
                        ->required()
                        ->placeholder('Pilih member...')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 5,
                        ]),

                    Select::make('upline_id')
                        ->label('Upline')
                        ->relationship('upline', 'name', fn (Builder $query): Builder => $query->latest('id'))
                        ->getOptionLabelFromRecordUsing(fn (Customer $record): string => $record->name . ($record->ref_code ? ' (' . $record->ref_code . ')' : ''))
                        ->searchable(['name', 'email', 'ref_code', 'username'])
                        ->preload()
                        ->placeholder('Pilih upline (opsional)...')
                        ->helperText('Kosongkan jika node ini merupakan root jaringan.')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 5,
                        ]),

                    Select::make('position')
                        ->label('Posisi')
                        ->options(self::positionOptions())
                        ->required()
                        ->native(false)
                        ->default('left')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 2,
                        ]),

                    Select::make('status')
                        ->label('Status')
                        ->options(self::statusOptions())
                        ->required()
                        ->native(false)
                        ->default(1)
                        ->columnSpan([
                            'default' => 12,
                            'md' => 3,
                        ]),

                    TextInput::make('level')
                        ->label('Level')
                        ->required()
                        ->numeric()
                        ->minValue(1)
                        ->default(1)
                        ->columnSpan([
                            'default' => 12,
                            'md' => 3,
                        ]),

                    Textarea::make('description')
                        ->label('Keterangan')
                        ->rows(3)
                        ->placeholder('Catatan tambahan jaringan...')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 12,
                        ]),
                ])
                ->columnSpanFull(),
        ]);
    }

    private static function positionOptions(): array
    {
        return [
            'left' => 'Kiri',
            'right' => 'Kanan',
        ];
    }

    private static function statusOptions(): array
    {
        return [
            1 => 'Aktif',
            0 => 'Tidak Aktif',
        ];
    }
}
