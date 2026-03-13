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

                TextColumn::make('submission_admin_fee')
                    ->label('Biaya Admin')
                    ->state(fn (CustomerWalletTransaction $record): float => self::extractSubmissionAdminFee($record->notes))
                    ->money('IDR')
                    ->alignEnd()
                    ->toggleable(),

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
                    ->modalDescription('Pilih "Ya, Setujui" untuk menyelesaikan withdrawal, atau "Tidak, Tolak permintaan" untuk membatalkan dan mengembalikan saldo customer.')
                    ->modalSubmitActionLabel('Ya, Setujui')
                    ->modalCancelAction(false)
                    ->extraModalFooterActions(fn (Action $action): array => [
                        $action->makeModalSubmitAction('reject_withdrawal', arguments: ['decision' => 'reject'])
                            ->label('Tolak permintaan')
                            ->color('danger')
                            ->icon('heroicon-o-x-circle'),
                    ])
                    ->action(function (CustomerWalletTransaction $record, array $arguments): void {
                        if (($arguments['decision'] ?? null) === 'reject') {
                            try {
                                self::rejectPendingWithdrawal((int) $record->id);
                            } catch (\Throwable $exception) {
                                Notification::make()
                                    ->danger()
                                    ->title('Gagal Menolak Withdrawal')
                                    ->body($exception->getMessage())
                                    ->send();

                                return;
                            }

                            Notification::make()
                                ->warning()
                                ->title('Withdrawal Ditolak')
                                ->body('Permintaan withdrawal ditolak dan saldo customer dikembalikan.')
                                ->send();

                            return;
                        }

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
                                ->body('Withdrawal berhasil disetujui dan notifikasi WhatsApp berhasil dikirim.')
                                ->send();
                        } catch (\Throwable $exception) {
                            Notification::make()
                                ->warning()
                                ->title('Withdrawal Disetujui, Notifikasi WA Gagal')
                                ->body('Status withdrawal sudah diperbarui, namun notifikasi WhatsApp gagal terkirim: '.$exception->getMessage())
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

            $transaction->forceFill([
                'status' => 'completed',
                'completed_at' => now(),
                'balance_after' => (float) ($customer->ewallet_saldo ?? 0),
                'notes' => self::appendApprovalNotes($transaction->notes),
            ])->save();

            return (int) $transaction->id;
        });
    }

    private static function rejectPendingWithdrawal(int $transactionId): int
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

            $refundAmount = max(0.0, (float) ($transaction->amount ?? 0));

            if ($refundAmount > 0) {
                $customer->increment('ewallet_saldo', $refundAmount);
                $customer->refresh();
            }

            $transaction->forceFill([
                'status' => 'cancelled',
                'completed_at' => now(),
                'balance_after' => (float) ($customer->ewallet_saldo ?? 0),
                'notes' => self::appendRejectionNotes($transaction->notes, $refundAmount),
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

    private static function appendApprovalNotes(?string $existingNotes): string
    {
        $chunks = [];
        $normalized = trim((string) $existingNotes);

        if ($normalized !== '') {
            $chunks[] = $normalized;
        }

        $chunks[] = 'Approval selesai tanpa potongan saldo tambahan.';

        return implode(PHP_EOL, $chunks);
    }

    private static function extractSubmissionAdminFee(?string $notes): float
    {
        $normalized = trim((string) $notes);

        if ($normalized === '') {
            return 0.0;
        }

        if (! preg_match('/Biaya admin:\s*Rp\s*([0-9\.\,]+)/i', $normalized, $matches)) {
            return 0.0;
        }

        $rawAmount = trim((string) ($matches[1] ?? '0'));
        $normalizedAmount = str_replace(['.', ','], ['', '.'], $rawAmount);

        return max(0.0, (float) $normalizedAmount);
    }

    private static function appendRejectionNotes(?string $existingNotes, float $refundAmount): string
    {
        $chunks = [];
        $normalized = trim((string) $existingNotes);

        if ($normalized !== '') {
            $chunks[] = $normalized;
        }

        $chunks[] = 'Withdrawal ditolak. Saldo dikembalikan: '.self::formatIdr($refundAmount).'.';

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
