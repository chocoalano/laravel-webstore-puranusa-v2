<?php

namespace App\Filament\Resources\CustomerBonusLifetimeCashRewards\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class CustomerBonusLifetimeCashRewardInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('member.name')
                ->label('Member Penerima')
                ->placeholder('-'),

            TextEntry::make('member.ref_code')
                ->label('Ref Member')
                ->placeholder('-'),

            TextEntry::make('reward_name')
                ->label('Nama Reward')
                ->placeholder('-'),

            TextEntry::make('reward')
                ->label('Nilai Target Reward')
                ->money('IDR')
                ->placeholder('-'),

            TextEntry::make('amount')
                ->label('Nominal Diterima')
                ->money('IDR')
                ->placeholder('-'),

            TextEntry::make('bv')
                ->label('Business Volume (BV)')
                ->numeric(decimalPlaces: 2)
                ->placeholder('-'),

            TextEntry::make('status')
                ->label('Status Reward')
                ->badge()
                ->formatStateUsing(fn (mixed $state): string => self::statusOptions()[(int) $state] ?? '-')
                ->color(fn (mixed $state): string => (int) $state === 1 ? 'success' : 'warning'),

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
