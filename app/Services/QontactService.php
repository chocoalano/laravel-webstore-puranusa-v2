<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class QontactService
{
    protected string $baseUrl = 'https://service-chat.qontak.com/api/open/v1';
    protected string $token;
    protected string $channelIntegrationId;

    public function __construct()
    {
        $this->token = config('services.qontak.api_token');
        $this->channelIntegrationId = config('services.qontak.channel_integration_id');
    }

    public function sendWhatsApp(string $toName, string $toNumber, string $templateId, array $bodyParams = [], string $languageCode = 'id', ?string $headerImageUrl = null): bool
    {
        $result = $this->sendWhatsAppWithResultFromParams(
            $toName,
            $toNumber,
            $templateId,
            $bodyParams,
            $languageCode,
            $headerImageUrl
        );

        return (bool) ($result['success'] ?? false);
    }

    /**
     * @return array{
     *   success: bool,
     *   status: int|null,
     *   error: string|null,
     *   body: array<mixed>|null
     * }
     */
    public function sendWhatsAppWithResultFromParams(string $toName, string $toNumber, string $templateId, array $bodyParams = [], string $languageCode = 'id', ?string $headerImageUrl = null): array
    {
        $bodyLabels = ['customer_name', 'total_transfer', 'param_3', 'param_4', 'param_5'];

        $parameters = ['body' => []];

        foreach ($bodyParams as $index => $value) {
            $parameters['body'][] = [
                'key' => (string) ($index + 1),
                'value_text' => (string) $value,
                'value' => $bodyLabels[$index] ?? 'param_' . ($index + 1),
            ];
        }

        if ($headerImageUrl) {
            $parameters['header'] = [
                'format' => 'IMAGE',
                'params' => [
                    ['key' => 'url', 'value' => $headerImageUrl],
                    ['key' => 'filename', 'value' => 'logo.png'],
                ],
            ];
        }

        $payload = [
            'to_name' => $toName,
            'to_number' => $toNumber,
            'message_template_id' => $templateId,
            'channel_integration_id' => $this->channelIntegrationId,
            'language' => [
                'code' => $languageCode,
            ],
            'parameters' => $parameters,
        ];

        return $this->sendWhatsAppWithResult($payload, $toNumber, $templateId);
    }

    /**
     * @param array<string, mixed> $payload
     * @return array{
     *   success: bool,
     *   status: int|null,
     *   error: string|null,
     *   body: array<mixed>|null
     * }
     */
    protected function sendWhatsAppWithResult(array $payload, string $toNumber, string $templateId): array
    {
        try {
            $response = Http::withToken($this->token)
                ->post("{$this->baseUrl}/broadcasts/whatsapp/direct", $payload);

            $body = $response->json();

            if ($response->successful()) {
                Log::info('Qontak WhatsApp sent', [
                    'to' => $toNumber,
                    'template' => $templateId,
                    'status' => $response->status(),
                    'body' => $body,
                ]);

                return [
                    'success' => true,
                    'status' => $response->status(),
                    'error' => null,
                    'body' => is_array($body) ? $body : null,
                ];
            }

            $errorMessage = $this->extractQontakErrorMessage($body)
                ?? "Qontak request failed with status {$response->status()}";

            Log::warning('Qontak WhatsApp failed', [
                'to' => $toNumber,
                'status' => $response->status(),
                'error' => $errorMessage,
                'body' => $body,
            ]);

            return [
                'success' => false,
                'status' => $response->status(),
                'error' => $errorMessage,
                'body' => is_array($body) ? $body : null,
            ];
        } catch (\Throwable $e) {
            Log::error('Qontak WhatsApp error', [
                'to' => $toNumber,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'status' => null,
                'error' => $e->getMessage(),
                'body' => null,
            ];
        }
    }

    protected function extractQontakErrorMessage(mixed $body): ?string
    {
        if (! is_array($body)) {
            return null;
        }

        if (isset($body['message']) && is_string($body['message'])) {
            return $body['message'];
        }

        if (isset($body['error']) && is_string($body['error'])) {
            return $body['error'];
        }

        if (isset($body['error']) && is_array($body['error'])) {
            if (isset($body['error']['message']) && is_string($body['error']['message'])) {
                return $body['error']['message'];
            }

            if (isset($body['error']['messages']) && is_array($body['error']['messages'])) {
                $firstError = $body['error']['messages'][0] ?? null;
                if (is_string($firstError)) {
                    return $firstError;
                }
            }
        }

        if (isset($body['errors']) && is_array($body['errors'])) {
            $first = $body['errors'][0] ?? null;
            if (is_string($first)) {
                return $first;
            }
            if (is_array($first) && isset($first['message']) && is_string($first['message'])) {
                return $first['message'];
            }
        }

        return null;
    }

    public function sendWithdrawalApproved(string $customerName, string $phoneNumber, string $amount): bool
    {
        $templateId = config('services.qontak.wd_approved_template_id');

        if (! $templateId || ! $this->token || ! $this->channelIntegrationId) {
            Log::warning('Qontak config incomplete, skipping WhatsApp notification');
            return false;
        }

        // Format nomor: pastikan pakai 62
        $originalPhone = $phoneNumber;
        $phoneNumber = $this->normalizePhoneNumber($phoneNumber);
        if ($phoneNumber === '') {
            Log::warning('Qontak phone number invalid, skipping WhatsApp notification', [
                'customer' => $customerName,
                'phone' => $originalPhone,
            ]);

            return false;
        }

        $headerImageUrl = config('services.qontak.wd_approved_header_image_url', 'https://puranusa.id/logo.png');

        return $this->sendWhatsApp(
            $customerName,
            $phoneNumber,
            $templateId,
            [$customerName, $amount],
            'id',
            $headerImageUrl
        );
    }

    public function normalizePhoneNumber(string $phone): string
    {
        $normalizedPhone = preg_replace('/[^0-9]/', '', $phone) ?? '';
        if ($normalizedPhone === '') {
            return '';
        }

        if (str_starts_with($normalizedPhone, '0')) {
            $normalizedPhone = '62' . substr($normalizedPhone, 1);
        }

        if (! str_starts_with($normalizedPhone, '62')) {
            $normalizedPhone = '62' . $normalizedPhone;
        }

        return strlen($normalizedPhone) >= 10 ? $normalizedPhone : '';
    }

    protected function formatPhoneNumber(string $phone): string
    {
        return $this->normalizePhoneNumber($phone);
    }
}
