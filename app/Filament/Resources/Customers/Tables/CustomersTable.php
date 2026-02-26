<?php

namespace App\Filament\Resources\Customers\Tables;

use App\Models\Customer;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class CustomersTable
{
    private static function hiddenByDefault(Column $column): Column
    {
        return $column->toggleable(isToggledHiddenByDefault: true);
    }

    public static function configure(Table $table): Table
    {
        return $table
            ->columns(self::columns())
            ->filters(self::filters(), layout: FiltersLayout::AboveContentCollapsible)
            ->filtersFormColumns(6)
            ->filtersFormSchema(self::filtersFormSchema())
            ->recordActions(self::recordActions())
            ->toolbarActions(self::toolbarActions());
    }

    /**
     * @return array<int, Column>
     */
    private static function columns(): array
    {
        return [
            TextColumn::make('username')
                ->label('Username')
                ->searchable()
                ->sortable(),
            TextColumn::make('ewallet_id')
                ->label('Ewallet ID')
                ->searchable()
                ->placeholder('-')
                ->sortable(),
            TextColumn::make('name')
                ->label('Nama')
                ->searchable()
                ->sortable(),
            TextColumn::make('package.name')
                ->label('Paket Member')
                ->searchable()
                ->placeholder('-'),
            TextColumn::make('level')
                ->label('Peringkat')
                ->badge()
                ->sortable()
                ->placeholder('-'),
            TextColumn::make('phone')
                ->label('Telepon')
                ->searchable()
                ->placeholder('-'),
            TextColumn::make('ewallet_saldo')
                ->label('Saldo')
                ->sortable()
                ->alignEnd()
                ->formatStateUsing(fn (mixed $state): string => 'Rp ' . number_format((float) $state, 0, ',', '.')),
            TextColumn::make('sponsor.name')
                ->label('Sponsor')
                ->searchable()
                ->placeholder('-'),
            TextColumn::make('upline.name')
                ->label('Upline')
                ->searchable()
                ->placeholder('-'),
            TextColumn::make('position')
                ->label('Posisi')
                ->badge()
                ->formatStateUsing(fn (?string $state): string => match ($state) {
                    'left' => 'Kiri',
                    'right' => 'Kanan',
                    default => '-',
                }),
            TextColumn::make('status')
                ->label('Status')
                ->badge()
                ->sortable()
                ->formatStateUsing(fn (mixed $state): string => match ((int) $state) {
                    1 => 'Prospek',
                    2 => 'Pasif',
                    3 => 'Aktif',
                    default => (string) $state,
                })
                ->color(fn (mixed $state): string => match ((int) $state) {
                    1 => 'gray',
                    2 => 'warning',
                    3 => 'success',
                    default => 'gray',
                }),
            self::hiddenByDefault(
                TextColumn::make('ref_code')
                    ->label('Ref Code')
                    ->searchable()
            ),
            self::hiddenByDefault(
                TextColumn::make('nik')
                    ->label('NIK')
                    ->searchable()
            ),
            self::hiddenByDefault(
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
            ),
            self::hiddenByDefault(
                TextColumn::make('gender')
                    ->label('Gender')
                    ->badge()
            ),
            self::hiddenByDefault(
                TextColumn::make('address')
                    ->label('Alamat')
                    ->searchable()
            ),
            self::hiddenByDefault(
                TextColumn::make('city_id')
                    ->label('City ID')
                    ->numeric()
                    ->sortable()
            ),
            self::hiddenByDefault(
                TextColumn::make('province_id')
                    ->label('Province ID')
                    ->numeric()
                    ->sortable()
            ),
            self::hiddenByDefault(
                TextColumn::make('email_verified_at')
                    ->label('Email Verified')
                    ->dateTime()
                    ->sortable()
            ),
            self::hiddenByDefault(
                TextColumn::make('bonus_pending')
                    ->label('Bonus Pending')
                    ->numeric()
                    ->sortable()
            ),
            self::hiddenByDefault(
                TextColumn::make('bonus_processed')
                    ->label('Bonus Processed')
                    ->numeric()
                    ->sortable()
            ),
            self::hiddenByDefault(
                TextColumn::make('bank_name')
                    ->label('Bank')
                    ->searchable()
            ),
            self::hiddenByDefault(
                TextColumn::make('bank_account')
                    ->label('No Rekening')
                    ->searchable()
            ),
            self::hiddenByDefault(
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
            ),
            self::hiddenByDefault(
                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime()
                    ->sortable()
            ),
        ];
    }

    /**
     * @return array<int, SelectFilter|TernaryFilter|Filter>
     */
    private static function filters(): array
    {
        return [
            SelectFilter::make('status')
                ->label('Status')
                ->placeholder('Semua')
                ->options([
                    1 => 'Prospek',
                    2 => 'Pasif',
                    3 => 'Aktif',
                ]),
            SelectFilter::make('package_id')
                ->label('Paket')
                ->placeholder('Semua')
                ->relationship('package', 'name')
                ->searchable()
                ->preload(),
            SelectFilter::make('level')
                ->label('Level')
                ->placeholder('Semua')
                ->options([
                    'Associate' => 'Associate',
                    'Senior Associate' => 'Senior Associate',
                    'Executive' => 'Executive',
                    'Director' => 'Director',
                ]),
            SelectFilter::make('gender')
                ->label('Jenis Kelamin')
                ->placeholder('Semua')
                ->options([
                    'male' => 'Laki-laki',
                    'female' => 'Perempuan',
                    'L' => 'L',
                    'P' => 'P',
                ]),
            SelectFilter::make('position')
                ->label('Posisi Binary')
                ->placeholder('Semua')
                ->options([
                    'left' => 'Kiri',
                    'right' => 'Kanan',
                ]),
            TernaryFilter::make('is_stockist')
                ->label('Stockist')
                ->placeholder('Semua')
                ->trueLabel('Ya')
                ->falseLabel('Tidak'),
            TernaryFilter::make('network_generated')
                ->label('Network Generated')
                ->placeholder('Semua')
                ->trueLabel('Ya')
                ->falseLabel('Tidak'),
            TernaryFilter::make('email_verified_at')
                ->label('Verifikasi Email')
                ->placeholder('Semua')
                ->trueLabel('Sudah Terverifikasi')
                ->falseLabel('Belum Terverifikasi')
                ->queries(
                    true: fn (Builder $query) => $query->whereNotNull('email_verified_at'),
                    false: fn (Builder $query) => $query->whereNull('email_verified_at'),
                    blank: fn (Builder $query) => $query,
                ),
            self::createdAtFilter(),
        ];
    }

    private static function createdAtFilter(): Filter
    {
        return Filter::make('created_at')
            ->label('Tanggal Bergabung')
            ->schema([
                Grid::make(2)->schema([
                    DatePicker::make('from')->label('Dari'),
                    DatePicker::make('until')->label('Sampai'),
                ]),
            ])
            ->query(fn (Builder $query, array $data): Builder => $query
                ->when($data['from'] ?? null, fn (Builder $query) => $query->whereDate('created_at', '>=', $data['from']))
                ->when($data['until'] ?? null, fn (Builder $query) => $query->whereDate('created_at', '<=', $data['until']))
            )
            ->indicateUsing(function (array $data): array {
                $indicators = [];

                if ($data['from'] ?? null) {
                    $indicators[] = Indicator::make('Bergabung dari ' . Carbon::parse($data['from'])->toFormattedDateString())
                        ->removeField('from');
                }

                if ($data['until'] ?? null) {
                    $indicators[] = Indicator::make('Bergabung sampai ' . Carbon::parse($data['until'])->toFormattedDateString())
                        ->removeField('until');
                }

                return $indicators;
            });
    }

    private static function filtersFormSchema(): \Closure
    {
        return fn (array $filters): array => [
            Section::make('Klasifikasi')
                ->description('Filter utama untuk status, paket, level, dan identitas umum.')
                ->schema([
                    $filters['status'],
                    $filters['package_id']->columnSpan(2),
                    $filters['level'],
                    $filters['gender'],
                    $filters['position'],
                ])
                ->columns(6)
                ->columnSpanFull(),
            Section::make('Flags')
                ->description('Filter boolean / ternary untuk kondisi khusus.')
                ->schema([
                    $filters['is_stockist']->columnSpan(2),
                    $filters['network_generated']->columnSpan(2),
                    $filters['email_verified_at']->columnSpan(2),
                ])
                ->columns(6)
                ->columnSpanFull(),
            Section::make('Periode')
                ->description('Filter tanggal bergabung (opsional).')
                ->schema([
                    $filters['created_at']->columnSpanFull(),
                ])
                ->columnSpanFull(),
        ];
    }

    /**
     * @return array<int, Action|ActionGroup>
     */
    private static function recordActions(): array
    {
        return [
            self::impersonateAction(),
            self::injectEwallet(),
            ActionGroup::make([
                ViewAction::make(),
                EditAction::make(),
            ]),
        ];
    }

    private static function impersonateAction(): Action
    {
        return Action::make('impersonate')
            ->tooltip('Impersonate')
            ->icon('bi-rocket-takeoff')
            ->requiresConfirmation()
            ->action(function (Customer $record): void {
                $adminUser = Auth::guard('web')->user();

                if (Auth::guard('customer')->check()) {
                    Auth::guard('customer')->logout();
                }

                Auth::guard('customer')->login($record);
                session()->regenerate();
                session()->put('impersonation', [
                    'is_active' => true,
                    'admin_id' => $adminUser?->id,
                    'admin_name' => $adminUser?->name,
                    'customer_id' => $record->id,
                ]);
            })
            ->successNotificationTitle('Berhasil masuk sebagai customer')
            ->successRedirectUrl(route('dashboard'));
    }

    private static function injectEwallet(): Action
    {
        return Action::make('inject_ewallet')
            ->label('Inject E-Wallet')
            ->tooltip('Menambahkan saldo secara manual ke akun member')
            ->icon('heroicon-o-wallet')
            ->form([
                TextInput::make('amount')
                    ->label('Jumlah Saldo')
                    ->numeric()
                    ->prefix('IDR')
                    ->required()
                    ->minValue(10000)
                    ->helperText('Masukkan nominal angka tanpa titik/koma.'),

                Textarea::make('note')
                    ->label('Keterangan / Alasan')
                    ->placeholder('Contoh: Koreksi bonus atau kompensasi sistem.')
                    ->required()
                    ->maxLength(500)
                    ->rows(3),
            ])
            ->action(function (Customer $record, array $data): void {
                $amount = (float) ($data['amount'] ?? 0);
                $note = trim((string) ($data['note'] ?? ''));

                try {
                    $isUpdated = $record->addBalance(
                        $amount,
                        $note !== '' ? $note : 'Top up ewallet'
                    );

                    if (! $isUpdated) {
                        throw new \RuntimeException('Saldo gagal diperbarui.');
                    }

                    $record->refresh();

                    Notification::make()
                        ->success()
                        ->title('Saldo Berhasil Diperbarui')
                        ->body('Berhasil melakukan inject saldo sebesar Rp ' . number_format($amount, 0, ',', '.') . '.')
                        ->send();
                } catch (\Throwable $exception) {
                    Notification::make()
                        ->danger()
                        ->title('Terjadi Kesalahan')
                        ->body('Gagal melakukan inject saldo: ' . $exception->getMessage())
                        ->send();
                }
            })
            ->requiresConfirmation()
            ->modalIcon('heroicon-o-banknotes')
            ->modalHeading('Konfirmasi Inject Saldo')
            ->modalDescription('Pastikan nominal dan alasan sudah sesuai. Tindakan ini akan tercatat di log sistem.');
    }

    /**
     * @return array<int, BulkActionGroup>
     */
    private static function toolbarActions(): array
    {
        return [
            BulkActionGroup::make([
                DeleteBulkAction::make(),
            ]),
        ];
    }
}
