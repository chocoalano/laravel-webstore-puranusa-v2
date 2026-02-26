<?php

namespace App\Support\Articles;

class ArticleContentParser
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function normalizeBlocks(mixed $rawContent): array
    {
        $decoded = $this->decodeContent($rawContent);

        if (! is_array($decoded)) {
            return [];
        }

        if (isset($decoded['blocks']) && is_array($decoded['blocks'])) {
            $decoded = $decoded['blocks'];
        }

        if (! array_is_list($decoded)) {
            if (array_key_exists('type', $decoded)) {
                $decoded = [$decoded];
            } else {
                $decoded = array_values($decoded);
            }
        }

        return collect($decoded)
            ->map(fn (mixed $block): ?array => $this->normalizeBlock($block))
            ->filter(fn (?array $block): bool => $block !== null)
            ->values()
            ->all();
    }

    /** @return array<int, string> */
    public function normalizeTags(mixed $rawTags): array
    {
        if (is_string($rawTags)) {
            $decoded = json_decode($rawTags, true);
        } elseif (is_array($rawTags)) {
            $decoded = $rawTags;
        } else {
            $decoded = [];
        }

        if (! is_array($decoded)) {
            return [];
        }

        return collect($decoded)
            ->filter(fn (mixed $tag): bool => is_string($tag) && trim($tag) !== '')
            ->map(fn (string $tag): string => trim($tag))
            ->unique()
            ->values()
            ->all();
    }

    public function extractPlainTextFromBlocks(array $blocks): string
    {
        $text = collect($blocks)
            ->map(function (array $block): string {
                $type = (string) ($block['type'] ?? '');

                return match ($type) {
                    'heading' => (string) ($block['text'] ?? ''),
                    'rich_text' => strip_tags((string) ($block['html'] ?? '')),
                    'list' => collect((array) ($block['items'] ?? []))
                        ->map(fn (mixed $item): string => is_string($item) ? strip_tags($item) : '')
                        ->implode(' '),
                    'quote' => (string) ($block['quote'] ?? ''),
                    default => '',
                };
            })
            ->implode(' ');

        return trim((string) preg_replace('/\s+/', ' ', $text));
    }

    public function extractFirstImageFromBlocks(array $blocks): ?string
    {
        foreach ($blocks as $block) {
            if (! is_array($block) || ($block['type'] ?? null) !== 'image') {
                continue;
            }

            $url = $block['url'] ?? null;
            if (is_string($url) && $url !== '') {
                return $url;
            }
        }

        return null;
    }

    public function estimateReadTimeMinutes(string $plainText): int
    {
        $words = str_word_count($plainText);

        return max(1, (int) ceil($words / 200));
    }

    /** @return array<string, mixed>|array<int, mixed>|null */
    private function decodeContent(mixed $rawContent): array|null
    {
        if (is_string($rawContent)) {
            $decoded = json_decode($rawContent, true);

            if (is_array($decoded)) {
                return $decoded;
            }

            return [
                [
                    'type' => 'rich_text',
                    'content' => ['text' => $rawContent],
                ],
            ];
        }

        if (is_array($rawContent)) {
            return $rawContent;
        }

        return null;
    }

    /** @return array<string, mixed>|null */
    private function normalizeBlock(mixed $rawBlock): ?array
    {
        if (! is_array($rawBlock)) {
            return null;
        }

        $blockType = (string) ($rawBlock['type'] ?? 'unknown');
        $data = $this->extractBlockData($rawBlock);

        return match ($blockType) {
            'heading' => $this->normalizeHeadingBlock($data),
            'rich_text', 'paragraph' => $this->normalizeRichTextBlock($data),
            'image' => $this->normalizeImageBlock($data),
            'list' => $this->normalizeListBlock($data),
            'quote' => $this->normalizeQuoteBlock($data),
            'divider' => ['type' => 'divider'],
            default => [
                'type' => 'unknown',
                'block_type' => $blockType,
                'data' => $data,
            ],
        };
    }

    /** @param array<string, mixed> $rawBlock
     *  @return array<string, mixed>
     */
    private function extractBlockData(array $rawBlock): array
    {
        if (isset($rawBlock['data']) && is_array($rawBlock['data'])) {
            return $rawBlock['data'];
        }

        if (isset($rawBlock['content'])) {
            if (is_array($rawBlock['content'])) {
                return $rawBlock['content'];
            }

            if (is_string($rawBlock['content'])) {
                return ['text' => $rawBlock['content']];
            }
        }

        return [];
    }

    /** @param array<string, mixed> $data
     *  @return array<string, mixed>
     */
    private function normalizeHeadingBlock(array $data): array
    {
        $rawLevel = (int) ($data['level'] ?? 2);
        $level = max(1, min(6, $rawLevel));

        $text = trim(strip_tags((string) ($data['text'] ?? $data['content'] ?? '')));

        return [
            'type' => 'heading',
            'level' => $level,
            'text' => $text,
        ];
    }

    /** @param array<string, mixed> $data
     *  @return array<string, mixed>
     */
    private function normalizeRichTextBlock(array $data): array
    {
        $html = (string) ($data['text'] ?? $data['content'] ?? '');

        return [
            'type' => 'rich_text',
            'html' => $this->sanitizeHtml($html),
        ];
    }

    /** @param array<string, mixed> $data
     *  @return array<string, mixed>
     */
    private function normalizeImageBlock(array $data): array
    {
        $rawUrl = (string) ($data['url'] ?? '');

        return [
            'type' => 'image',
            'url' => $this->normalizeImageUrl($rawUrl),
            'alt' => trim((string) ($data['alt'] ?? '')),
            'caption' => trim(strip_tags((string) ($data['caption'] ?? ''))),
        ];
    }

    /** @param array<string, mixed> $data
     *  @return array<string, mixed>
     */
    private function normalizeListBlock(array $data): array
    {
        $items = collect((array) ($data['items'] ?? []))
            ->map(fn (mixed $item): string => is_string($item) ? $this->sanitizeHtml($item) : '')
            ->filter(fn (string $item): bool => trim(strip_tags($item)) !== '')
            ->values()
            ->all();

        return [
            'type' => 'list',
            'ordered' => (bool) ($data['ordered'] ?? false),
            'items' => $items,
        ];
    }

    /** @param array<string, mixed> $data
     *  @return array<string, mixed>
     */
    private function normalizeQuoteBlock(array $data): array
    {
        return [
            'type' => 'quote',
            'quote' => trim(strip_tags((string) ($data['quote'] ?? ''))),
            'cite' => trim(strip_tags((string) ($data['cite'] ?? ''))),
        ];
    }

    private function normalizeImageUrl(string $rawUrl): ?string
    {
        if ($rawUrl === '') {
            return null;
        }

        if (
            str_starts_with($rawUrl, 'http://')
            || str_starts_with($rawUrl, 'https://')
            || str_starts_with($rawUrl, '/storage/')
            || str_starts_with($rawUrl, 'data:')
        ) {
            return $rawUrl;
        }

        $trimmedUrl = ltrim($rawUrl, '/');

        if (str_starts_with($trimmedUrl, 'storage/')) {
            return '/' . $trimmedUrl;
        }

        return asset('storage/' . $trimmedUrl);
    }

    private function sanitizeHtml(string $html): string
    {
        $cleaned = preg_replace('/<script\\b[^>]*>(.*?)<\\/script>/is', '', $html);

        return is_string($cleaned) ? trim($cleaned) : trim($html);
    }
}
