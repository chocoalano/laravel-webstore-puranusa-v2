<?php

namespace App\Filament\Resources\Articles\Schemas;

use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ArticleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(12)->schema([
                Section::make('Artikel')
                    ->description('Informasi dasar artikel dan publikasi.')
                    ->columns(12)
                    ->schema([
                        TextInput::make('title')
                            ->label('Judul')
                            ->required()
                            ->maxLength(200)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                if (blank($get('slug')) && filled($state)) {
                                    $set('slug', Str::slug((string) $state));
                                }

                                if (blank($get('seo_title')) && filled($state)) {
                                    $set('seo_title', (string) $state);
                                }
                            })
                            ->columnSpanFull(),

                        TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->maxLength(200)
                            ->helperText('URL-friendly. Contoh: judul-artikel-anda')
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug((string) $state)))
                            ->unique(ignoreRecord: true)
                            ->columnSpan(['default' => 12, 'lg' => 8]),

                        Toggle::make('is_published')
                            ->label('Publikasikan')
                            ->default(false)
                            ->helperText('Aktifkan untuk mempublikasikan artikel.')
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                if ($state && blank($get('published_at'))) {
                                    $set('published_at', now());
                                }

                                if (! $state) {
                                    $set('published_at', null);
                                }
                            })
                            ->columnSpan(['default' => 12, 'lg' => 4]),

                        DateTimePicker::make('published_at')
                            ->label('Tanggal Publikasi')
                            ->seconds(false)
                            ->helperText('Diisi otomatis saat dipublikasikan, bisa diubah untuk penjadwalan.')
                            ->visible(fn (callable $get) => (bool) $get('is_published'))
                            ->required(fn (callable $get) => (bool) $get('is_published'))
                            ->columnSpanFull(),
                    ])
                    ->columnSpan(['default' => 12, 'lg' => 8]),

                Section::make('SEO')
                    ->description('Optimasi tampilan di mesin pencari.')
                    ->schema([
                        TextInput::make('seo_title')
                            ->label('SEO Title')
                            ->maxLength(200)
                            ->helperText('Judul untuk Google (opsional).'),

                        Textarea::make('seo_description')
                            ->label('SEO Description')
                            ->rows(5)
                            ->maxLength(300)
                            ->helperText('Ringkasan 150–300 karakter untuk snippet.')
                            ->columnSpanFull(),
                    ])
                    ->columnSpan(['default' => 12, 'lg' => 4]),

                Section::make('Konten')
                    ->description('Gunakan Builder untuk menyusun blok-blok konten artikel.')
                    ->schema([
                        Repeater::make('contents')
                            ->label('Bagian Konten')
                            ->relationship('contents')
                            ->mutateRelationshipDataBeforeFillUsing(function (array $data): array {
                                $data['content'] = self::normalizeBuilderState($data['content'] ?? null);

                                return $data;
                            })
                            ->mutateRelationshipDataBeforeCreateUsing(function (array $data): ?array {
                                $data['content'] = self::encodeBuilderStateForStorage($data['content'] ?? []);

                                return $data;
                            })
                            ->mutateRelationshipDataBeforeSaveUsing(function (array $data): ?array {
                                $data['content'] = self::encodeBuilderStateForStorage($data['content'] ?? []);

                                return $data;
                            })

                            ->defaultItems(1)
                            ->reorderable()
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => filled($state['tags'] ?? null)
                                ? 'Konten • ' . implode(', ', array_slice((array) $state['tags'], 0, 3))
                                : 'Konten'
                            )
                            ->schema([
                                Builder::make('content')
                                    ->label('Body (Builder)')
                                    ->required()
                                    ->default([])
                                    ->addActionLabel('Tambah blok')
                                    ->blockNumbers(false)
                                    ->blockIcons()
                                    ->collapsible()
                                    ->cloneable()

                                    ->blocks([
                                        Block::make('heading')
                                            ->label('Heading')
                                            ->columns(2)
                                            ->schema([
                                                TextInput::make('content')
                                                    ->label('Teks')
                                                    ->required()
                                                    ->maxLength(200),

                                                Select::make('level')
                                                    ->label('Level')
                                                    ->options([
                                                        'h1' => 'H1',
                                                        'h2' => 'H2',
                                                        'h3' => 'H3',
                                                        'h4' => 'H4',
                                                        'h5' => 'H5',
                                                        'h6' => 'H6',
                                                    ])
                                                    ->default('h2')
                                                    ->required(),
                                            ]),

                                        Block::make('rich_text')
                                            ->label('Rich Text')
                                            ->schema([
                                                RichEditor::make('content')
                                                    ->label('Isi')
                                                    ->required(),
                                            ]),

                                        Block::make('image')
                                            ->label('Gambar')
                                            ->columns(2)
                                            ->schema([
                                                FileUpload::make('url')
                                                    ->label('File Gambar')
                                                    ->image()
                                                    ->imageEditor()
                                                    ->directory('articles')
                                                    ->required(),

                                                TextInput::make('alt')
                                                    ->label('Alt Text')
                                                    ->maxLength(150)
                                                    ->required(),
                                            ]),

                                        Block::make('quote')
                                            ->label('Quote')
                                            ->schema([
                                                Textarea::make('quote')
                                                    ->label('Kutipan')
                                                    ->rows(4)
                                                    ->required(),

                                                TextInput::make('cite')
                                                    ->label('Sumber (opsional)')
                                                    ->maxLength(100),
                                            ]),

                                        Block::make('divider')
                                            ->label('Divider')
                                            ->schema([]),
                                    ])
                                    ->columnSpanFull(),

                                TagsInput::make('tags')
                                    ->label('Tags')
                                    ->helperText('Pisahkan dengan Enter. Contoh: seo, bisnis, teknologi')
                                    ->columnSpanFull(),
                            ])
                            ->columns(12)
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
            ])->columnSpanFull(),
        ]);
    }

    /**
     * Pastikan Builder selalu menerima array:
     * - Jika DB berisi JSON builder string -> decode
     * - Jika DB berisi HTML/string lama -> bungkus jadi 1 block rich_text
     * - Jika kosong -> []
     */
    protected static function normalizeBuilderState(mixed $value): array
    {
        if (blank($value)) {
            return [];
        }

        // Sudah array (mungkin karena cast/atau sudah normal)
        if (is_array($value)) {
            return self::looksLikeBuilderState($value)
                ? $value
                : [self::makeRichTextBlock(json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '')];
        }

        if (! is_string($value)) {
            return [];
        }

        $decoded = json_decode($value, true);

        if (is_array($decoded) && self::looksLikeBuilderState($decoded)) {
            return $decoded;
        }

        // Fallback: anggap HTML/plain
        return [self::makeRichTextBlock($value)];
    }

    protected static function looksLikeBuilderState(array $value): bool
    {
        return array_is_list($value)
            && isset($value[0])
            && is_array($value[0])
            && array_key_exists('type', $value[0])
            && array_key_exists('data', $value[0]);
    }

    protected static function makeRichTextBlock(string $html): array
    {
        return [
            'type' => 'rich_text',
            'data' => [
                'content' => $html,
            ],
        ];
    }

    protected static function encodeBuilderStateForStorage(mixed $value): string
    {
        if (is_string($value)) {
            return $value;
        }

        if (! is_array($value)) {
            return '[]';
        }

        return json_encode(
            array_values($value),
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        ) ?: '[]';
    }
}
