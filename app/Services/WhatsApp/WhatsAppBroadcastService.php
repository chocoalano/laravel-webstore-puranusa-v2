<?php

namespace App\Services\WhatsApp;

use App\Models\WhatsAppBroadcastRecipient;
use App\Repositories\WhatsApp\Contracts\WhatsAppBroadcastRepositoryInterface;
use App\Services\QontactService;
use Illuminate\Support\Facades\Log;

class WhatsAppBroadcastService
{
    public function __construct(
        protected WhatsAppBroadcastRepositoryInterface $broadcastRepository,
        protected QontactService $qontactService,
    ) {}

    public function process(int $broadcastId): void
    {
        $broadcast = $this->broadcastRepository->findById($broadcastId);

        if (! $broadcast) {
            return;
        }

        if (trim((string) $broadcast->template_id) === '') {
            $this->broadcastRepository->markFailed($broadcast, 'Template ID Qontak wajib diisi.');

            return;
        }

        $this->broadcastRepository->markProcessing($broadcast);

        if ($this->broadcastRepository->countRecipients($broadcastId) === 0) {
            $recipientPayloads = $this->buildUniqueRecipientPayloads();

            if ($recipientPayloads === []) {
                $this->broadcastRepository->markFailed(
                    $broadcast,
                    'Tidak ada nomor customer valid yang dapat diproses.'
                );

                return;
            }

            $this->broadcastRepository->replaceRecipients($broadcastId, $recipientPayloads);
        }

        $lastError = null;
        $pendingRecipients = $this->broadcastRepository->getPendingRecipients($broadcastId);

        /** @var WhatsAppBroadcastRecipient $recipient */
        foreach ($pendingRecipients as $recipient) {
            $this->broadcastRepository->markRecipientProcessing((int) $recipient->id);

            try {
                $result = $this->qontactService->sendWhatsAppWithResultFromParams(
                    trim((string) $recipient->customer_name) !== ''
                        ? (string) $recipient->customer_name
                        : 'Pelanggan',
                    (string) $recipient->normalized_phone,
                    (string) $broadcast->template_id,
                    $this->buildBodyParams(
                        (string) $recipient->customer_name,
                        (string) $broadcast->message,
                    ),
                );

                if ((bool) ($result['success'] ?? false)) {
                    $this->broadcastRepository->markRecipientSent(
                        (int) $recipient->id,
                        $this->extractSuccessMessage($result)
                    );

                    continue;
                }

                $lastError = $this->extractErrorMessage($result);
                $this->broadcastRepository->markRecipientFailed((int) $recipient->id, $lastError);
            } catch (\Throwable $exception) {
                $lastError = $exception->getMessage();
                $this->broadcastRepository->markRecipientFailed((int) $recipient->id, $lastError);

                Log::error('Failed to send WhatsApp broadcast recipient.', [
                    'broadcast_id' => $broadcastId,
                    'recipient_id' => $recipient->id,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        $stats = $this->broadcastRepository->summarizeRecipientStats($broadcastId);

        $this->broadcastRepository->markCompleted(
            $broadcast,
            $stats['total'],
            $stats['success'],
            $stats['failed'],
            $lastError
        );
    }

    /**
     * @return list<array{
     *   customer_id:int,
     *   customer_name:string,
     *   phone:string,
     *   normalized_phone:string
     * }>
     */
    private function buildUniqueRecipientPayloads(): array
    {
        $customers = $this->broadcastRepository->getCustomersWithPhone();
        $uniquePhones = [];
        $recipients = [];

        foreach ($customers as $customer) {
            $rawPhone = trim((string) ($customer->phone ?? ''));
            if ($rawPhone === '') {
                continue;
            }

            $normalizedPhone = $this->qontactService->normalizePhoneNumber($rawPhone);
            if ($normalizedPhone === '' || isset($uniquePhones[$normalizedPhone])) {
                continue;
            }

            $uniquePhones[$normalizedPhone] = true;
            $customerName = trim((string) ($customer->name ?? ''));

            $recipients[] = [
                'customer_id' => (int) ($customer->id ?? 0),
                'customer_name' => $customerName !== '' ? $customerName : 'Pelanggan',
                'phone' => $rawPhone,
                'normalized_phone' => $normalizedPhone,
            ];
        }

        return $recipients;
    }

    /**
     * @return list<string>
     */
    private function buildBodyParams(string $customerName, string $broadcastMessage): array
    {
        $params = [];
        $normalizedName = trim($customerName);
        $normalizedMessage = trim($broadcastMessage);

        if ($normalizedName !== '') {
            $params[] = $normalizedName;
        }

        if ($normalizedMessage !== '') {
            $params[] = $normalizedMessage;
        }

        return $params;
    }

    /**
     * @param  array{error:?string}  $result
     */
    private function extractErrorMessage(array $result): string
    {
        $error = trim((string) ($result['error'] ?? ''));

        return $error !== '' ? $error : 'Gagal mengirim pesan ke nomor tujuan.';
    }

    /**
     * @param  array{status:int|null}  $result
     */
    private function extractSuccessMessage(array $result): ?string
    {
        $statusCode = $result['status'] ?? null;
        if (! is_int($statusCode)) {
            return null;
        }

        return "Sent via Qontak (HTTP {$statusCode})";
    }
}
