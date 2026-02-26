<?php

namespace App\Filament\Resources\CustomerWithdrawals\Tables;

use App\Jobs\SendWithdrawalApprovedWhatsAppJob;
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
                    'customer:id,name,ref_code,bank_name,bank_account',
                ]))
            ->columns([
                TextColumn::make('customer.name')
                    ->label('Customer')
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
                            $indicators[] = Indicator::make('Nominal ≥ Rp' . number_format((float) $data['min'], 0, ',', '.'))->removeField('min');
                        }

                        if (filled($data['max'] ?? null)) {
                            $indicators[] = Indicator::make('Nominal ≤ Rp' . number_format((float) $data['max'], 0, ',', '.'))->removeField('max');
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
                    ->visible(fn (CustomerWalletTransaction $record): bool => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->modalHeading('Setujui Withdrawal')
                    ->modalDescription('Withdrawal akan ditandai completed dan notifikasi WhatsApp dikirim ke customer melalui queue.')
                    ->modalSubmitActionLabel('Ya, Setujui')
                    ->action(function (CustomerWalletTransaction $record): void {
                        try {
                            DB::transaction(function () use ($record): void {
                                $transaction = CustomerWalletTransaction::query()
                                    ->whereKey($record->id)
                                    ->lockForUpdate()
                                    ->first();

                                if (! $transaction) {
                                    throw new \RuntimeException('Data withdrawal tidak ditemukan.');
                                }

                                if ($transaction->status !== 'pending') {
                                    throw new \RuntimeException('Withdrawal ini sudah diproses sebelumnya.');
                                }

                                $transaction->forceFill([
                                    'status' => 'completed',
                                    'completed_at' => now(),
                                ])->save();
                            });

                            SendWithdrawalApprovedWhatsAppJob::dispatch((int) $record->id)->afterCommit();

                            Notification::make()
                                ->success()
                                ->title('Withdrawal Disetujui')
                                ->body('Status withdrawal berhasil diubah menjadi completed. Notifikasi WhatsApp sedang diproses queue.')
                                ->send();
                        } catch (\Throwable $exception) {
                            Notification::make()
                                ->danger()
                                ->title('Gagal Menyetujui Withdrawal')
                                ->body($exception->getMessage())
                                ->send();
                        }
                    }),
                ViewAction::make(),
            ])
            ->toolbarActions([]);
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
                    $indicators[] = Indicator::make($label . ' dari ' . $data['from'])->removeField('from');
                }

                if (filled($data['until'] ?? null)) {
                    $indicators[] = Indicator::make($label . ' sampai ' . $data['until'])->removeField('until');
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
}
