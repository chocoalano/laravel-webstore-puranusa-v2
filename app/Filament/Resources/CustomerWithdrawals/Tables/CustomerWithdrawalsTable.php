<?php

namespace App\Filament\Resources\CustomerWithdrawals\Tables;

use App\Jobs\SendWithdrawalApprovedWhatsAppJob;
use App\Models\Customer;
use App\Models\CustomerWalletTransaction;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class CustomerWithdrawalsTable
{
    private const WITHDRAWAL_ADMIN_FEE = 6500.0;

    public static function configure(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('transaction_ref')
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(fn (Builder $query): Builder => $query
                ->where('type', 'withdrawal')
                ->with([
                    'customer:id,name,username,ref_code,bank_name,bank_account',
                ]))
            ->columns([
                TextColumn::make('customer.username')
                    ->label('Username Customer')
                    ->placeholder('-')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('customer.name')
                    ->label('Nama Customer')
                    ->placeholder('-')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('amount')
                    ->label('Nominal Penarikan')
                    ->money('IDR')
                    ->sortable()
                    ->summarize(
                        Sum::make()
                            ->label('Total Penarikan')
                            ->money('IDR')
                    ),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->sortable()
                    ->formatStateUsing(fn (string $state): string => self::statusOptions()[$state] ?? $state)
                    ->color(fn (string $state): string => self::statusColors()[$state] ?? 'gray'),

                TextColumn::make('customer.bank_name')
                    ->label('Bank')
                    ->placeholder('-')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('customer.bank_account')
                    ->label('No. Rekening')
                    ->placeholder('-')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('payment_method')
                    ->label('Metode Bayar')
                    ->placeholder('-')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('transaction_ref')
                    ->label('Ref Transaksi')
                    ->placeholder('-')
                    ->searchable()
                    ->copyable()
                    ->toggleable(),

                IconColumn::make('is_system')
                    ->label('Sistem')
                    ->boolean()
                    ->toggleable(),

                TextColumn::make('completed_at')
                    ->label('Selesai')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('customer_id')
                    ->label('Customer')
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->preload()
                    ->placeholder('Semua customer'),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options(self::statusOptions())
                    ->placeholder('Semua status'),

                TernaryFilter::make('is_system')
                    ->label('Sumber Transaksi')
                    ->placeholder('Semua')
                    ->trueLabel('Sistem Otomatis')
                    ->falseLabel('Manual'),

                Filter::make('amount_range')
                    ->label('Rentang Nominal')
                    ->schema([
                        TextInput::make('min')
                            ->label('Minimum (Rp)')
                            ->numeric()
                            ->minValue(0)
                            ->prefix('Rp')
                            ->placeholder('0'),
                        TextInput::make('max')
                            ->label('Maksimum (Rp)')
                            ->numeric()
                            ->minValue(0)
                            ->prefix('Rp')
                            ->placeholder('∞'),
                    ])
                    ->columns(2)
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when(filled($data['min'] ?? null), fn (Builder $builder): Builder => $builder->where('amount', '>=', $data['min']))
                        ->when(filled($data['max'] ?? null), fn (Builder $builder): Builder => $builder->where('amount', '<=', $data['max']))
                    )
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if (filled($data['min'] ?? null)) {
                            $indicators[] = Indicator::make('Nominal ≥ Rp'.number_format((float) $data['min'], 0, ',', '.'))->removeField('min');
                        }

                        if (filled($data['max'] ?? null)) {
                            $indicators[] = Indicator::make('Nominal ≤ Rp'.number_format((float) $data['max'], 0, ',', '.'))->removeField('max');
                        }

                        return $indicators;
                    }),

                self::betweenDateFilter('created_between', 'Periode Dibuat', 'created_at'),
            ], layout: FiltersLayout::AboveContentCollapsible)
            ->filtersFormColumns(12)
            ->filtersFormSchema(fn (array $filters): array => [
                $filters['customer_id']->columnSpan(4),
                $filters['status']->columnSpan(2),
                $filters['is_system']->columnSpan(2),
                $filters['amount_range']->columnSpan(4),
                $filters['created_between']->columnSpan(4),
            ])
            ->recordActions([
                Action::make('approve_withdrawal')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (CustomerWalletTransaction $record): bool => self::normalizeStatus($record->status) === 'pending')
                    ->requiresConfirmation()
                    ->modalHeading('Setujui Withdrawal')
                    ->modalDescription('Withdrawal akan ditandai completed, saldo customer disesuaikan, dan notifikasi WhatsApp dikirim.')
                    ->modalSubmitActionLabel('Ya, Setujui')
                    ->action(function (CustomerWalletTransaction $record): void {
                        try {
                            $approvedTransactionId = self::approvePendingWithdrawal((int) $record->id);
                        } catch (\Throwable $exception) {
                            Notification::make()
                                ->danger()
                                ->title('Gagal Menyetujui Withdrawal')
                                ->body($exception->getMessage())
                                ->send();

                            return;
                        }

                        try {
                            dispatch_sync(new SendWithdrawalApprovedWhatsAppJob($approvedTransactionId));

                            Notification::make()
                                ->success()
                                ->title('Withdrawal Disetujui')
                                ->body('Withdrawal berhasil disetujui, saldo diperbarui, dan notifikasi WhatsApp berhasil dikirim.')
                                ->send();
                        } catch (\Throwable $exception) {
                            Notification::make()
                                ->warning()
                                ->title('Withdrawal Disetujui, Notifikasi WA Gagal')
                                ->body('Status dan saldo sudah diperbarui, namun notifikasi WhatsApp gagal terkirim: '.$exception->getMessage())
                                ->send();
                        }
                    }),
                ViewAction::make(),
            ])
            ->toolbarActions([]);
    }

    private static function approvePendingWithdrawal(int $transactionId): int
    {
        return DB::transaction(function () use ($transactionId): int {
            $transaction = CustomerWalletTransaction::query()
                ->whereKey($transactionId)
                ->lockForUpdate()
                ->first();

            if (! $transaction) {
                throw new \RuntimeException('Data withdrawal tidak ditemukan.');
            }

            if (self::normalizeType($transaction->type) !== 'withdrawal') {
                throw new \RuntimeException('Transaksi ini bukan withdrawal.');
            }

            if (self::normalizeStatus($transaction->status) !== 'pending') {
                throw new \RuntimeException('Withdrawal ini sudah diproses sebelumnya.');
            }

            /** @var Customer|null $customer */
            $customer = Customer::query()
                ->whereKey($transaction->customer_id)
                ->lockForUpdate()
                ->first();

            if (! $customer) {
                throw new \RuntimeException('Customer untuk transaksi withdrawal tidak ditemukan.');
            }

            // $currentBalance = (float) ($customer->ewallet_saldo ?? 0);
            $grossAmount = max(0.0, (float) ($transaction->amount ?? 0));
            $recordedBalanceBefore = (float) ($transaction->balance_before ?? 0);
            $recordedBalanceAfter = (float) ($transaction->balance_after ?? 0);
            $alreadyDeductedGross = max(0.0, $recordedBalanceBefore - $recordedBalanceAfter);
            $missingGrossDeduction = max(0.0, $grossAmount - $alreadyDeductedGross);
            $additionalDeduction = $missingGrossDeduction + self::WITHDRAWAL_ADMIN_FEE;

            // if ($currentBalance < $additionalDeduction) {
            //     throw new \RuntimeException('Saldo wallet customer tidak mencukupi untuk proses approval withdrawal.');
            // }

            if ($additionalDeduction > 0) {
                $customer->decrement('ewallet_saldo', $additionalDeduction);
                $customer->refresh();
            }

            $transaction->forceFill([
                'status' => 'completed',
                'completed_at' => now(),
                'balance_after' => (float) ($customer->ewallet_saldo ?? 0),
                'notes' => self::appendApprovalNotes(
                    $transaction->notes,
                    self::WITHDRAWAL_ADMIN_FEE,
                    $missingGrossDeduction
                ),
            ])->save();

            return (int) $transaction->id;
        });
    }

    private static function betweenDateFilter(string $name, string $label, string $column): Filter
    {
        return Filter::make($name)
            ->label($label)
            ->schema([
                DateTimePicker::make('from')
                    ->label('Dari Tanggal')
                    ->seconds(false)
                    ->placeholder('Awal periode'),
                DateTimePicker::make('until')
                    ->label('Sampai Tanggal')
                    ->seconds(false)
                    ->placeholder('Akhir periode'),
            ])
            ->columns(2)
            ->query(fn (Builder $query, array $data): Builder => $query
                ->when(filled($data['from'] ?? null), fn (Builder $builder): Builder => $builder->where($column, '>=', $data['from']))
                ->when(filled($data['until'] ?? null), fn (Builder $builder): Builder => $builder->where($column, '<=', $data['until']))
            )
            ->indicateUsing(function (array $data) use ($label): array {
                $indicators = [];

                if (filled($data['from'] ?? null)) {
                    $indicators[] = Indicator::make($label.' dari '.$data['from'])->removeField('from');
                }

                if (filled($data['until'] ?? null)) {
                    $indicators[] = Indicator::make($label.' sampai '.$data['until'])->removeField('until');
                }

                return $indicators;
            });
    }

    private static function statusOptions(): array
    {
        return [
            'pending' => 'Pending',
            'completed' => 'Completed',
            'failed' => 'Failed',
            'cancelled' => 'Cancelled',
        ];
    }

    private static function statusColors(): array
    {
        return [
            'pending' => 'warning',
            'completed' => 'success',
            'failed' => 'danger',
            'cancelled' => 'gray',
        ];
    }

    private static function appendApprovalNotes(?string $existingNotes, float $adminFee, float $missingGrossDeduction): string
    {
        $chunks = [];
        $normalized = trim((string) $existingNotes);

        if ($normalized !== '') {
            $chunks[] = $normalized;
        }

        $chunks[] = 'Biaya admin approval: '.self::formatIdr($adminFee);

        if ($missingGrossDeduction > 0) {
            $chunks[] = 'Potongan gross saat approve: '.self::formatIdr($missingGrossDeduction);
        }

        return implode(PHP_EOL, $chunks);
    }

    private static function formatIdr(float $amount): string
    {
        return 'Rp '.number_format((int) round($amount), 0, ',', '.');
    }

    private static function normalizeStatus(mixed $status): string
    {
        return strtolower(trim((string) $status));
    }

    private static function normalizeType(mixed $type): string
    {
        return strtolower(trim((string) $type));
    }
}
