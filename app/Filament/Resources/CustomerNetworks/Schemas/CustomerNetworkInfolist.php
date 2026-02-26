<?php

namespace App\Filament\Resources\CustomerNetworks\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class CustomerNetworkInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('member.name')
                ->label('Member')
                ->placeholder('-'),

            TextEntry::make('member.ref_code')
                ->label('Kode Member')
                ->placeholder('-'),

            TextEntry::make('upline.name')
                ->label('Upline')
                ->placeholder('-'),

            TextEntry::make('upline.ref_code')
                ->label('Kode Upline')
                ->placeholder('-'),

            TextEntry::make('position')
                ->label('Posisi')
                ->badge()
                ->formatStateUsing(fn (?string $state): string => self::positionOptions()[$state ?? ''] ?? '-')
                ->color(fn (?string $state): string => self::positionColors()[$state ?? ''] ?? 'gray'),

            TextEntry::make('status')
                ->label('Status')
                ->badge()
                ->formatStateUsing(fn (mixed $state): string => self::statusOptions()[(int) $state] ?? '-')
                ->color(fn (mixed $state): string => self::statusColors()[(int) $state] ?? 'gray'),

            TextEntry::make('level')
                ->label('Level')
                ->numeric(decimalPlaces: 0),

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

    private static function positionOptions(): array
    {
        return [
            'left' => 'Kiri',
            'right' => 'Kanan',
        ];
    }

    private static function positionColors(): array
    {
        return [
            'left' => 'info',
            'right' => 'warning',
        ];
    }

    private static function statusOptions(): array
    {
        return [
            1 => 'Aktif',
            0 => 'Tidak Aktif',
        ];
    }

    private static function statusColors(): array
    {
        return [
            1 => 'success',
            0 => 'gray',
        ];
    }
}
