<?php

namespace App\Filament\Resources\WhatsAppBroadcasts\Pages;

use App\Filament\Resources\WhatsAppBroadcasts\WhatsAppBroadcastResource;
use App\Filament\Widgets\WhatsAppBroadcasts\CustomerWhatsAppConfirmationWidget;
use App\Filament\Widgets\WhatsAppBroadcasts\WhatsAppOutboundLogWidget;
use App\Jobs\ProcessWhatsAppBroadcastJob;
use App\Models\WhatsAppBroadcast;
use App\Services\QontactService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Components\Livewire;
use Filament\Schemas\Components\RenderHook;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\View\PanelsRenderHook;

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

                    Select::make('template_id')
                        ->label('Template WhatsApp Qontak')
                        ->required()
                        ->searchable()
                        ->live()
                        ->options(fn (): array => app(QontactService::class)->getWhatsAppTemplates())
                        ->placeholder('Pilih template...')
                        ->default((string) config('services.qontak.broadcast_template_id', ''))
                        ->helperText('Pilih template yang sudah disetujui (Approved) di Qontak. [N var] menunjukkan jumlah variabel.')
                        ->afterStateUpdated(function (?string $state, Set $set): void {
                            if (! $state) {
                                $set('body_params', []);

                                return;
                            }

                            $params = app(QontactService::class)->getWhatsAppTemplateParams($state);
                            if ($params === []) {
                                $set('body_params', []);

                                return;
                            }

                            $set('body_params', array_map(
                                fn (array $param): array => ['value' => $param['value'], 'value_text' => ''],
                                $params,
                            ));
                        }),

                    Repeater::make('body_params')
                        ->label('Parameter Body (Variabel Template)')
                        ->helperText('Kosongkan jika template tidak memiliki variabel. Nama variabel harus sesuai yang terdaftar di Qontak, contoh: full_name.')
                        ->schema([
                            TextInput::make('value')
                                ->label('Nama Variabel')
                                ->placeholder('Contoh: full_name')
                                ->required(),
                            TextInput::make('value_text')
                                ->label('Nilai')
                                ->placeholder('Contoh: Burhanudin Hakim')
                                ->required(),
                        ])
                        ->columns(2)
                        ->defaultItems(0)
                        ->addActionLabel('Tambah Variabel')
                        ->reorderable(false),

                ])
                ->action(function (array $data): void {
                    try {
                        $normalizedPhone = self::sendTestWhatsAppMessageNow($data);

                        Notification::make()
                            ->title('Pesan test berhasil terkirim')
                            ->body("Pesan test WhatsApp berhasil dikirim ke nomor {$normalizedPhone}.")
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
                            ->title('Pengiriman test gagal')
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

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Callout::make('Syarat Pengiriman Pesan WhatsApp')
                ->warning()
                ->description(
                    'Pesan broadcast/informasi lainya hanya akan terkirim ke penerima yang sebelumnya sudah pernah mengirim pesan WhatsApp ke nomor gateway yang terdaftar di Qontak. '
                    .'Ini adalah ketentuan dari WhatsApp Business API — nomor yang belum pernah memulai percakapan tidak dapat menerima pesan outgoing dari sistem. '
                    .'Pastikan penerima sudah terdaftar di tab "Nomor WA Terkonfirmasi" sebelum menjalankan broadcast.'
                ),

            Tabs::make()
                ->contained(false)
                ->tabs([
                    Tab::make('WA Broadcasts')
                        ->icon('bi-whatsapp')
                        ->schema([
                            $this->getTabsContentComponent(),
                            RenderHook::make(PanelsRenderHook::RESOURCE_PAGES_LIST_RECORDS_TABLE_BEFORE),
                            EmbeddedTable::make(),
                            RenderHook::make(PanelsRenderHook::RESOURCE_PAGES_LIST_RECORDS_TABLE_AFTER),
                        ]),

                    Tab::make('Nomor WA Terkonfirmasi')
                        ->icon('heroicon-o-check-badge')
                        ->schema([
                            Livewire::make(CustomerWhatsAppConfirmationWidget::class),
                        ]),

                    Tab::make('Log Outbound')
                        ->icon('heroicon-o-chat-bubble-left-ellipsis')
                        ->schema([
                            Livewire::make(WhatsAppOutboundLogWidget::class),
                        ]),
                ]),
        ]);
    }

    protected function getFooterWidgets(): array
    {
        return [];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private static function sendTestWhatsAppMessageNow(array $data): string
    {
        $qontactService = app(QontactService::class);

        $recipientName = trim((string) ($data['to_name'] ?? ''));
        $rawPhone = trim((string) ($data['phone'] ?? ''));
        $templateId = trim((string) ($data['template_id'] ?? ''));
        $normalizedPhone = $qontactService->normalizePhoneNumber($rawPhone);

        if ($normalizedPhone === '') {
            throw new \InvalidArgumentException('Gunakan format nomor Indonesia yang valid, contoh 0812xxxx atau 62812xxxx.');
        }

        $resolvedName = $recipientName !== '' ? $recipientName : 'Tester Admin';

        $rawBodyParams = [];
        foreach (array_values((array) ($data['body_params'] ?? [])) as $index => $item) {
            $varName = trim((string) ($item['value'] ?? ''));
            $varText = trim((string) ($item['value_text'] ?? ''));

            if ($varName === '' || $varText === '') {
                continue;
            }

            $rawBodyParams[] = [
                'key' => (string) ($index + 1),
                'value' => $varName,
                'value_text' => $varText,
            ];
        }

        $result = $qontactService->sendWhatsAppWithFormattedParams(
            $resolvedName,
            $normalizedPhone,
            $templateId,
            $rawBodyParams,
            'id',
        );

        if (! (bool) ($result['success'] ?? false)) {
            $error = trim((string) ($result['error'] ?? ''));
            throw new \RuntimeException($error !== '' ? $error : 'Pengiriman pesan test WhatsApp gagal.');
        }

        return $normalizedPhone;
    }
}
