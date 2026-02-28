<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Models\Order;
use App\Models\Setting;
use App\Models\Shipment;
use App\Services\Shipping\LionParcelService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select as FormsSelect;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\Summarizers\Count;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;

class OrdersTable
{
    private static function hiddenByDefault(Column $column): Column
    {
        return $column->toggleable(isToggledHiddenByDefault: true);
    }

    private static function currencyOptions(): array
    {
        return Order::query()
            ->whereNotNull('currency')
            ->where('currency', '!=', '')
            ->distinct()
            ->orderBy('currency')
            ->pluck('currency', 'currency')
            ->all();
    }

    private static function statusOptions(): array
    {
        $known = [
            'pending' => 'pending',
            'PAID' => 'PAID',
            'shipped' => 'shipped',
            'delivered' => 'delivered',
            'cancelled' => 'cancelled',
        ];

        $fromData = Order::query()
            ->whereNotNull('status')
            ->where('status', '!=', '')
            ->distinct()
            ->orderBy('status')
            ->pluck('status', 'status')
            ->all();

        return array_merge($known, $fromData);
    }

    private static function typeOptions(): array
    {
        $known = [
            'planA' => 'planA',
            'planB' => 'planB',
        ];

        $fromData = Order::query()
            ->whereNotNull('type')
            ->where('type', '!=', '')
            ->distinct()
            ->orderBy('type')
            ->pluck('type', 'type')
            ->all();

        return array_merge($known, $fromData);
    }

    private static function statusColor(?string $status): string
    {
        return match (strtolower((string) $status)) {
            'paid', 'delivered' => 'success',
            'shipped' => 'info',
            'pending' => 'warning',
            'cancelled' => 'danger',
            default => 'gray',
        };
    }

    private static function typeColor(?string $type): string
    {
        return match ((string) $type) {
            'planA' => 'primary',
            'planB' => 'info',
            default => 'gray',
        };
    }

    private static function summarizeMoneyByCurrency(Builder|QueryBuilder $query, string $amountColumn): string
    {
        $rows = (clone $query)
            ->reorder()
            ->selectRaw("currency, SUM({$amountColumn}) as total")
            ->groupBy('currency')
            ->orderBy('currency')
            ->get();

        if ($rows->isEmpty()) {
            return '—';
        }

        return $rows
            ->map(function ($row): string {
                $currency = $row->currency ?: '—';
                $total = (float) ($row->total ?? 0);
                $formatted = number_format($total, 0, ',', '.');

                return "{$currency} {$formatted}";
            })
            ->implode(' | ');
    }

    private static function moneySummaries(string $column, string $label = 'Total'): array
    {
        return [
            Sum::make()
                ->label($label)
                ->numeric(decimalPlaces: 0)
                ->visible(fn ($query): bool => (clone $query)->distinct()->count('currency') <= 1),

            Summarizer::make("{$column}_by_currency")
                ->label("{$label} per mata uang")
                ->visible(fn ($query): bool => (clone $query)->distinct()->count('currency') > 1)
                ->using(fn (QueryBuilder $query): string => self::summarizeMoneyByCurrency($query, $column)),
        ];
    }

