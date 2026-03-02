<?php

namespace App\Filament\Resources\Contents;

use App\Filament\Resources\Contents\Pages\ManageContents;
use App\Models\Content;
use BackedEnum;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use UnitEnum;

class ContentResource extends Resource
{
    protected static ?string $model = Content::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Content';
    protected static ?string $navigationLabel = 'Zenner Konten';
    protected static ?string $modelLabel = 'Zenner Konten';
    protected static ?string $pluralModelLabel = 'Zenner Konten';
    protected static string | UnitEnum | null $navigationGroup = 'Zenner Club';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
                // LEFT: Content editor
                Section::make('Konten')
                    ->description('Informasi dasar dan isi konten.')
                    ->icon(Heroicon::DocumentText)
                    ->columnSpan(['default' => 12, 'lg' => 8])
                    ->schema([
                        Tabs::make('content-tabs')
                            ->contained(false) // supaya tidak “card di dalam card”
                            ->persistTab()
                            ->id('zenner-content-tabs')
                            ->tabs([
                                Tab::make('Konten Utama')
                                    ->icon(Heroicon::PencilSquare)
                                    ->columns(2)
                                    ->schema([
                                        TextInput::make('title')
                                            ->label('Judul')
                                            ->required()
                                            ->maxLength(255)
                                            ->live(onBlur: true)
                                            // jangan overwrite slug kalau user sudah edit manual
                                            ->afterStateUpdated(function (Get $get, Set $set, ?string $old, ?string $state) {
                                                if (($get('slug') ?? '') !== Str::slug($old ?? '')) {
                                                    return;
                                                }

                                                $set('slug', Str::slug($state ?? ''));
                                            }),

                                        TextInput::make('slug')
                                            ->label('Slug')
                                            ->required()
                                            ->unique(ignoreRecord: true)
                                            ->maxLength(255)
                                            ->helperText('Otomatis dari Judul. Kamu boleh ubah untuk SEO / struktur URL.'),

                                        Select::make('category_id')
                                            ->label('Kategori')
                                            ->relationship('category', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->required(),

                                        Select::make('status')
                                            ->label('Status')
                                            ->options([
                                                'draft' => 'Draft',
                                                'published' => 'Published',
                                                'archived' => 'Archived',
                                            ])
                                            ->required()
                                            ->native(false)
                                            ->helperText('Draft = belum tampil • Published = tampil • Archived = arsip.'),

                                        RichEditor::make('content')
                                            ->label('Isi Konten')
                                            ->fileAttachmentsAcceptedFileTypes(['image/png', 'image/jpeg'])
                                            ->fileAttachmentsDirectory('zenner-contents')
                                            ->fileAttachmentsVisibility('private')
                                            ->resizableImages()
                                            ->extraInputAttributes(['style' => 'min-height: 500px;'])
                                            ->required()
                                            ->columnSpanFull(),
                                    ]),

                                Tab::make('Lampiran & Media')
                                    ->icon(Heroicon::PaperClip)
                                    ->columns(2)
                                    ->schema([
                                        FileUpload::make('file')
                                            ->label('Lampiran File')
                                            ->directory('zenner-club-attachments')
                                            ->acceptedFileTypes([
                                                'image/*',
                                                'video/*',
                                                'application/pdf',
                                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                            ])
                                            ->maxSize(51200)
                                            ->downloadable()
                                            ->openable()
                                            ->helperText('Format: JPG/PNG/MP4/PDF/XLSX • Maks 50MB')
                                            ->columnSpanFull(),

                                        TextInput::make('vlink')
                                            ->label('Link Video (URL)')
                                            ->url()
                                            ->placeholder('https://youtube.com/... atau https://drive.google.com/...')
                                            ->helperText('Opsional. Pastikan link bisa diakses sesuai kebijakan akses perusahaan.')
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                    ]),
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('category.name')
                    ->label('Category')
                    ->placeholder('-'),
                TextEntry::make('title'),
                TextEntry::make('slug'),
                TextEntry::make('content')
                    ->placeholder('-')
                    ->html()
                    ->columnSpanFull(),
                TextEntry::make('file')
                    ->placeholder('-'),
                TextEntry::make('vlink')
                    ->placeholder('-'),
                TextEntry::make('status')
                    ->placeholder('-'),
                TextEntry::make('created_by')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('Content')
            ->columns([
                TextColumn::make('category.name')
                    ->searchable(),
                TextColumn::make('title')
                    ->searchable(),
                TextColumn::make('slug')
                    ->searchable(),
                TextColumn::make('file')
                    ->searchable(),
                TextColumn::make('vlink')
                    ->searchable(),
                TextColumn::make('status')
                    ->searchable(),
                TextColumn::make('created_by.name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageContents::route('/'),
        ];
    }
}
