<?php

namespace App\Filament\Resources\WhatsAppBroadcasts\Pages;

use App\Filament\Resources\WhatsAppBroadcasts\WhatsAppBroadcastResource;
use App\Jobs\ProcessWhatsAppBroadcastJob;
use App\Jobs\SendWhatsAppTestMessageJob;
use App\Models\WhatsAppBroadcast;
use App\Services\QontactService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;

class ManageWhatsAppBroadcasts extends ManageRecords
{
    protected static string $resource = WhatsAppBroadcastResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('test_wa')
                ->label('Test Kirim Pesan Whatsapp')
                ->icon('heroicon-o-paper-airplane')
                ->color('gray')
                ->modalHeading('Test Pengiriman WhatsApp')
                ->modalDescription('Gunakan untuk menguji koneksi gateway Qontak dan validasi template sebelum broadcast massal.')
                ->modalSubmitActionLabel('Kirim Test')
                ->schema([
                    TextInput::make('to_name')
                        ->label('Nama Penerima')
                        ->required()
                        ->maxLength(120)
                        ->default('Tester Admin'),

                    TextInput::make('phone')
                        ->label('Nomor WhatsApp')
                        ->required()
                        ->tel()
                        ->placeholder('081234567890')
                        ->helperText('Bisa format 08xxx atau 62xxx.'),

                    TextInput::make('template_id')
                        ->label('Template ID Qontak')
                        ->required()
                        ->maxLength(100)
                        ->default((string) config('services.qontak.broadcast_template_id', '')),

                    Textarea::make('message')
                        ->label('Pesan Uji')
                        ->required()
                        ->rows(4)
                        ->default('Ini adalah pesan test WhatsApp Broadcast.'),
                ])
                ->action(function (array $data): void {
                    try {
                        $normalizedPhone = self::dispatchTestWhatsAppMessage($data);

                        Notification::make()
                            ->title('Pesan test dijadwalkan')
                            ->body("Pesan test dimasukkan ke queue whatsapp untuk nomor {$normalizedPhone}.")
                            ->success()
                            ->send();
                    } catch (\InvalidArgumentException $exception) {
                        Notification::make()
                            ->title('Nomor WhatsApp tidak valid')
                            ->body($exception->getMessage())
                            ->danger()
                            ->send();
                    } catch (\Throwable $exception) {
                        Notification::make()
                            ->title('Terjadi kesalahan saat menjadwalkan test')
                            ->body($exception->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
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

    /**
     * @param  array<string, mixed>  $data
     */
    private static function dispatchTestWhatsAppMessage(array $data): string
    {
        $qontactService = app(QontactService::class);

        $recipientName = trim((string) ($data['to_name'] ?? ''));
        $rawPhone = trim((string) ($data['phone'] ?? ''));
        $templateId = trim((string) ($data['template_id'] ?? ''));
        $message = trim((string) ($data['message'] ?? ''));
        $normalizedPhone = $qontactService->normalizePhoneNumber($rawPhone);

        if ($normalizedPhone === '') {
            throw new \InvalidArgumentException('Gunakan format nomor Indonesia yang valid, contoh 0812xxxx atau 62812xxxx.');
        }

        SendWhatsAppTestMessageJob::dispatch(
            $recipientName,
            $normalizedPhone,
            $templateId,
            $message,
        );

        return $normalizedPhone;
    }
}
