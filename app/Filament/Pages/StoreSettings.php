<?php

namespace App\Filament\Pages;

use App\Models\PaymentMethod;
use App\Models\Setting;
use App\Services\RajaOngkirService;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Support\Arr;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use UnitEnum;

class StoreSettings extends Page implements HasSchemas
{
    use InteractsWithSchemas;

    protected string $view = 'filament.pages.store-settings';

    protected static ?string $title = 'Pengaturan Toko';
    protected ?string $subheading = 'Pengaturan toko untuk mengelola informasi toko, alamat, branding, preferensi, pembayaran, dan ekspedisi.';
    protected static ?string $navigationLabel = 'Pengaturan Toko';
    protected static string|UnitEnum|null $navigationGroup = 'Pengaturan';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill($this->getStoredState());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Pengaturan')
                    ->id('store-settings-tabs')
                    ->persistTabInQueryString()
                    ->tabs([
                        // =========================================================
                        // PROFIL
                        // =========================================================
                        Tab::make('Profil')
                            ->icon('heroicon-m-building-storefront')
                            ->schema([
                                Section::make('Informasi Toko')
                                    ->description('Info dasar yang tampil di storefront, invoice, email, dan footer.')
                                    ->columns(2)
                                    ->schema([
                                        TextInput::make('store.name')
                                            ->label('Nama Toko')
                                            ->helperText('Nama publik (tampil di header, invoice, email).')
                                            ->required()
                                            ->maxLength(120),

                                        TextInput::make('store.legal_name')
                                            ->label('Nama Legal / PT')
                                            ->helperText('Opsional: nama perusahaan untuk invoice & dokumen.')
                                            ->maxLength(180),

                                        TextInput::make('store.email')
                                            ->label('Email Toko')
                                            ->email()
                                            ->helperText('Email utama untuk kontak customer.')
                                            ->maxLength(120),

                                        TextInput::make('store.phone')
                                            ->label('No. Telepon / WhatsApp')
                                            ->tel()
                                            ->helperText('Contoh: +62812xxxx')
                                            ->maxLength(40),

                                        TextInput::make('store.website')
                                            ->label('Website')
                                            ->url()
                                            ->helperText('URL resmi (opsional).')
                                            ->maxLength(180),

                                        Textarea::make('store.description')
                                            ->label('Deskripsi Singkat')
                                            ->helperText('Ringkasan toko untuk halaman profil/storefront.')
                                            ->rows(4)
                                            ->columnSpanFull(),

                                        KeyValue::make('store.opening_hours')
                                            ->label('Jam Operasional')
                                            ->helperText('Key=Hari, Value=Jam. Contoh: Senin=09:00-17:00')
                                            ->addActionLabel('Tambah Hari')
                                            ->keyLabel('Hari')
                                            ->valueLabel('Jam')
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                        // =========================================================
                        // ALAMAT
                        // =========================================================
                        Tab::make('Alamat')
                            ->icon('heroicon-m-map-pin')
                            ->schema([
                                Section::make('Alamat Toko')
                                    ->description('Alamat untuk invoice, pickup, dan info customer.')
                                    ->columns(2)
                                    ->schema([
                                        Textarea::make('address.line1')
                                            ->label('Alamat')
                                            ->helperText('Alamat utama (jalan, nomor, RT/RW).')
                                            ->rows(3)
                                            ->required()
                                            ->columnSpanFull(),

                                        TextInput::make('address.line2')
                                            ->label('Detail Tambahan')
                                            ->helperText('Opsional: gedung, lantai, patokan.')
                                            ->maxLength(180)
                                            ->columnSpanFull(),

                                        TextInput::make('address.city')
                                            ->label('Kota / Kabupaten')
                                            ->required()
                                            ->maxLength(80),

                                        TextInput::make('address.province')
                                            ->label('Provinsi')
                                            ->required()
                                            ->maxLength(80),

                                        TextInput::make('address.postal_code')
                                            ->label('Kode Pos')
                                            ->maxLength(12),

                                        Select::make('address.country')
                                            ->label('Negara')
                                            ->options([
                                                'ID' => 'Indonesia',
                                                'SG' => 'Singapore',
                                                'MY' => 'Malaysia',
                                                'TH' => 'Thailand',
                                                'VN' => 'Vietnam',
                                                'PH' => 'Philippines',
                                                'US' => 'United States',
                                            ])
                                            ->searchable()
                                            ->required()
                                            ->default('ID'),

                                        TextInput::make('address.google_maps_url')
                                            ->label('Google Maps URL')
                                            ->url()
                                            ->helperText('Opsional: link maps untuk customer.')
                                            ->maxLength(255)
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                        // =========================================================
                        // PREFERENSI
                        // =========================================================
                        Tab::make('Preferensi')
                            ->icon('heroicon-m-cog-6-tooth')
                            ->schema([
                                Section::make('Preferensi Umum')
                                    ->description('Pengaturan operasional & format yang dipakai sistem.')
                                    ->columns(2)
                                    ->schema([
                                        Select::make('preferences.currency')
                                            ->label('Mata Uang')
                                            ->options([
                                                'IDR' => 'IDR (Rupiah)',
                                                'USD' => 'USD (US Dollar)',
                                                'SGD' => 'SGD (Singapore Dollar)',
                                                'MYR' => 'MYR (Ringgit)',
                                            ])
                                            ->searchable()
                                            ->required()
                                            ->default('IDR'),

                                        Select::make('preferences.timezone')
                                            ->label('Timezone')
                                            ->options([
                                                'Asia/Jakarta' => 'Asia/Jakarta (WIB)',
                                                'Asia/Makassar' => 'Asia/Makassar (WITA)',
                                                'Asia/Jayapura' => 'Asia/Jayapura (WIT)',
                                                'UTC' => 'UTC',
                                            ])
                                            ->searchable()
                                            ->required()
                                            ->default('Asia/Jakarta'),

                                        Select::make('preferences.language')
                                            ->label('Bahasa')
                                            ->options([
                                                'id' => 'Bahasa Indonesia',
                                                'en' => 'English',
                                            ])
                                            ->required()
                                            ->default('id'),

                                        TextInput::make('preferences.order_prefix')
                                            ->label('Prefix Nomor Order/Invoice')
                                            ->helperText('Contoh: INV, ORD, SAS.')
                                            ->maxLength(12)
                                            ->default('INV'),

                                        TextInput::make('preferences.invoice_due_days')
                                            ->label('Jatuh Tempo Invoice (hari)')
                                            ->numeric()
                                            ->minValue(0)
                                            ->maxValue(365)
                                            ->default(7)
                                            ->helperText('0 = tanpa jatuh tempo.'),

                                        Toggle::make('preferences.tax_included')
                                            ->label('Harga Termasuk Pajak')
                                            ->default(false),

                                        Toggle::make('preferences.enable_guest_checkout')
                                            ->label('Izinkan Checkout Tamu')
                                            ->default(true),

                                        TextInput::make('preferences.support_email')
                                            ->label('Email Support')
                                            ->email()
                                            ->maxLength(120)
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                        // =========================================================
                        // BRANDING
                        // =========================================================
                        Tab::make('Branding')
                            ->icon('heroicon-m-paint-brush')
                            ->schema([
                                Section::make('Identitas Visual')
                                    ->description('Logo, favicon, dan warna utama.')
                                    ->columns(2)
                                    ->schema([
                                        FileUpload::make('branding.logo')
                                            ->label('Logo')
                                            ->helperText('Disarankan PNG transparan. Maks 2MB.')
                                            ->image()
                                            ->imageEditor()
                                            ->disk('public')
                                            ->directory('settings/branding')
                                            ->visibility('public')
                                            ->maxSize(2048),

                                        FileUpload::make('branding.favicon')
                                            ->label('Favicon')
                                            ->helperText('Disarankan PNG 256x256. Maks 1MB.')
                                            ->image()
                                            ->imageEditor()
                                            ->disk('public')
                                            ->directory('settings/branding')
                                            ->visibility('public')
                                            ->maxSize(1024),

                                        ColorPicker::make('branding.primary_color')
                                            ->label('Primary Color')
                                            ->default('#0ea5e9'),

                                        ColorPicker::make('branding.secondary_color')
                                            ->label('Secondary Color')
                                            ->default('#111827'),

                                        TextInput::make('branding.tagline')
                                            ->label('Tagline')
                                            ->maxLength(140)
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                        // =========================================================
                        // SOSIAL
                        // =========================================================
                        Tab::make('Sosial')
                            ->icon('heroicon-m-chat-bubble-left-right')
                            ->schema([
                                Section::make('Social Links')
                                    ->description('Link yang tampil di footer / profil toko.')
                                    ->columns(2)
                                    ->schema([
                                        TextInput::make('social.whatsapp')
                                            ->label('WhatsApp')
                                            ->helperText('Contoh: https://wa.me/62812xxxx')
                                            ->url()
                                            ->maxLength(255),

                                        TextInput::make('social.instagram')
                                            ->label('Instagram')
                                            ->helperText('Contoh: https://instagram.com/username')
                                            ->url()
                                            ->maxLength(255),

                                        TextInput::make('social.tiktok')->label('TikTok')->url()->maxLength(255),
                                        TextInput::make('social.facebook')->label('Facebook')->url()->maxLength(255),
                                        TextInput::make('social.youtube')->label('YouTube')->url()->maxLength(255),
                                        TextInput::make('social.x')->label('X (Twitter)')->url()->maxLength(255),
                                    ]),
                            ]),

                        // =========================================================
                        // PEMBAYARAN (PaymentMethod model)
                        // =========================================================
                        Tab::make('Pembayaran')
                            ->icon('heroicon-m-credit-card')
                            ->schema([
                                Section::make('Metode Pembayaran')
                                    ->description('Diambil dari tabel payment_methods (model PaymentMethod).')
                                    ->columns(1)
                                    ->schema([
                                        Select::make('payment_methods.active')
                                            ->label('Metode Pembayaran Aktif')
                                            ->multiple()
                                            ->searchable()
                                            ->preload()
                                            ->options(fn () => PaymentMethod::query()->orderBy('name')->pluck('name', 'code')->all())
                                            ->helperText('Yang dipilih akan di-set is_active = true.'),
                                    ]),
                            ]),

                        // =========================================================
                        // EKSPEDISI MULTI PROVIDER (FIX DEFAULT PROVIDER OPTIONS)
                        // =========================================================
                        Tab::make('Ekspedisi')
                            ->icon('heroicon-m-truck')
                            ->schema([
                                Section::make('Ekspedisi External (Multi Provider)')
                                    ->description('Support multi-provider dengan default provider yang reaktif.')
                                    ->columns(2)
                                    ->schema([
                                        Toggle::make('shipping.enabled')
                                            ->label('Aktifkan Ekspedisi External')
                                            ->default(false),

                                        Select::make('shipping.mode')
                                            ->label('Mode')
                                            ->options([
                                                'sandbox' => 'Sandbox / Testing',
                                                'production' => 'Production',
                                            ])
                                            ->required()
                                            ->default('production'),

                                        // ✅ FIX: providers harus live + afterStateUpdated untuk sync default_provider
                                        Select::make('shipping.providers')
                                            ->label('Provider Aktif')
                                            ->multiple()
                                            ->searchable()
                                            ->preload()
                                            ->options($this->shippingProviderOptions())
                                            ->helperText('Pilih satu atau lebih provider.')
                                            ->live()
                                            ->afterStateUpdated(function (Set $set, Get $get, $state): void {
                                                $selected = array_values(array_filter((array) $state));
                                                $currentDefault = $get('shipping.default_provider');

                                                if ($selected === []) {
                                                    $set('shipping.default_provider', null);
                                                    return;
                                                }

                                                if (! is_string($currentDefault) || ! in_array($currentDefault, $selected, true)) {
                                                    $set('shipping.default_provider', $selected[0]);
                                                }
                                            })
                                            ->columnSpanFull(),

                                        // ✅ FIX: options hanya dari selected + live + disabled ketika kosong
                                        Select::make('shipping.default_provider')
                                            ->label('Default Provider')
                                            ->live()
                                            ->options(function (Get $get) {
                                                $selected = array_values(array_filter((array) ($get('shipping.providers') ?? [])));
                                                $all = $this->shippingProviderOptions();
                                                return $selected === [] ? [] : array_intersect_key($all, array_flip($selected));
                                            })
                                            ->disabled(fn (Get $get) => empty((array) ($get('shipping.providers') ?? [])))
                                            ->required(fn (Get $get) => (bool) ($get('shipping.enabled') ?? false))
                                            ->helperText('Provider utama untuk kalkulasi/tracking.')
                                            ->columnSpanFull(),

                                        Placeholder::make('shipping.note')
                                            ->label('Catatan')
                                            ->content(new HtmlString('<div class="text-sm opacity-80">Setiap provider bisa punya base URL, endpoint, header, dan query dinamis yang disimpan di <code>shipping.providers_config</code>. Secret per provider disimpan di <code>shipping.keys.{provider}</code> (terenkripsi).</div>'))
                                            ->columnSpanFull(),

                                        TagsInput::make('shipping.couriers')
                                            ->label('Kurir Aktif (Opsional)')
                                            ->placeholder('Tambah kurir...'),

                                        Select::make('shipping.origin_province_id')
                                            ->label('Origin Province')
                                            ->required(fn (Get $get) => (bool) ($get('shipping.enabled') ?? false))
                                            ->options(fn () => $this->originProvinceOptions())
                                            ->searchable()
                                            ->preload()
                                            ->live()
                                            ->native(false)
                                            ->afterStateUpdated(function (Set $set, mixed $state): void {
                                                $selectedId = $this->normalizeRegionId($state);
                                                $provinceLabel = $selectedId ? ($this->originProvinceOptions()[$selectedId] ?? null) : null;

                                                $set('shipping.origin_province_label', $this->toUppercaseLabel($provinceLabel));
                                                $set('shipping.origin_city_id', null);
                                                $set('shipping.origin_city_label', null);
                                                $set('shipping.origin_district_id', null);
                                                $set('shipping.origin_district_label', null);
                                            }),

                                        Select::make('shipping.origin_city_id')
                                            ->label('Origin City')
                                            ->required(fn (Get $get) => (bool) ($get('shipping.enabled') ?? false))
                                            ->options(fn (Get $get) => $this->originCityOptions($get('shipping.origin_province_id')))
                                            ->searchable()
                                            ->preload()
                                            ->live()
                                            ->native(false)
                                            ->disabled(fn (Get $get) => blank($get('shipping.origin_province_id')))
                                            ->afterStateUpdated(function (Set $set, Get $get, mixed $state): void {
                                                $selectedId = $this->normalizeRegionId($state);
                                                $cityOptions = $this->originCityOptions($get('shipping.origin_province_id'));
                                                $cityLabel = $selectedId ? ($cityOptions[$selectedId] ?? null) : null;

                                                $set('shipping.origin_city_label', $this->toUppercaseLabel($cityLabel));
                                                $set('shipping.origin_district_id', null);
                                                $set('shipping.origin_district_label', null);
                                            }),

                                        Select::make('shipping.origin_district_id')
                                            ->label('Origin District')
                                            ->options(fn (Get $get) => $this->originDistrictOptions($get('shipping.origin_city_id')))
                                            ->searchable()
                                            ->preload()
                                            ->native(false)
                                            ->disabled(fn (Get $get) => blank($get('shipping.origin_city_id')))
                                            ->afterStateUpdated(function (Set $set, Get $get, mixed $state): void {
                                                $selectedId = $this->normalizeRegionId($state);
                                                $districtOptions = $this->originDistrictOptions($get('shipping.origin_city_id'));
                                                $districtLabel = $selectedId ? ($districtOptions[$selectedId] ?? null) : null;

                                                $set('shipping.origin_district_label', $this->toUppercaseLabel($districtLabel));
                                            }),

                                        Hidden::make('shipping.origin_province_label'),
                                        Hidden::make('shipping.origin_city_label'),
                                        Hidden::make('shipping.origin_district_label'),

                                        TextInput::make('shipping.origin_postal_code')
                                            ->label('Origin Postal Code')
                                            ->maxLength(20),

                                        Textarea::make('shipping.origin_address')
                                            ->label('Alamat Gudang/Origin')
                                            ->rows(3)
                                            ->columnSpanFull(),

                                        Toggle::make('shipping.use_live_rates')
                                            ->label('Gunakan Live Rates')
                                            ->default(true),

                                        Toggle::make('shipping.enable_tracking')
                                            ->label('Aktifkan Tracking')
                                            ->default(true),

                                        TextInput::make('shipping.cache_ttl_minutes')
                                            ->label('Cache TTL Ongkir (menit)')
                                            ->numeric()
                                            ->minValue(0)
                                            ->maxValue(1440)
                                            ->default(30),
                                    ]),
                            ]),

                        // =========================================================
                        // SEO
                        // =========================================================
                        Tab::make('SEO')
                            ->icon('heroicon-m-globe-alt')
                            ->schema([
                                Section::make('SEO & OpenGraph')
                                    ->description('Judul, deskripsi, keyword, dan OG image.')
                                    ->columns(2)
                                    ->schema([
                                        TextInput::make('seo.meta_title')
                                            ->label('Meta Title')
                                            ->maxLength(120)
                                            ->columnSpanFull(),

                                        Textarea::make('seo.meta_description')
                                            ->label('Meta Description')
                                            ->rows(4)
                                            ->maxLength(300)
                                            ->columnSpanFull(),

                                        TagsInput::make('seo.meta_keywords')
                                            ->label('Keywords')
                                            ->helperText('Pisahkan per item.'),

                                        FileUpload::make('seo.og_image')
                                            ->label('OpenGraph Image')
                                            ->helperText('Rekomendasi 1200x630. Maks 2MB.')
                                            ->image()
                                            ->imageEditor()
                                            ->disk('public')
                                            ->directory('settings/seo')
                                            ->visibility('public')
                                            ->maxSize(2048),
                                    ]),
                            ]),
                    ]),
            ])
            ->statePath('data');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Simpan')
                ->icon('heroicon-m-check')
                ->color('primary')
                ->keyBindings(['mod+s'])
                ->action('save'),

            Action::make('reset')
                ->label('Reset')
                ->icon('heroicon-m-arrow-path')
                ->color('gray')
                ->requiresConfirmation()
                ->action('resetForm'),
        ];
    }

    public function save(): void
    {
        $state = $this->form->getState();
        $state = $this->syncShippingOriginLabels($state);

        foreach ($this->getSettingPaths() as $path) {
            $raw = data_get($state, $path);

            // Secret: kalau kosong, jangan overwrite
            if ($this->isSecretPath($path) && ($raw === null || $raw === '')) {
                continue;
            }

            $key = $this->resolveExistingKeyForWrite($path);

            if ($this->isSecretPath($path)) {
                $value = Crypt::encryptString((string) $raw);
                $type = 'text';
            } else {
                $value = $this->serializeValueForStorage($path, $raw);
                $type = $this->getSettingType($path);
            }

            Setting::query()->updateOrCreate(
                ['key' => $key],
                [
                    'value' => $value,
                    'type'  => $type,
                    'group' => $this->getSettingGroup($path),
                ],
            );
        }

        // Update Payment Methods
        $activeCodes = collect(data_get($state, 'payment_methods.active', []))->filter()->values()->all();
        PaymentMethod::query()->update(['is_active' => false]);
        if (! empty($activeCodes)) {
            PaymentMethod::query()->whereIn('code', $activeCodes)->update(['is_active' => true]);
        }

        Notification::make()
            ->success()
            ->title('Tersimpan')
            ->body('Pengaturan berhasil diperbarui.')
            ->send();
    }

    public function resetForm(): void
    {
        $this->form->fill($this->getStoredState());

        Notification::make()
            ->info()
            ->title('Di-reset')
            ->body('Form dikembalikan ke data terakhir yang tersimpan.')
            ->send();
    }

    protected function getStoredState(): array
    {
        $default = $this->getDefaultState();

        $keys = collect($this->getSettingPaths())
            ->flatMap(fn (string $path) => $this->candidateKeys($path))
            ->unique()
            ->values()
            ->all();

        $rows = Setting::query()
            ->whereIn('key', $keys)
            ->get()
            ->keyBy('key');

        $state = $default;

        foreach ($this->getSettingPaths() as $path) {
            // Secret tidak di-fill kembali
            if ($this->isSecretPath($path)) {
                continue;
            }

            $row = collect($this->candidateKeys($path))
                ->map(fn (string $k) => $rows->get($k))
                ->first(fn ($r) => $r !== null);

            if (! $row) {
                continue;
            }

            Arr::set($state, $path, $this->parseValueFromStorage($path, $row->value));
        }

        Arr::set(
            $state,
            'payment_methods.active',
            PaymentMethod::query()->where('is_active', true)->pluck('code')->values()->all()
        );

        return $state;
    }

    protected function candidateKeys(string $path): array
    {
        $prefix = $this->getKeyPrefix();
        return $prefix ? [$prefix . $path, $path] : [$path];
    }

    protected function resolveExistingKeyForWrite(string $path): string
    {
        $candidates = $this->candidateKeys($path);
        $existing = Setting::query()->whereIn('key', $candidates)->value('key');
        return $existing ?: $candidates[0];
    }

    protected function getKeyPrefix(): string
    {
        try {
            $tenant = Filament::getTenant();
        } catch (\Throwable) {
            $tenant = null;
        }

        return $tenant ? ('tenant:' . (string) $tenant->getKey() . '.') : '';
    }

    protected function shippingProviderOptions(): array
    {
        return [
            'rajaongkir' => 'RajaOngkir',
            'shipper' => 'Shipper',
            'binderbyte' => 'BinderByte',
            'lion_parcel' => 'Lion Parcel (Custom)',
            'manual' => 'Manual (Tanpa API)',
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function originProvinceOptions(): array
    {
        $provinces = Cache::remember('store_settings:rajaongkir:provinces', now()->addHours(6), function (): array {
            return app(RajaOngkirService::class)->getProvinces();
        });

        $options = [];

        foreach ($provinces as $province) {
            $id = $this->extractRajaOngkirValue($province, ['id', 'province_id']);
            $name = $this->extractRajaOngkirValue($province, ['province_name', 'province', 'name']);

            if (blank($id) || blank($name)) {
                continue;
            }

            $options[(string) $id] = $this->toUppercaseLabel((string) $name);
        }

        return $options;
    }

    /**
     * @return array<string, string>
     */
    protected function originCityOptions(mixed $provinceId): array
    {
        $provinceId = $this->normalizeRegionId($provinceId);

        if ($provinceId === null) {
            return [];
        }

        $cacheKey = "store_settings:rajaongkir:cities:{$provinceId}";

        $cities = Cache::remember($cacheKey, now()->addHours(6), function () use ($provinceId): array {
            return app(RajaOngkirService::class)->getCities((int) $provinceId);
        });

        $options = [];

        foreach ($cities as $city) {
            $id = $this->extractRajaOngkirValue($city, ['id', 'city_id']);
            $name = $this->extractRajaOngkirValue($city, ['city_name', 'city', 'name']);
            $type = $this->extractRajaOngkirValue($city, ['type']);

            if (blank($id) || blank($name)) {
                continue;
            }

            $label = trim((string) (filled($type) ? "{$type} {$name}" : $name));
            $options[(string) $id] = $this->toUppercaseLabel($label);
        }

        return $options;
    }

    /**
     * @return array<string, string>
     */
    protected function originDistrictOptions(mixed $cityId): array
    {
        $cityId = $this->normalizeRegionId($cityId);

        if ($cityId === null) {
            return [];
        }

        $cacheKey = "store_settings:rajaongkir:districts:{$cityId}";

        $districts = Cache::remember($cacheKey, now()->addHours(6), function () use ($cityId): array {
            return app(RajaOngkirService::class)->getDistricts((int) $cityId);
        });

        $options = [];

        foreach ($districts as $district) {
            $id = $this->extractRajaOngkirValue($district, ['id', 'district_id', 'subdistrict_id']);
            $name = $this->extractRajaOngkirValue($district, ['district_name', 'subdistrict_name', 'district', 'name']);

            if (blank($id) || blank($name)) {
                continue;
            }

            $options[(string) $id] = $this->toUppercaseLabel((string) $name);
        }

        return $options;
    }

    protected function normalizeRegionId(mixed $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        if (! is_scalar($value)) {
            return null;
        }

        $stringValue = trim((string) $value);

        return $stringValue !== '' ? $stringValue : null;
    }

    protected function extractRajaOngkirValue(mixed $row, array $keys): mixed
    {
        foreach ($keys as $key) {
            if (is_array($row) && array_key_exists($key, $row)) {
                return $row[$key];
            }

            if (is_object($row) && isset($row->{$key})) {
                return $row->{$key};
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $state
     * @return array<string, mixed>
     */
    protected function syncShippingOriginLabels(array $state): array
    {
        $provinceId = $this->normalizeRegionId(data_get($state, 'shipping.origin_province_id'));
        $provinceOptions = $this->originProvinceOptions();
        $provinceLabel = $provinceId ? ($provinceOptions[$provinceId] ?? data_get($state, 'shipping.origin_province_label')) : null;

        $cityId = $this->normalizeRegionId(data_get($state, 'shipping.origin_city_id'));
        $cityOptions = $this->originCityOptions($provinceId);
        $cityLabel = $cityId ? ($cityOptions[$cityId] ?? data_get($state, 'shipping.origin_city_label')) : null;

        $districtId = $this->normalizeRegionId(data_get($state, 'shipping.origin_district_id'));
        $districtOptions = $this->originDistrictOptions($cityId);
        $districtLabel = $districtId ? ($districtOptions[$districtId] ?? data_get($state, 'shipping.origin_district_label')) : null;

        data_set($state, 'shipping.origin_province_label', $this->toUppercaseLabel($provinceLabel));
        data_set($state, 'shipping.origin_city_label', $this->toUppercaseLabel($cityLabel));
        data_set($state, 'shipping.origin_district_label', $this->toUppercaseLabel($districtLabel));

        return $state;
    }

    protected function toUppercaseLabel(mixed $label): ?string
    {
        if (blank($label)) {
            return null;
        }

        return Str::upper(trim((string) $label));
    }

    // ==========================================================
    // DATA MAPS
    // ==========================================================
    protected function getSettingPaths(): array
    {
        return [
            'store.name',
            'store.legal_name',
            'store.email',
            'store.phone',
            'store.website',
            'store.description',
            'store.opening_hours',

            'address.line1',
            'address.line2',
            'address.city',
            'address.province',
            'address.postal_code',
            'address.country',
            'address.google_maps_url',

            'preferences.currency',
            'preferences.timezone',
            'preferences.language',
            'preferences.order_prefix',
            'preferences.invoice_due_days',
            'preferences.tax_included',
            'preferences.enable_guest_checkout',
            'preferences.support_email',

            'branding.logo',
            'branding.favicon',
            'branding.primary_color',
            'branding.secondary_color',
            'branding.tagline',

            'social.whatsapp',
            'social.instagram',
            'social.tiktok',
            'social.facebook',
            'social.youtube',
            'social.x',

            // shipping multi provider
            'shipping.enabled',
            'shipping.mode',
            'shipping.providers',
            'shipping.default_provider',
            'shipping.couriers',
            'shipping.origin_province_id',
            'shipping.origin_province_label',
            'shipping.origin_city_id',
            'shipping.origin_city_label',
            'shipping.origin_district_id',
            'shipping.origin_district_label',
            'shipping.origin_postal_code',
            'shipping.origin_address',
            'shipping.use_live_rates',
            'shipping.enable_tracking',
            'shipping.cache_ttl_minutes',

            // seo
            'seo.meta_title',
            'seo.meta_description',
            'seo.meta_keywords',
            'seo.og_image',
        ];
    }

    protected function getSettingGroup(string $path): string
    {
        return str_starts_with($path, 'social.') ? 'social' : 'general';
    }

    protected function getSettingType(string $path): string
    {
        return in_array($path, $this->imagePaths(), true) ? 'image' : 'text';
    }

    protected function imagePaths(): array
    {
        return [
            'branding.logo',
            'branding.favicon',
            'seo.og_image',
        ];
    }

    protected function jsonPaths(): array
    {
        return [
            'store.opening_hours',
            'seo.meta_keywords',
            'shipping.providers',
            'shipping.couriers',
        ];
    }

    protected function boolPaths(): array
    {
        return [
            'preferences.tax_included',
            'preferences.enable_guest_checkout',
            'shipping.enabled',
            'shipping.use_live_rates',
            'shipping.enable_tracking',
        ];
    }

    protected function intPaths(): array
    {
        return [
            'preferences.invoice_due_days',
            'shipping.cache_ttl_minutes',
        ];
    }

    protected function secretPaths(): array
    {
        return []; // tambahkan secret path jika ada
    }

    protected function isSecretPath(string $path): bool
    {
        return in_array($path, $this->secretPaths(), true);
    }

    protected function serializeValueForStorage(string $path, mixed $value): ?string
    {
        if (in_array($path, $this->jsonPaths(), true)) {
            $value = is_array($value) ? $value : [];
            return json_encode($value, JSON_UNESCAPED_UNICODE);
        }

        if (in_array($path, $this->boolPaths(), true)) {
            return $value ? '1' : '0';
        }

        if (in_array($path, $this->intPaths(), true)) {
            return is_null($value) ? null : (string) ((int) $value);
        }

        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE);
        }

        return is_null($value) ? null : (string) $value;
    }

    protected function parseValueFromStorage(string $path, ?string $stored): mixed
    {
        if ($stored === null) {
            return in_array($path, $this->jsonPaths(), true) ? [] : null;
        }

        if (in_array($path, $this->jsonPaths(), true)) {
            $decoded = json_decode($stored, true);
            return is_array($decoded) ? $decoded : [];
        }

        if (in_array($path, $this->boolPaths(), true)) {
            return in_array($stored, ['1', 'true', 'yes', 'on'], true);
        }

        if (in_array($path, $this->intPaths(), true)) {
            return (int) $stored;
        }

        return $stored;
    }

    protected function getDefaultState(): array
    {
        return [
            'store' => [
                'name' => null,
                'legal_name' => null,
                'email' => null,
                'phone' => null,
                'website' => null,
                'description' => null,
                'opening_hours' => [],
            ],
            'address' => [
                'line1' => null,
                'line2' => null,
                'city' => null,
                'province' => null,
                'postal_code' => null,
                'country' => 'ID',
                'google_maps_url' => null,
            ],
            'preferences' => [
                'currency' => 'IDR',
                'timezone' => 'Asia/Jakarta',
                'language' => 'id',
                'order_prefix' => 'INV',
                'invoice_due_days' => 7,
                'tax_included' => false,
                'enable_guest_checkout' => true,
                'support_email' => null,
            ],
            'branding' => [
                'logo' => null,
                'favicon' => null,
                'primary_color' => '#0ea5e9',
                'secondary_color' => '#111827',
                'tagline' => null,
            ],
            'social' => [
                'whatsapp' => null,
                'instagram' => null,
                'tiktok' => null,
                'facebook' => null,
                'youtube' => null,
                'x' => null,
            ],
            'payment_methods' => [
                'active' => [],
            ],
            'shipping' => [
                'enabled' => false,
                'mode' => 'production',
                'providers' => [],
                'default_provider' => null,
                'couriers' => [],
                'origin_province_id' => null,
                'origin_province_label' => null,
                'origin_city_id' => null,
                'origin_city_label' => null,
                'origin_district_id' => null,
                'origin_district_label' => null,
                'origin_postal_code' => null,
                'origin_address' => null,
                'use_live_rates' => true,
                'enable_tracking' => true,
                'cache_ttl_minutes' => 30,
            ],
            'seo' => [
                'meta_title' => null,
                'meta_description' => null,
                'meta_keywords' => [],
                'og_image' => null,
            ],
        ];
    }
}
