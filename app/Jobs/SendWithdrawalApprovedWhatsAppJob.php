<?php

namespace App\Jobs;

use App\Models\CustomerWalletTransaction;
use App\Services\QontactService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class SendWithdrawalApprovedWhatsAppJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 120;

    public function __construct(
        public int $transactionId,
    ) {
        $this->onQueue('whatsapp');
    }

    public function handle(QontactService $qontactService): void
    {
        $transaction = CustomerWalletTransaction::query()
            ->with('customer:id,name,phone')
            ->whereKey($this->transactionId)
            ->first();

        if (! $transaction) {
            Log::error('Withdrawal approved WhatsApp failed: transaction not found.', [
                'transaction_id' => $this->transactionId,
            ]);

            throw new RuntimeException('Data transaksi withdrawal tidak ditemukan.');
        }

        if (($transaction->type !== 'withdrawal') || ($transaction->status !== 'completed')) {
            Log::error('Withdrawal approved WhatsApp failed: invalid transaction state.', [
                'transaction_id' => $transaction->id,
                'type' => $transaction->type,
                'status' => $transaction->status,
            ]);

            throw new RuntimeException('Status transaksi tidak valid untuk kirim notifikasi WhatsApp.');
        }

        $customerName = (string) ($transaction->customer?->name ?? '');
        $customerPhone = (string) ($transaction->customer?->phone ?? '');

        if ($customerName === '' || $customerPhone === '') {
            Log::error('Withdrawal approved WhatsApp failed: customer contact incomplete.', [
                'transaction_id' => $transaction->id,
                'customer_id' => $transaction->customer_id,
            ]);

            throw new RuntimeException('Data kontak customer tidak lengkap untuk kirim notifikasi WhatsApp.');
        }

        $rawAmount = (float) ($transaction->amount ?? 0);
        $rawAdminFee = self::extractAdminFeeFromNotes((string) ($transaction->notes ?? ''));
        $rawNetAmount = max(0.0, $rawAmount - $rawAdminFee);

        // Format tanpa prefix "Rp" karena template body sudah mengandung "Rp {{2}}"
        $formattedAmount = self::formatNumber($rawAmount);
        $formattedAdminFee = $rawAdminFee > 0 ? self::formatNumber($rawAdminFee) : '';
        $formattedNetAmount = $rawAdminFee > 0 ? self::formatNumber($rawNetAmount) : '';

        Log::info('Withdrawal approved WhatsApp job started.', [
            'transaction_id' => $transaction->id,
            'customer_id' => $transaction->customer_id,
            'customer_name' => $customerName,
            'customer_phone' => $customerPhone,
            'amount' => $rawAmount,
            'admin_fee' => $rawAdminFee,
            'net_amount' => $rawNetAmount,
            'nominal_text' => $rawAdminFee > 0
                ? "{$formattedAmount} - {$formattedAdminFee} = {$formattedNetAmount}"
                : $formattedAmount,
        ]);

        $sent = $qontactService->sendWithdrawalApproved(
            $customerName,
            $customerPhone,
            $formattedAmount,
            $formattedAdminFee,
            $formattedNetAmount,
        );

        if (! $sent) {
            Log::error('Withdrawal approved WhatsApp failed: provider send returned false.', [
                'transaction_id' => $transaction->id,
                'customer_id' => $transaction->customer_id,
                'customer_phone' => $customerPhone,
            ]);

            throw new RuntimeException('Pengiriman notifikasi WhatsApp gagal.');
        }

        Log::info('Withdrawal approved WhatsApp job completed successfully.', [
            'transaction_id' => $transaction->id,
        ]);
    }

    public function failed(?\Throwable $exception): void
    {
        Log::error('Withdrawal approved WhatsApp job failed.', [
            'transaction_id' => $this->transactionId,
            'error' => $exception?->getMessage(),
        ]);
    }

    private static function extractAdminFeeFromNotes(string $notes): float
    {
        if ($notes === '' || ! preg_match('/Biaya admin:\s*Rp\s*([0-9\.\,]+)/i', $notes, $matches)) {
            return 0.0;
        }

        $raw = str_replace(['.', ','], ['', '.'], trim((string) ($matches[1] ?? '0')));

        return max(0.0, (float) $raw);
    }

    private static function formatNumber(float $amount): string
    {
        return number_format((int) round($amount), 0, ',', '.');
    }
}
