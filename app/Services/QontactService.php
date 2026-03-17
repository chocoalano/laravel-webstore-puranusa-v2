<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class QontactService
{
    protected string $baseUrl = 'https://service-chat.qontak.com/api/open/v1';

    protected string $token;

    protected string $channelIntegrationId;

    protected Client $client;

    /** @var array<string, array{label: string, variable_count: int, variables: list<int>, params: list<array{key: string, value: string}>}>|null */
    private ?array $templatesCache = null;

    public function __construct()
    {
        $this->token = (string) config('services.qontak.api_token');
        $this->channelIntegrationId = (string) config('services.qontak.channel_integration_id');
        $this->client = new Client([
            'base_uri' => $this->baseUrl.'/',
            'timeout' => 30,
            'headers' => [
                'Authorization' => "Bearer {$this->token}",
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ]);
    }

    public function sendWhatsApp(
        string $toName,
        string $toNumber,
        string $templateId,
        array $bodyParams = [],
        string $languageCode = 'id',
        ?string $headerImageUrl = null,
    ): bool {
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
     * @return array{success: bool, status: int|null, error: string|null, body: array<mixed>|null}
     */
    public function sendWhatsAppWithResultFromParams(
        string $toName,
        string $toNumber,
        string $templateId,
        array $bodyParams = [],
        string $languageCode = 'id',
        ?string $headerImageUrl = null,
    ): array {
        $bodyLabels = ['full_name', 'nominal', 'param_3', 'param_4', 'param_5'];

        $parameters = [];
        $bodyItems = [];

        foreach ($bodyParams as $index => $value) {
            $bodyItems[] = [
                'key' => (string) ($index + 1),
                'value' => $bodyLabels[$index] ?? 'param_'.($index + 1),
                'value_text' => (string) $value,
            ];
        }

        if ($bodyItems !== []) {
            $parameters['body'] = $bodyItems;
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
            'to_number' => (int) $toNumber,
            'to_name' => $toName,
            'message_template_id' => $templateId,
            'channel_integration_id' => $this->channelIntegrationId,
            'language' => ['code' => $languageCode],
        ];

        if ($parameters !== []) {
            $payload['parameters'] = $parameters;
        }

        return $this->sendWhatsAppWithResult($payload, $toNumber, $templateId);
    }

    /**
     * Send WhatsApp with pre-formatted body params (key, value, value_text).
     *
     * @param  list<array{key: string, value: string, value_text: string}>  $bodyParams
     * @return array{success: bool, status: int|null, error: string|null, body: array<mixed>|null}
     */
    public function sendWhatsAppWithFormattedParams(
        string $toName,
        string $toNumber,
        string $templateId,
        array $bodyParams = [],
        string $languageCode = 'id',
    ): array {
        $payload = [
            'to_name' => $toName,
            'to_number' => $toNumber,
            'message_template_id' => $templateId,
            'channel_integration_id' => $this->channelIntegrationId,
            'language' => ['code' => $languageCode],
        ];

        if ($bodyParams !== []) {
            $payload['parameters'] = ['body' => $bodyParams];
        }

        return $this->sendWhatsAppWithResult($payload, $toNumber, $templateId);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array{success: bool, status: int|null, error: string|null, body: array<mixed>|null}
     */
    protected function sendWhatsAppWithResult(array $payload, string $toNumber, string $templateId): array
    {
        try {
            $response = $this->client->request('POST', 'broadcasts/whatsapp/direct', [
                'json' => $payload,
            ]);

            $statusCode = $response->getStatusCode();
            $body = json_decode((string) $response->getBody(), true);

            Log::info('Qontak WhatsApp sent', [
                'to' => $toNumber,
                'template' => $templateId,
                'status' => $statusCode,
                'body' => $body,
            ]);

            return [
                'success' => true,
                'status' => $statusCode,
                'error' => null,
                'body' => is_array($body) ? $body : null,
            ];
        } catch (RequestException $e) {
            $statusCode = $e->getResponse()?->getStatusCode();
            $body = null;

            if ($e->hasResponse()) {
                $body = json_decode((string) $e->getResponse()->getBody(), true);
            }

            $errorMessage = $this->extractQontakErrorMessage($body)
                ?? $e->getMessage();

            Log::warning('Qontak WhatsApp failed', [
                'to' => $toNumber,
                'status' => $statusCode,
                'error' => $errorMessage,
                'body' => $body,
            ]);

            return [
                'success' => false,
                'status' => $statusCode,
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

    public function sendWithdrawalApproved(
        string $customerName,
        string $phoneNumber,
        string $amount,
        string $adminFee = '',
        string $netAmount = '',
    ): bool {
        $templateId = config('services.qontak.wd_approved_template_id');

        if (! $templateId || ! $this->token || ! $this->channelIntegrationId) {
            Log::warning('Qontak config incomplete, skipping WhatsApp notification');

            return false;
        }

        $originalPhone = $phoneNumber;
        $phoneNumber = $this->normalizePhoneNumber($phoneNumber);

        if ($phoneNumber === '') {
            Log::warning('Qontak phone number invalid, skipping WhatsApp notification', [
                'customer' => $customerName,
                'phone' => $originalPhone,
            ]);

            return false;
        }

        $nominalText = ($adminFee !== '' && $netAmount !== '')
            ? "{$amount} - {$adminFee} = {$netAmount}"
            : $amount;

        $headerImageUrl = (string) config('services.qontak.wd_approved_header_image_url', 'https://puranusa.id/logo.png');

        return $this->sendWhatsApp(
            $customerName,
            $phoneNumber,
            $templateId,
            [$customerName, $nominalText],
            'id',
            $headerImageUrl
        );
    }

    public function sendWithdrawalRejected(
        string $customerName,
        string $phoneNumber,
        string $amount,
    ): bool {
        $templateId = config('services.qontak.wd_rejected_template_id');

        if (! $templateId || ! $this->token || ! $this->channelIntegrationId) {
            Log::warning('Qontak config incomplete, skipping WhatsApp rejection notification');

            return false;
        }

        $originalPhone = $phoneNumber;
        $phoneNumber = $this->normalizePhoneNumber($phoneNumber);

        if ($phoneNumber === '') {
            Log::warning('Qontak phone number invalid, skipping WhatsApp rejection notification', [
                'customer' => $customerName,
                'phone' => $originalPhone,
            ]);

            return false;
        }

        return $this->sendWhatsApp(
            $customerName,
            $phoneNumber,
            $templateId,
            [$customerName, $amount],
            'id',
        );
    }

    /**
     * @return array<string, array{label: string, variable_count: int, variables: list<int>}>
     */
    private function fetchWhatsAppTemplatesData(): array
    {
        if ($this->templatesCache !== null) {
            return $this->templatesCache;
        }

        try {
            $response = $this->client->request('GET', 'templates/whatsapp');
            $items = json_decode((string) $response->getBody(), true)['data'] ?? [];

            if (! is_array($items)) {
                return $this->templatesCache = [];
            }

            $result = [];

            foreach ($items as $item) {
                $id = (string) ($item['id'] ?? '');

                if ($id === '') {
                    continue;
                }

                $name = (string) ($item['name'] ?? $id);
                $category = (string) ($item['category'] ?? '');
                $body = (string) ($item['body'] ?? '');

                preg_match_all('/\{\{(\d+)\}\}/', $body, $matches);
                $varNums = array_unique(array_map('intval', $matches[1] ?? []));
                sort($varNums);

                // Extract named params from API response
                $params = [];
                if (is_array($item['params'] ?? null)) {
                    foreach ($item['params'] as $param) {
                        if (is_array($param) && isset($param['key'], $param['value'])) {
                            $params[] = [
                                'key' => (string) $param['key'],
                                'value' => (string) $param['value'],
                            ];
                        }
                    }
                }

                // Fallback: generate from variable numbers if API didn't return params
                if ($params === [] && $varNums !== []) {
                    $fallbackLabels = ['full_name', 'nominal', 'param_3', 'param_4', 'param_5'];
                    foreach ($varNums as $i => $num) {
                        $params[] = [
                            'key' => (string) $num,
                            'value' => $fallbackLabels[$i] ?? 'param_'.$num,
                        ];
                    }
                }

                $result[$id] = [
                    'label' => $category !== '' ? "{$name} ({$category})" : $name,
                    'variable_count' => count($varNums),
                    'variables' => array_values($varNums),
                    'params' => $params,
                ];
            }

            return $this->templatesCache = $result;
        } catch (\Throwable) {
            return $this->templatesCache = [];
        }
    }

    /**
     * @return array<string, string> id => "name (category) [N var]"
     */
    public function getWhatsAppTemplates(): array
    {
        $data = $this->fetchWhatsAppTemplatesData();
        $options = [];

        foreach ($data as $id => $template) {
            $varCount = $template['variable_count'];
            $varSuffix = $varCount > 0 ? " [{$varCount} var]" : ' [0 var]';
            $options[$id] = $template['label'].$varSuffix;
        }

        return $options;
    }

    /**
     * @return list<int>
     */
    public function getWhatsAppTemplateVariables(string $templateId): array
    {
        return $this->fetchWhatsAppTemplatesData()[$templateId]['variables'] ?? [];
    }

    /**
     * @return list<array{key: string, value: string}>
     */
    public function getWhatsAppTemplateParams(string $templateId): array
    {
        return $this->fetchWhatsAppTemplatesData()[$templateId]['params'] ?? [];
    }

    /**
     * @return array{data: list<array<string, mixed>>, error: string|null}
     */
    public function getWhatsAppBroadcastLog(string $broadcastId): array
    {
        try {
            $response = $this->client->request('GET', "broadcasts/{$broadcastId}/whatsapp/log");
            $data = json_decode((string) $response->getBody(), true)['data'] ?? [];

            return [
                'data' => is_array($data) ? $data : [],
                'error' => null,
            ];
        } catch (RequestException $e) {
            $body = null;

            if ($e->hasResponse()) {
                $body = json_decode((string) $e->getResponse()->getBody(), true);
            }

            $error = $this->extractQontakErrorMessage($body)
                ?? 'Request gagal dengan status '.($e->getResponse()?->getStatusCode() ?? 'unknown');

            return ['data' => [], 'error' => $error];
        } catch (\Throwable $e) {
            return ['data' => [], 'error' => $e->getMessage()];
        }
    }

    public function normalizePhoneNumber(string $phone): string
    {
        $normalizedPhone = preg_replace('/[^0-9]/', '', $phone) ?? '';

        if ($normalizedPhone === '') {
            return '';
        }

        if (str_starts_with($normalizedPhone, '0')) {
            $normalizedPhone = '62'.substr($normalizedPhone, 1);
        }

        if (! str_starts_with($normalizedPhone, '62')) {
            $normalizedPhone = '62'.$normalizedPhone;
        }

        return strlen($normalizedPhone) >= 10 ? $normalizedPhone : '';
    }

    protected function formatPhoneNumber(string $phone): string
    {
        return $this->normalizePhoneNumber($phone);
    }
}
