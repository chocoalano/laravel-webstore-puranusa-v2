<?php

namespace App\Filament\Resources\CustomerTopups\Pages;

use App\Filament\Resources\CustomerTopups\CustomerTopupResource;
use App\Filament\Resources\CustomerTopups\Widgets\CustomerTopupOverview;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Components\RenderHook;
use Filament\Schemas\Schema;
use Filament\View\PanelsRenderHook;

class ManageCustomerTopups extends ManageRecords
{
    protected static string $resource = CustomerTopupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            CustomerTopupOverview::class,
        ];
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Callout::make('Top-Up Saldo (Otomatis via Midtrans)')
                ->success()
                ->description(
                    'Top-up saldo diproses otomatis melalui Midtrans (QRIS/Transfer Bank). '
                    .'Saldo bertambah segera setelah pembayaran dikonfirmasi oleh Midtrans — tidak ada notifikasi WA untuk top-up. '
                    .'Pelanggan dapat memantau saldo langsung di Dashboard aplikasi. '
                    .'Catatan: tombol top-up hanya aktif jika nomor WA pelanggan sudah terkonfirmasi.'
                ),

            RenderHook::make(PanelsRenderHook::RESOURCE_PAGES_LIST_RECORDS_TABLE_BEFORE),
            EmbeddedTable::make(),
            RenderHook::make(PanelsRenderHook::RESOURCE_PAGES_LIST_RECORDS_TABLE_AFTER),
        ]);
    }
}
