<?php

namespace App\Filament\Resources\WhatsAppBroadcasts\Pages;

use App\Filament\Resources\WhatsAppBroadcasts\WhatsAppBroadcastResource;
use App\Jobs\ProcessWhatsAppBroadcastJob;
use App\Models\WhatsAppBroadcast;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;

class ManageWhatsAppBroadcasts extends ManageRecords
{
    protected static string $resource = WhatsAppBroadcastResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->after(function (WhatsAppBroadcast $record): void {
                    try {
                        $record->update([
                            'status' => 'processing',
                            'total_recipients' => 0,
                            'success_recipients' => 0,
                            'failed_recipients' => 0,
                            'last_error' => null,
                            'sent_at' => null,
                        ]);

                        ProcessWhatsAppBroadcastJob::dispatch((int) $record->id);

                        Notification::make()
                            ->title('Broadcast dijadwalkan')
                            ->body('Proses pengiriman WhatsApp sedang dijalankan melalui queue.')
                            ->success()
                            ->send();
                    } catch (\Throwable $exception) {
                        $record->update([
                            'status' => 'failed',
                            'last_error' => $exception->getMessage(),
                        ]);

                        Notification::make()
                            ->title('Gagal menjadwalkan broadcast')
                            ->body($exception->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}
