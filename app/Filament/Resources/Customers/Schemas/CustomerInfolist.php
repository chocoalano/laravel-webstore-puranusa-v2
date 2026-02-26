<?php

namespace App\Filament\Resources\Customers\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CustomerInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            // =========================================================
            // JARINGAN (Sponsor / Upline)
            // =========================================================
            Section::make('Jaringan')
                ->description('Informasi relasi sponsor dan upline dalam jaringan.')
                ->collapsed()
                ->columns(3)
                ->schema([
                    TextEntry::make('sponsor.name')
                        ->label('Sponsor')
                        ->placeholder('-'),

                    TextEntry::make('upline.name')
                        ->label('Upline')
                        ->placeholder('-'),

                    TextEntry::make('position')
                        ->label('Posisi Binary')
                        ->badge()
                        ->placeholder('-'),

                    TextEntry::make('ref_code')
                        ->label('Kode Referal')
                        ->placeholder('-'),

                    TextEntry::make('level')
                        ->label('Level')
                        ->badge()
                        ->placeholder('-'),
                ]),

            // =========================================================
            // IDENTITAS CUSTOMER
            // =========================================================
            Section::make('Identitas')
                ->description('Data identitas dasar customer.')
                ->collapsed()
                ->columns(3)
                ->schema([
                    TextEntry::make('username')
                        ->label('Username')
                        ->placeholder('-'),

                    TextEntry::make('nik')
                        ->label('NIK')
                        ->placeholder('-'),

                    TextEntry::make('name')
                        ->label('Nama Lengkap')
                        ->placeholder('-'),

                    TextEntry::make('gender')
                        ->label('Jenis Kelamin')
                        ->badge()
                        ->placeholder('-'),

                    TextEntry::make('email')
                        ->label('Email')
                        ->limit(20)
                        ->placeholder('-'),

                    TextEntry::make('phone')
                        ->label('Nomor Telepon')
                        ->placeholder('-'),
                ]),

            // =========================================================
            // ALAMAT
            // =========================================================
            Section::make('Alamat')
                ->description('Alamat dan wilayah domisili customer.')
                ->collapsed()
                ->columns(2)
                ->schema([
                    TextEntry::make('alamat')
                        ->label('Alamat')
                        ->placeholder('-')
                        ->limit(50)
                        ->columnSpanFull(),

                    TextEntry::make('address')
                        ->label('Alamat Tambahan')
                        ->placeholder('-')
                        ->limit(50)
                        ->columnSpanFull(),
                ]),

            // =========================================================
            // PAKET & STATUS KEANGGOTAAN
            // =========================================================
            Section::make('Keanggotaan')
                ->description('Informasi paket dan status customer.')
                ->collapsed()
                ->columns(2)
                ->schema([
                    TextEntry::make('package.name')
                        ->label('Paket')
                        ->placeholder('-'),

                    TextEntry::make('status')
                        ->label('Status Customer')
                        ->formatStateUsing(fn ($state) => match ((int) $state) {
                            1 => 'Prospek',
                            2 => 'Pasif',
                            3 => 'Aktif',
                            default => (string) $state,
                        })
                        ->badge()
                        ->placeholder('-'),

                    IconEntry::make('network_generated')
                        ->label('Jaringan Dibangkitkan Sistem')
                        ->boolean(),
                ]),

            // =========================================================
            // E-WALLET & BONUS
            // =========================================================
            Section::make('Dompet & Bonus')
                ->description('Saldo e-wallet dan akumulasi bonus.')
                ->collapsed()
                ->columns(2)
                ->schema([
                    TextEntry::make('ewallet_id')
                        ->label('ID E-Wallet')
                        ->placeholder('-'),

                    TextEntry::make('ewallet_saldo')
                        ->label('Saldo E-Wallet')
                        ->numeric()
                        ->placeholder('-'),

                    TextEntry::make('bonus_pending')
                        ->label('Bonus Tertunda')
                        ->numeric()
                        ->placeholder('-'),

                    TextEntry::make('bonus_processed')
                        ->label('Bonus Diproses')
                        ->numeric()
                        ->placeholder('-'),
                ]),

            // =========================================================
            // REKENING BANK
            // =========================================================
            Section::make('Rekening Bank')
                ->description('Informasi rekening untuk penarikan/komisi.')
                ->collapsed()
                ->schema([
                    TextEntry::make('bank_name')
                        ->label('Nama Bank')
                        ->placeholder('-'),

                    TextEntry::make('bank_account')
                        ->label('Nomor Rekening')
                        ->placeholder('-'),
                ]),

            // =========================================================
            // STOCKIST
            // =========================================================
            Section::make('Stockist')
                ->description('Status stockist dan wilayah stockist (jika berlaku).')
                ->collapsed()
                ->columns(3)
                ->schema([
                    IconEntry::make('is_stockist')
                        ->label('Stockist')
                        ->boolean(),

                    TextEntry::make('stockist_kabupaten_id')
                        ->label('ID Kabupaten Stockist')
                        ->placeholder('-'),

                    TextEntry::make('stockist_kabupaten_name')
                        ->label('Kabupaten Stockist')
                        ->placeholder('-'),

                    TextEntry::make('stockist_province_id')
                        ->label('ID Provinsi Stockist')
                        ->numeric()
                        ->placeholder('-'),

                    TextEntry::make('stockist_province_name')
                        ->label('Provinsi Stockist')
                        ->placeholder('-'),
                ]),

            // =========================================================
            // METRIK JARINGAN (KAKI / PV / OMZET)
            // =========================================================
            Section::make('Metrik Jaringan')
                ->description('Statistik kaki kiri/kanan, PV, dan omzet.')
                ->collapsed()
                ->columns(4)
                ->schema([
                    TextEntry::make('foot_left')
                        ->label('Foot Kiri')
                        ->numeric()
                        ->placeholder('-'),

                    TextEntry::make('foot_right')
                        ->label('Foot Kanan')
                        ->numeric()
                        ->placeholder('-'),

                    TextEntry::make('total_left')
                        ->label('Total Kiri')
                        ->numeric()
                        ->placeholder('-'),

                    TextEntry::make('total_right')
                        ->label('Total Kanan')
                        ->numeric()
                        ->placeholder('-'),

                    TextEntry::make('sponsor_left')
                        ->label('Sponsor Kiri')
                        ->numeric()
                        ->placeholder('-'),

                    TextEntry::make('sponsor_right')
                        ->label('Sponsor Kanan')
                        ->numeric()
                        ->placeholder('-'),

                    TextEntry::make('pv_left')
                        ->label('PV Kiri')
                        ->numeric()
                        ->placeholder('-'),

                    TextEntry::make('pv_right')
                        ->label('PV Kanan')
                        ->numeric()
                        ->placeholder('-'),

                    TextEntry::make('omzet')
                        ->label('Omzet Pribadi')
                        ->numeric()
                        ->placeholder('-'),

                    TextEntry::make('omzet_group')
                        ->label('Omzet Grup')
                        ->numeric()
                        ->placeholder('-'),

                    TextEntry::make('omzet_planb')
                        ->label('Omzet Plan B')
                        ->numeric()
                        ->placeholder('-'),

                    TextEntry::make('omzet_group_left')
                        ->label('Omzet Grup Kiri')
                        ->numeric()
                        ->placeholder('-'),

                    TextEntry::make('omzet_group_right')
                        ->label('Omzet Grup Kanan')
                        ->numeric()
                        ->placeholder('-'),

                    TextEntry::make('omzet_pairing_left')
                        ->label('Omzet Pairing Kiri')
                        ->numeric()
                        ->placeholder('-'),

                    TextEntry::make('omzet_pairing_right')
                        ->label('Omzet Pairing Kanan')
                        ->numeric()
                        ->placeholder('-'),

                    TextEntry::make('omzet_group_left_plana')
                        ->label('Grup Kiri Plan A')
                        ->numeric()
                        ->placeholder('-'),

                    TextEntry::make('omzet_group_right_plana')
                        ->label('Grup Kanan Plan A')
                        ->numeric()
                        ->placeholder('-'),

                    TextEntry::make('omzet_group_left_planb')
                        ->label('Grup Kiri Plan B')
                        ->numeric()
                        ->placeholder('-'),

                    TextEntry::make('omzet_group_right_planb')
                        ->label('Grup Kanan Plan B')
                        ->numeric()
                        ->placeholder('-'),
                ]),

            // =========================================================
            // PAIRING
            // =========================================================
            Section::make('Pairing')
                ->description('Informasi pairing harian dan terakhir pairing.')
                ->columns(3)
                ->collapsed()
                ->schema([
                    TextEntry::make('daily_pairing')
                        ->label('Pairing Harian')
                        ->numeric()
                        ->placeholder('-'),

                    TextEntry::make('max_daily_pairing')
                        ->label('Batas Pairing Harian')
                        ->numeric()
                        ->placeholder('-'),

                    TextEntry::make('last_pairing_date')
                        ->label('Tanggal Pairing Terakhir')
                        ->date()
                        ->placeholder('-'),
                ]),

            // =========================================================
            // META
            // =========================================================
            Section::make('Informasi Sistem')
                ->description('Metadata pembuatan dan pembaruan data.')
                ->columns(2)
                ->collapsed()
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
        ]);
    }
}
