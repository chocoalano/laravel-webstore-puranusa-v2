<?php

namespace App\Filament\Resources\CustomerBonusPairings\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class CustomerBonusPairingInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('member.name')
                ->label('Member Penerima')
                ->placeholder('-'),

            TextEntry::make('member.ref_code')
                ->label('Ref Penerima')
                ->placeholder('-'),

            TextEntry::make('sourceMember.name')
                ->label('Sumber Pairing')
                ->placeholder('-'),

            TextEntry::make('sourceMember.ref_code')
                ->label('Ref Sumber')
                ->placeholder('-'),

            TextEntry::make('pairing_count')
                ->label('Jumlah Pair')
                ->numeric()
                ->placeholder('-'),

            TextEntry::make('amount')
                ->label('Nominal Bonus')
                ->money('IDR')
                ->placeholder('-'),

            TextEntry::make('index_value')
                ->label('Nilai Index')
                ->numeric(decimalPlaces: 2)
                ->placeholder('-'),

            TextEntry::make('status')
                ->label('Status Bonus')
                ->badge()
                ->formatStateUsing(fn (mixed $state): string => self::statusOptions()[(int) $state] ?? '-')
                ->color(fn (mixed $state): string => (int) $state === 1 ? 'success' : 'warning'),

            TextEntry::make('pairing_date')
                ->label('Tanggal Pairing')
                ->date()
                ->placeholder('-'),

            TextEntry::make('description')
                ->label('Keterangan')
                ->placeholder('-')
                ->columnSpanFull(),

            TextEntry::make('created_at')
                ->label('Dibuat')
                ->dateTime()
                ->placeholder('-'),

            TextEntry::make('updated_at')
                ->label('Diperbarui')
                ->dateTime()
                ->placeholder('-'),
        ]);
    }

    private static function statusOptions(): array
    {
        return [
            0 => 'Menunggu Pencairan',
            1 => 'Sudah Dirilis',
        ];
    }
}