    /**
     * @return array<int, string>
     */
    private static function normalizeAppliedPromos(mixed $state): array
    {
        if (blank($state)) {
            return [];
        }

        if (is_string($state)) {
            $decoded = json_decode($state, true);
            $state = is_array($decoded) ? $decoded : [$state];
        }

        if (! is_array($state)) {
            return [];
        }

        return collect($state)
            ->map(function (mixed $value): ?string {
                if (is_scalar($value)) {
                    return (string) $value;
                }

                if (is_array($value)) {
                    if (isset($value['code']) && is_scalar($value['code'])) {
                        return (string) $value['code'];
                    }

                    return json_encode($value, JSON_UNESCAPED_UNICODE) ?: null;
                }

                return null;
            })
            ->filter(fn (?string $value): bool => filled($value))
            ->values()
            ->all();
    }

    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(self::baseQuery())
            ->defaultSort('created_at', 'desc')
            ->groups(self::groups())
            ->defaultGroup('status')
            ->groupingSettingsInDropdownOnDesktop()
            ->columns(self::columns())
            ->filters(self::filters(), layout: FiltersLayout::AboveContentCollapsible)
            ->filtersFormColumns(6)
            ->filtersFormSchema(fn (array $filters): array => self::filtersFormSchema($filters))
            ->recordActions(self::recordActions())
            ->toolbarActions(self::toolbarActions());
    }

    private static function baseQuery(): \Closure
    {
        return fn (Builder $query): Builder => $query
            ->with([
                'customer:id,name',
                'shippingAddress:id,recipient_name,city_label',
                'billingAddress:id,recipient_name,city_label',
            ])
            ->withCount(['items', 'payments', 'shipments', 'refunds', 'returns', 'bonusCashbacks'])
            ->withSum('items', 'qty');
    }

    private static function groups(): array
    {
        return [
            Group::make('status')
                ->label('Status')
                ->collapsible()
                ->titlePrefixedWithLabel(false)
                ->getTitleFromRecordUsing(function (Order $record): string {
                    $status = trim((string) $record->status);

                    return $status !== '' ? strtoupper($status) : 'TANPA STATUS';
                }),

            Group::make('type')
                ->label('Tipe')
                ->collapsible()
                ->titlePrefixedWithLabel(false)
                ->getTitleFromRecordUsing(function (Order $record): string {
                    $type = trim((string) $record->type);

                    return $type !== '' ? $type : 'Tanpa tipe';
                }),

            Group::make('customer.name')
                ->label('Pelanggan')
                ->collapsible()
                ->titlePrefixedWithLabel(false)
                ->getTitleFromRecordUsing(function (Order $record): string {
                    $customerName = trim((string) data_get($record, 'customer.name'));

                    return $customerName !== '' ? $customerName : 'Tanpa pelanggan';
                }),

            Group::make('currency')
                ->label('Mata Uang')
                ->collapsible()
                ->titlePrefixedWithLabel(false)
                ->getTitleFromRecordUsing(function (Order $record): string {
                    $currency = trim((string) $record->currency);

                    return $currency !== '' ? strtoupper($currency) : 'Tanpa mata uang';
                }),

            Group::make('created_at')
                ->label('Tanggal Dibuat')
                ->date()
                ->collapsible(),
        ];
    }

    private static function columns(): array
    {
        return [
            self::hiddenByDefault(
                TextColumn::make('id')
                    ->label('ID')
                    ->numeric()
                    ->sortable()
            ),

            TextColumn::make('order_no')
                ->label('Nomor Pesanan')
                ->searchable()
                ->sortable()
                ->copyable()
                ->summarize(
                    Count::make()
                        ->label('Total Pesanan')
                        ->numeric()
                ),

            TextColumn::make('customer.name')
                ->label('Pelanggan')
                ->searchable()
                ->sortable(),

            TextColumn::make('status')
                ->badge()
                ->color(fn (?string $state): string => self::statusColor($state))
                ->searchable()
                ->sortable(),

            TextColumn::make('type')
                ->badge()
                ->color(fn (?string $state): string => self::typeColor($state))
                ->sortable(),

            self::hiddenByDefault(
                TextColumn::make('currency')
                    ->label('Mata Uang')
                    ->badge()
                    ->searchable()
                    ->sortable()
            ),

            TextColumn::make('items_count')
                ->label('Jumlah Item')
                ->numeric()
                ->alignEnd()
                ->sortable()
                ->summarize(Sum::make()->label('Total')->numeric(decimalPlaces: 0)),

            TextColumn::make('items_sum_qty')
                ->label('Total Kuantitas')
                ->numeric()
                ->alignEnd()
                ->sortable()
                ->summarize(Sum::make()->label('Total')->numeric(decimalPlaces: 0)),

            self::hiddenByDefault(
                TextColumn::make('shipments_count')
                    ->label('Pengiriman')
                    ->numeric()
                    ->alignEnd()
                    ->sortable()
                    ->summarize(Sum::make()->label('Total')->numeric(decimalPlaces: 0))
            ),

            self::hiddenByDefault(
                TextColumn::make('refunds_count')
                    ->label('Pengembalian Dana')
                    ->numeric()
                    ->alignEnd()
                    ->sortable()
                    ->summarize(Sum::make()->label('Total')->numeric(decimalPlaces: 0))
            ),

            self::hiddenByDefault(
                TextColumn::make('returns_count')
                    ->label('Retur')
                    ->numeric()
                    ->alignEnd()
                    ->sortable()
                    ->summarize(Sum::make()->label('Total')->numeric(decimalPlaces: 0))
            ),

            TextColumn::make('grand_total')
                ->label('Total Akhir')
                ->money(fn ($record) => $record->currency ?: 'IDR')
                ->alignEnd()
                ->weight('bold')
                ->sortable()
                ->summarize(self::moneySummaries('grand_total', 'Total Akhir')),

            self::hiddenByDefault(
                TextColumn::make('subtotal_amount')
                    ->label('Subtotal')
                    ->money(fn ($record) => $record->currency ?: 'IDR')
                    ->alignEnd()
                    ->sortable()
                    ->summarize(self::moneySummaries('subtotal_amount', 'Subtotal'))
            ),

            self::hiddenByDefault(
                TextColumn::make('discount_amount')
                    ->label('Diskon')
                    ->money(fn ($record) => $record->currency ?: 'IDR')
                    ->alignEnd()
                    ->sortable()
                    ->summarize(self::moneySummaries('discount_amount', 'Diskon'))
            ),

            self::hiddenByDefault(
                TextColumn::make('shipping_amount')
                    ->label('Ongkir')
                    ->money(fn ($record) => $record->currency ?: 'IDR')
                    ->alignEnd()
                    ->sortable()
                    ->summarize(self::moneySummaries('shipping_amount', 'Ongkir'))
            ),

            self::hiddenByDefault(
                TextColumn::make('tax_amount')
                    ->label('Pajak')
                    ->money(fn ($record) => $record->currency ?: 'IDR')
                    ->alignEnd()
                    ->sortable()
                    ->summarize(self::moneySummaries('tax_amount', 'Pajak'))
            ),

            TextColumn::make('bv_amount')
                ->label('BV')
                ->numeric(decimalPlaces: 2)
                ->alignEnd()
                ->sortable()
                ->summarize(Sum::make()->label('Total BV')->numeric(decimalPlaces: 2)),

            IconColumn::make('bonus_generated')
                ->label('Bonus Terbentuk')
                ->boolean(),

            self::hiddenByDefault(
                TextColumn::make('applied_promos')
                    ->label('Promo')
                    ->badge()
                    ->formatStateUsing(function (mixed $state): string {
                        $count = count(self::normalizeAppliedPromos($state));

                        return $count > 0 ? "{$count} promo" : '—';
                    })
                    ->color(function (mixed $state): string {
                        $count = count(self::normalizeAppliedPromos($state));

                        return $count > 0 ? 'success' : 'gray';
                    })
                    ->tooltip(function ($record): ?string {
                        $promos = self::normalizeAppliedPromos($record->applied_promos);

                        return empty($promos) ? null : implode(', ', $promos);
                    })
            ),

            self::hiddenByDefault(
                TextColumn::make('shippingAddress.recipient_name')
                    ->label('Penerima Kirim')
                    ->placeholder('-')
                    ->searchable()
            ),

            self::hiddenByDefault(
                TextColumn::make('shippingAddress.city_label')
                    ->label('Kota Kirim')
                    ->placeholder('-')
                    ->searchable()
            ),

            self::hiddenByDefault(
                TextColumn::make('placed_at')
                    ->label('Waktu Pemesanan')
                    ->dateTime()
                    ->placeholder('-')
                    ->sortable()
            ),

            self::hiddenByDefault(
                TextColumn::make('paid_at')
                    ->label('Waktu Pembayaran')
                    ->dateTime()
                    ->placeholder('-')
                    ->sortable()
            ),

            self::hiddenByDefault(
                TextColumn::make('processed_at')
                    ->label('Waktu Diproses')
                    ->dateTime()
                    ->placeholder('-')
                    ->sortable()
            ),

            self::hiddenByDefault(
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable()
            ),

            self::hiddenByDefault(
                TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime()
                    ->sortable()
            ),
        ];
    }

    private static function filters(): array
    {
        return [
            SelectFilter::make('customer_id')
                ->label('Pelanggan')
                ->relationship('customer', 'username')
                ->searchable()
                ->preload()
                ->placeholder('Semua pelanggan'),

            SelectFilter::make('status')
                ->label('Status')
                ->options(fn (): array => self::statusOptions())
                ->searchable()
                ->placeholder('Semua status'),

            SelectFilter::make('type')
                ->label('Tipe')
                ->options(fn (): array => self::typeOptions())
                ->placeholder('Semua tipe'),

            Filter::make('order_no_search')
                ->label('Nomor Pesanan')
                ->schema([
                    TextInput::make('q')
                        ->label('Cari Nomor Pesanan')
                        ->placeholder('Contoh: ORD-2026-00123')
                        ->maxLength(255),
                ])
                ->query(function (Builder $query, array $data): Builder {
                    $keyword = trim((string) ($data['q'] ?? ''));

                    if ($keyword === '') {
                        return $query;
                    }

                    return $query->where('order_no', 'like', '%'.$keyword.'%');
                })
                ->indicateUsing(function (array $data): array {
                    $keyword = trim((string) ($data['q'] ?? ''));

                    return $keyword !== ''
                        ? [Indicator::make("Nomor Pesanan: {$keyword}")->removeField('q')]
                        : [];
                }),
        ];
    }

    private static function filtersFormSchema(array $filters): array
    {
        return [
            $filters['customer_id'],
            $filters['status'],
            $filters['type'],

            $filters['order_no_search'],
        ];
    }

    private static function recordActions(): array
    {
        return [
            Action::make('preview_invoice')
                ->icon('heroicon-o-eye')
                ->tooltip('Preview invoice')
                ->visible(fn (Order $record): bool => filled($record->paid_at))
                ->url(fn (Order $record): string => route('control-panel.orders.invoice', ['order' => $record, 'preview' => 1]))
                ->openUrlInNewTab()
                ->link(),
            Action::make('download_invoice')
                ->icon(Heroicon::ArrowDownCircle)
                ->tooltip('Download invoice')
                ->visible(fn (Order $record): bool => filled($record->paid_at))
                ->url(fn (Order $record): string => route('control-panel.orders.invoice', ['order' => $record]))
                ->openUrlInNewTab()
                ->link(),
            ActionGroup::make([
                ViewAction::make(),
                EditAction::make(),
                Action::make(name: 'kirim_pesanan')
                    ->label('Proses Kirim Barang')
                    ->icon('heroicon-o-truck')
                    ->color('success')
                    ->modalWidth('7xl')
                    ->requiresConfirmation()
                    ->modalHeading('Proses Kirim Barang (Lion Parcel)')
                    ->modalDescription(fn (Order $record): string => "Lengkapi data booking pengiriman untuk pesanan {$record->order_no}.")
                    ->modalSubmitActionLabel('Booking & Simpan Pengiriman')
                    ->form([
                        Section::make('Ringkasan Pesanan')
                            ->description('Informasi referensi pesanan yang sedang diproses.')
                            ->columns(12)
                            ->schema([
                                TextInput::make('order_no')
                                    ->label('Nomor Pesanan')
                                    ->default(fn (Order $record): string => (string) $record->order_no)
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->helperText('ID pesanan internal, hanya untuk referensi.')
                                    ->columnSpan([
                                        'default' => 12,
                                        'md' => 4,
                                    ]),

                                TextInput::make('customer_name')
                                    ->label('Pelanggan')
                                    ->default(fn (Order $record): string => (string) ($record->customer?->name ?? '-'))
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->helperText('Nama customer pemesan, otomatis dari order.')
                                    ->columnSpan([
                                        'default' => 12,
                                        'md' => 4,
                                    ]),

                                TextInput::make('shipping_destination')
                                    ->label('Tujuan Pengiriman')
                                    ->default(fn (Order $record): string => self::shippingDestination($record))
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->helperText('Ringkasan alamat kirim dari data order.')
                                    ->columnSpan([
                                        'default' => 12,
                                        'md' => 4,
                                    ]),
                            ])
                            ->columnSpanFull(),

                        Section::make('Pengaturan Shipment')
                            ->description('Data shipment yang disimpan ke tabel pengiriman.')
                            ->columns(12)
                            ->schema([
                                FormsSelect::make('courier_id')
                                    ->label('Kurir')
                                    ->options(fn (): array => ['lion' => Shipment::courierOptions()['lion'] ?? 'Lion Parcel'])
                                    ->default('lion')
                                    ->native(false)
                                    ->required()
                                    ->disabled()
                                    ->dehydrated()
                                    ->helperText('Kurir dikunci ke Lion Parcel untuk action ini.')
                                    ->columnSpan([
                                        'default' => 12,
                                        'md' => 4,
                                    ]),

                                FormsSelect::make('status')
                                    ->label('Status Pengiriman')
                                    ->options([
                                        'pending' => 'Pending',
                                        'READY_TO_SHIP' => 'Siap Dikirim',
                                        'shipped' => 'Dikirim',
                                        'delivered' => 'Diterima',
                                    ])
                                    ->default('shipped')
                                    ->native(false)
                                    ->live()
                                    ->required()
                                    ->helperText('Status awal shipment setelah booking berhasil.')
                                    ->columnSpan([
                                        'default' => 12,
                                        'md' => 4,
                                    ]),

                                TextInput::make('shipping_fee')
                                    ->label('Biaya Pengiriman')
                                    ->numeric()
                                    ->minValue(0)
                                    ->required()
                                    ->prefix('IDR')
                                    ->helperText('Nominal ongkir yang tercatat pada shipment.')
                                    ->columnSpan([
                                        'default' => 12,
                                        'md' => 4,
                                    ]),

                                TextInput::make('tracking_no')
                                    ->label('Nomor Resi (Override Manual)')
                                    ->placeholder('Kosongkan untuk menggunakan hasil dari API')
                                    ->maxLength(120)
                                    ->helperText('Isi hanya jika ingin override resi dari respons API.')
                                    ->columnSpan([
                                        'default' => 12,
                                        'md' => 6,
                                    ]),

                                DateTimePicker::make('shipped_at')
                                    ->label('Waktu Dikirim')
                                    ->seconds(false)
                                    ->visible(fn (callable $get): bool => in_array((string) $get('status'), ['shipped', 'delivered'], true))
                                    ->required(fn (callable $get): bool => in_array((string) $get('status'), ['shipped', 'delivered'], true))
                                    ->helperText('Wajib jika status Dikirim atau Diterima.')
                                    ->columnSpan([
                                        'default' => 12,
                                        'md' => 3,
                                    ]),

                                DateTimePicker::make('delivered_at')
                                    ->label('Waktu Diterima')
                                    ->seconds(false)
                                    ->visible(fn (callable $get): bool => (string) $get('status') === 'delivered')
                                    ->required(fn (callable $get): bool => (string) $get('status') === 'delivered')
                                    ->afterOrEqual('shipped_at')
                                    ->helperText('Wajib jika status Diterima, harus >= waktu dikirim.')
                                    ->columnSpan([
                                        'default' => 12,
                                        'md' => 3,
                                    ]),
                            ])
                            ->columnSpanFull(),

                        Section::make('Data Booking Lion Parcel (STT)')
                            ->description('Semua field ini dikirim ke endpoint `/client/booking` pada objek `stt`.')
                            ->columns(12)
                            ->schema([
                                TextInput::make('stt_no_ref_external')
                                    ->label('Ref Eksternal (stt_no_ref_external)')
                                    ->required()
                                    ->maxLength(100)
                                    ->helperText('Referensi unik dari sistem internal, umumnya nomor order.')
                                    ->columnSpan([
                                        'default' => 12,
                                        'md' => 4,
                                    ]),

                                TextInput::make('stt_no')
                                    ->label('Nomor STT (Opsional)')
                                    ->placeholder('Biarkan kosong bila generate otomatis dari Lion Parcel')
                                    ->maxLength(100)
                                    ->helperText('Opsional. Kosongkan jika STT dibuat otomatis oleh provider.')
                                    ->columnSpan([
                                        'default' => 12,
                                        'md' => 4,
                                    ]),

                                TextInput::make('stt_tax_number')
                                    ->label('Nomor Pajak (Opsional)')
                                    ->maxLength(100)
                                    ->helperText('Isi jika diperlukan untuk keperluan dokumen pajak.')
                                    ->columnSpan([
                                        'default' => 12,
                                        'md' => 4,
                                    ]),

                                TextInput::make('stt_product_type')
                                    ->label('Product Type')
                                    ->required()
                                    ->maxLength(50)
                                    ->default('regpack')
                                    ->helperText('Kode layanan Lion Parcel, contoh: regpack.')
                                    ->columnSpan([
                                        'default' => 12,
                                        'md' => 3,
                                    ]),

                                TextInput::make('stt_commodity_code')
                                    ->label('Commodity Code')
                                    ->required()
                                    ->maxLength(50)
                                    ->default((string) config('services.lion_parcel.commodity', 'ABR036'))
                                    ->helperText('Kode komoditi pengiriman sesuai konfigurasi Lion Parcel.')
                                    ->columnSpan([
                                        'default' => 12,
                                        'md' => 3,
                                    ]),

                                FormsSelect::make('stt_insurance_type')
                                    ->label('Tipe Asuransi')
                                    ->options([
                                        'free' => 'Free',
                                        'paid' => 'Paid',
                                    ])
                                    ->default('free')
                                    ->native(false)
                                    ->required()
                                    ->helperText('Pilih tipe proteksi/asuransi untuk paket.')
                                    ->columnSpan([
                                        'default' => 12,
                                        'md' => 3,
                                    ]),

                                TextInput::make('stt_goods_estimate_price')
                                    ->label('Estimasi Harga Barang')
                                    ->numeric()
                                    ->minValue(1)
                                    ->required()
                                    ->prefix('IDR')
                                    ->helperText('Nilai estimasi barang untuk kebutuhan perhitungan risiko.')
                                    ->columnSpan([
                                        'default' => 12,
                                        'md' => 3,
                                    ]),

                                TextInput::make('stt_goods_status')
                                    ->label('Status Barang')
                                    ->default('')
                                    ->maxLength(100)
                                    ->helperText('Status kondisi barang, bisa dikosongkan bila tidak ada ketentuan.')
                                    ->columnSpan([
                                        'default' => 12,
                                        'md' => 6,
                                    ]),

                                TextInput::make('stt_origin')
                                    ->label('Origin')
                                    ->required()
                                    ->maxLength(255)
                                    ->helperText('Asal kiriman (kecamatan/kota) sesuai format Lion Parcel.')
                                    ->columnSpan([
                                        'default' => 12,
                                        'md' => 6,
                                    ]),

                                TextInput::make('stt_destination')
                                    ->label('Destination')
                                    ->required()
                                    ->maxLength(255)
                                    ->helperText('Tujuan kiriman (district Lion atau label tujuan yang valid).')
                                    ->columnSpan([
                                        'default' => 12,
                                        'md' => 6,
                                    ]),

                                TextInput::make('stt_sender_name')
                                    ->label('Nama Pengirim')
                                    ->required()
                                    ->maxLength(150)
                                    ->helperText('Nama pihak pengirim barang.')
                                    ->columnSpan([
                                        'default' => 12,
                                        'md' => 6,
                                    ]),

                                TextInput::make('stt_sender_phone')
                                    ->label('Telepon Pengirim')
                                    ->required()
                                    ->maxLength(30)
                                    ->helperText('Nomor telepon pengirim, format angka aktif.')
                                    ->columnSpan([
                                        'default' => 12,
                                        'md' => 6,
                                    ]),

                                Textarea::make('stt_sender_address')
                                    ->label('Alamat Pengirim')
                                    ->required()
                                    ->rows(2)
                                    ->helperText('Alamat lengkap pengirim untuk pickup/verifikasi kurir.')
                                    ->columnSpanFull(),

                                TextInput::make('stt_recipient_name')
                                    ->label('Nama Penerima')
                                    ->required()
                                    ->maxLength(150)
                                    ->helperText('Nama penerima pada alamat tujuan.')
                                    ->columnSpan([
                                        'default' => 12,
                                        'md' => 6,
                                    ]),

                                TextInput::make('stt_recipient_phone')
                                    ->label('Telepon Penerima')
                                    ->required()
                                    ->maxLength(30)
                                    ->helperText('Nomor telepon penerima yang bisa dihubungi.')
                                    ->columnSpan([
                                        'default' => 12,
                                        'md' => 6,
                                    ]),

                                Textarea::make('stt_recipient_address')
                                    ->label('Alamat Penerima')
                                    ->required()
                                    ->rows(2)
                                    ->helperText('Alamat lengkap penerima sesuai alamat pengiriman order.')
                                    ->columnSpanFull(),

                                TextInput::make('stt_piece_length')
                                    ->label('Panjang Paket (cm)')
                                    ->numeric()
                                    ->minValue(1)
                                    ->required()
                                    ->helperText('Panjang paket per piece dalam sentimeter.')
                                    ->columnSpan([
                                        'default' => 12,
                                        'md' => 3,
                                    ]),

                                TextInput::make('stt_piece_width')
                                    ->label('Lebar Paket (cm)')
                                    ->numeric()
                                    ->minValue(1)
                                    ->required()
                                    ->helperText('Lebar paket per piece dalam sentimeter.')
                                    ->columnSpan([
                                        'default' => 12,
                                        'md' => 3,
                                    ]),

                                TextInput::make('stt_piece_height')
                                    ->label('Tinggi Paket (cm)')
                                    ->numeric()
                                    ->minValue(1)
                                    ->required()
                                    ->helperText('Tinggi paket per piece dalam sentimeter.')
                                    ->columnSpan([
                                        'default' => 12,
                                        'md' => 3,
                                    ]),

                                TextInput::make('stt_piece_gross_weight')
                                    ->label('Berat Kotor (kg)')
                                    ->numeric()
                                    ->minValue(0.1)
                                    ->required()
                                    ->helperText('Berat kotor paket per piece dalam kilogram.')
                                    ->columnSpan([
                                        'default' => 12,
                                        'md' => 3,
                                    ]),

                                TextInput::make('stt_piece_per_pack')
                                    ->label('Piece per Pack')
                                    ->numeric()
                                    ->minValue(0)
                                    ->default(0)
                                    ->required()
                                    ->helperText('Jumlah piece dalam satu pack jika menggunakan pack.')
                                    ->columnSpan([
                                        'default' => 12,
                                        'md' => 4,
                                    ]),

                                TextInput::make('stt_next_commodity')
                                    ->label('Next Commodity (Opsional)')
                                    ->default('')
                                    ->maxLength(100)
                                    ->helperText('Isi jika ada komoditi lanjutan dari paket utama.')
                                    ->columnSpan([
                                        'default' => 12,
                                        'md' => 4,
                                    ]),

                                Toggle::make('stt_is_cod')
                                    ->label('COD')
                                    ->default(false)
                                    ->live()
                                    ->helperText('Aktifkan jika paket menggunakan skema pembayaran COD.')
                                    ->columnSpan([
                                        'default' => 12,
                                        'md' => 4,
                                    ]),

                                TextInput::make('stt_cod_amount')
                                    ->label('Nominal COD')
                                    ->numeric()
                                    ->minValue(0)
                                    ->default(0)
                                    ->required(fn (callable $get): bool => (bool) $get('stt_is_cod'))
                                    ->visible(fn (callable $get): bool => (bool) $get('stt_is_cod'))
                                    ->prefix('IDR')
                                    ->helperText('Nominal yang harus ditagihkan saat COD aktif.')
                                    ->columnSpan([
                                        'default' => 12,
                                        'md' => 6,
                                    ]),

                                Toggle::make('stt_is_woodpacking')
                                    ->label('Woodpacking')
                                    ->default(false)
                                    ->helperText('Aktifkan jika paket menggunakan kemasan kayu.')
                                    ->columnSpan([
                                        'default' => 12,
                                        'md' => 6,
                                    ]),
                            ])
                            ->columnSpanFull(),
                    ])
                    ->fillForm(fn (Order $record): array => self::shippingBookingDefaults($record))
                    ->action(function (Order $record, array $data): void {
                        try {
                            $bookingPayload = self::buildLionBookingPayload($data);
                            $bookingResult = app(LionParcelService::class)->createBooking($bookingPayload);

                            if (! (bool) ($bookingResult['success'] ?? false)) {
                                throw new \RuntimeException((string) ($bookingResult['message'] ?? 'Booking Lion Parcel gagal.'));
                            }

                            $shipmentStatus = (string) ($data['status'] ?? 'shipped');
                            $trackingNumber = filled($bookingResult['tracking_no'] ?? null)
                                ? (string) $bookingResult['tracking_no']
                                : (filled($data['tracking_no'] ?? null) ? (string) $data['tracking_no'] : null);

                            DB::transaction(function () use ($record, $data, $shipmentStatus, $trackingNumber): void {
                                $lockedOrder = Order::query()
                                    ->with('items:id,order_id,qty')
                                    ->whereKey($record->id)
                                    ->lockForUpdate()
                                    ->first();

                                if (! $lockedOrder) {
                                    throw new \RuntimeException('Pesanan tidak ditemukan.');
                                }

                                if ($lockedOrder->shipments()->exists()) {
                                    throw new \RuntimeException('Pesanan ini sudah memiliki data pengiriman.');
                                }

                                $shipment = $lockedOrder->shipments()->create([
                                    'courier_id' => (string) $data['courier_id'],
                                    'tracking_no' => $trackingNumber,
                                    'status' => $shipmentStatus,
                                    'shipped_at' => filled($data['shipped_at'] ?? null)
                                        ? $data['shipped_at']
                                        : (in_array($shipmentStatus, ['shipped', 'delivered'], true) ? now() : null),
                                    'delivered_at' => (string) $shipmentStatus === 'delivered'
                                        ? (filled($data['delivered_at'] ?? null) ? $data['delivered_at'] : now())
                                        : null,
                                    'shipping_fee' => (float) ($data['shipping_fee'] ?? 0),
                                ]);

                                foreach ($lockedOrder->items as $orderItem) {
                                    $shipment->items()->create([
                                        'order_item_id' => $orderItem->id,
                                        'qty' => (int) $orderItem->qty,
                                    ]);
                                }

                                if (in_array($shipmentStatus, ['shipped', 'delivered'], true)) {
                                    $lockedOrder->status = $shipmentStatus;
                                    if ($lockedOrder->processed_at === null) {
                                        $lockedOrder->processed_at = now();
                                    }
                                    $lockedOrder->save();
                                }
                            });

                            Notification::make()
                                ->title('Pengiriman berhasil diproses')
                                ->body(
                                    filled($trackingNumber)
                                        ? "Booking Lion Parcel sukses. Nomor resi {$trackingNumber}."
                                        : 'Booking Lion Parcel sukses dan data pengiriman tersimpan.'
                                )
                                ->success()
                                ->send();
                        } catch (\Throwable $exception) {
                            Notification::make()
                                ->title('Gagal memproses pengiriman')
                                ->body($exception->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->visible(fn (Order $record): bool => $record->status === 'PAID' || $record->status === 'processing' && (int) ($record->shipments_count ?? 0) === 0),
            ]),
        ];
    }

    private static function shippingDestination(Order $record): string
    {
        return collect([
            $record->shippingAddress?->recipient_name,
            $record->shippingAddress?->recipient_phone,
            $record->shippingAddress?->address_line1,
            $record->shippingAddress?->address_line2,
            $record->shippingAddress?->city_label,
            $record->shippingAddress?->province_label,
            $record->shippingAddress?->postal_code,
        ])
            ->filter(fn (mixed $value): bool => filled($value))
            ->implode(' • ');
    }

    private static function shippingBookingDefaults(Order $record): array
    {
        $record->loadMissing([
            'customer:id,name,phone',
            'shippingAddress:id,recipient_name,recipient_phone,address_line1,address_line2,district,district_lion,city_label,province_label,postal_code',
            'items:id,order_id,qty,weight_gram,length_mm,width_mm,height_mm',
        ]);

        $settings = Setting::query()
            ->whereIn('key', [
                'store.name',
                'store.phone',
                'shipping.origin_district_label',
                'shipping.origin_address',
                'address.line1',
                'address.line2',
                'address.city',
                'address.province',
                'address.postal_code',
            ])
            ->pluck('value', 'key')
            ->all();

        $originLabel = trim((string) ($settings['shipping.origin_district_label'] ?? config('services.lion_parcel.origin', '')));
        $senderAddress = self::resolveSenderAddress($settings, $originLabel);
        $senderPhone = self::normalizePhoneNumber((string) ($settings['store.phone'] ?? ''));
        $recipientPhoneRaw = (string) ($record->shippingAddress?->recipient_phone ?? $record->customer?->phone ?? '');
        $recipientPhone = self::normalizePhoneNumber($recipientPhoneRaw);

        return [
            'order_no' => (string) $record->order_no,
            'customer_name' => (string) ($record->customer?->name ?? '-'),
            'shipping_destination' => self::shippingDestination($record),
            'status' => 'shipped',
            'shipping_fee' => (float) ($record->shipping_amount ?? 0),
            'courier_id' => 'lion',
            'tracking_no' => null,
            'shipped_at' => now(),
            'delivered_at' => null,
            'stt_no_ref_external' => (string) $record->order_no,
            'stt_no' => '',
            'stt_tax_number' => '',
            'stt_goods_estimate_price' => max(1, (int) round((float) ($record->grand_total ?? 1))),
            'stt_goods_status' => '',
            'stt_origin' => $originLabel,
            'stt_destination' => self::shippingDestinationLion($record),
            'stt_sender_name' => (string) ($settings['store.name'] ?? config('app.name')),
            'stt_sender_phone' => $senderPhone !== '' ? $senderPhone : (string) ($settings['store.phone'] ?? ''),
            'stt_sender_address' => $senderAddress,
            'stt_recipient_name' => (string) ($record->shippingAddress?->recipient_name ?? $record->customer?->name ?? ''),
            'stt_recipient_address' => self::shippingRecipientAddress($record),
            'stt_recipient_phone' => $recipientPhone !== '' ? $recipientPhone : $recipientPhoneRaw,
            'stt_insurance_type' => 'free',
            'stt_product_type' => 'regpack',
            'stt_commodity_code' => (string) config('services.lion_parcel.commodity', 'ABR036'),
            'stt_is_cod' => false,
            'stt_is_woodpacking' => false,
            'stt_piece_length' => self::orderDimensionCm($record, 'length_mm'),
            'stt_piece_width' => self::orderDimensionCm($record, 'width_mm'),
            'stt_piece_height' => self::orderDimensionCm($record, 'height_mm'),
            'stt_piece_gross_weight' => self::orderTotalWeightKg($record),
            'stt_piece_per_pack' => 0,
            'stt_next_commodity' => '',
            'stt_cod_amount' => 0,
        ];
    }

    private static function buildLionBookingPayload(array $data): array
    {
        $isCod = self::toBoolean($data['stt_is_cod'] ?? false);
        $codAmount = $isCod ? (int) round((float) ($data['stt_cod_amount'] ?? 0)) : 0;

        return [
            'stt_no' => (string) ($data['stt_no'] ?? ''),
            'stt_no_ref_external' => (string) ($data['stt_no_ref_external'] ?? ''),
            'stt_tax_number' => (string) ($data['stt_tax_number'] ?? ''),
            'stt_goods_estimate_price' => max(1, (int) round((float) ($data['stt_goods_estimate_price'] ?? 1))),
            'stt_goods_status' => (string) ($data['stt_goods_status'] ?? ''),
            'stt_origin' => (string) ($data['stt_origin'] ?? ''),
            'stt_destination' => (string) ($data['stt_destination'] ?? ''),
            'stt_sender_name' => (string) ($data['stt_sender_name'] ?? ''),
            'stt_sender_phone' => self::normalizePhoneNumber((string) ($data['stt_sender_phone'] ?? '')),
            'stt_sender_address' => (string) ($data['stt_sender_address'] ?? ''),
            'stt_recipient_name' => (string) ($data['stt_recipient_name'] ?? ''),
            'stt_recipient_address' => (string) ($data['stt_recipient_address'] ?? ''),
            'stt_recipient_phone' => self::normalizePhoneNumber((string) ($data['stt_recipient_phone'] ?? '')),
            'stt_insurance_type' => (string) ($data['stt_insurance_type'] ?? 'free'),
            'stt_product_type' => (string) ($data['stt_product_type'] ?? 'regpack'),
            'stt_commodity_code' => (string) ($data['stt_commodity_code'] ?? config('services.lion_parcel.commodity', 'ABR036')),
            'stt_is_cod' => $isCod,
            'stt_is_woodpacking' => self::toBoolean($data['stt_is_woodpacking'] ?? false),
            'stt_pieces' => [[
                'stt_piece_length' => max(1, (int) round((float) ($data['stt_piece_length'] ?? 1))),
                'stt_piece_width' => max(1, (int) round((float) ($data['stt_piece_width'] ?? 1))),
                'stt_piece_height' => max(1, (int) round((float) ($data['stt_piece_height'] ?? 1))),
                'stt_piece_gross_weight' => max(0.1, (float) ($data['stt_piece_gross_weight'] ?? 1)),
            ]],
            'stt_piece_per_pack' => max(0, (int) round((float) ($data['stt_piece_per_pack'] ?? 0))),
            'stt_next_commodity' => (string) ($data['stt_next_commodity'] ?? ''),
            'stt_cod_amount' => $codAmount,
        ];
    }

    private static function resolveSenderAddress(array $settings, string $originLabel): string
    {
        $originAddress = trim((string) ($settings['shipping.origin_address'] ?? ''));

        if ($originAddress !== '') {
            return $originAddress;
        }

        $companyAddress = collect([
            $settings['address.line1'] ?? null,
            $settings['address.line2'] ?? null,
            $settings['address.city'] ?? null,
            $settings['address.province'] ?? null,
            $settings['address.postal_code'] ?? null,
        ])
            ->map(fn (mixed $value): string => trim((string) $value))
            ->filter(fn (string $value): bool => $value !== '')
            ->implode(', ');

        return $companyAddress !== '' ? $companyAddress : $originLabel;
    }

    private static function shippingDestinationLion(Order $record): string
    {
        $record->loadMissing('shippingAddress:id,district,district_lion,city_label');
        $address = $record->shippingAddress;

        if (! $address) {
            return '';
        }

        $districtLion = trim((string) ($address->district_lion ?? ''));

        if ($districtLion !== '') {
            return $districtLion;
        }

        return collect([
            $address->district,
            $address->city_label,
        ])
            ->map(fn (mixed $value): string => trim((string) $value))
            ->filter(fn (string $value): bool => $value !== '')
            ->implode(', ');
    }

    private static function shippingRecipientAddress(Order $record): string
    {
        $record->loadMissing('shippingAddress:id,address_line1,address_line2,district,city_label,province_label,postal_code');
        $address = $record->shippingAddress;

        if (! $address) {
            return '';
        }

        return collect([
            $address->address_line1,
            $address->address_line2,
            $address->district,
            $address->city_label,
            $address->province_label,
            $address->postal_code,
        ])
            ->map(fn (mixed $value): string => trim((string) $value))
            ->filter(fn (string $value): bool => $value !== '')
            ->implode(', ');
    }

    private static function orderDimensionCm(Order $record, string $dimensionField): int
    {
        $record->loadMissing('items:id,order_id,length_mm,width_mm,height_mm');

        $maxMillimeter = (int) $record->items->max(function (mixed $item) use ($dimensionField): int {
            $value = (int) ($item->{$dimensionField} ?? 0);

            return $value > 0 ? $value : 100;
        });

        return max(10, (int) ceil($maxMillimeter / 10));
    }

    private static function orderTotalWeightKg(Order $record): float
    {
        $record->loadMissing('items:id,order_id,qty,weight_gram');

        $totalWeightGram = (int) $record->items->sum(function (mixed $item): int {
            $quantity = (int) ($item->qty ?? 1);
            $weightPerItem = (int) ($item->weight_gram ?? 200);

            return max(1, $quantity) * max(1, $weightPerItem);
        });

        return max(0.1, round($totalWeightGram / 1000, 2));
    }

    private static function normalizePhoneNumber(string $phone): string
    {
        $digits = preg_replace('/[^0-9]/', '', $phone) ?? '';

        if ($digits === '') {
            return '';
        }

        if (str_starts_with($digits, '0')) {
            $digits = '62'.substr($digits, 1);
        } elseif (! str_starts_with($digits, '62')) {
            $digits = '62'.$digits;
        }

        return $digits;
    }

    private static function toBoolean(mixed $state): bool
    {
        if (is_bool($state)) {
            return $state;
        }

        if (is_int($state)) {
            return $state === 1;
        }

        if (is_string($state)) {
            return in_array(strtolower(trim($state)), ['1', 'true', 'yes', 'on'], true);
        }

        return false;
    }

    private static function toolbarActions(): array
    {
        return [
            BulkActionGroup::make([
                DeleteBulkAction::make(),
            ]),
        ];
    }
}
