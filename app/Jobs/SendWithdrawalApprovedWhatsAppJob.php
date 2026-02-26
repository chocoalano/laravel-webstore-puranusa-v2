<?php

namespace App\Jobs;

use App\Models\CustomerWalletTransaction;
use App\Services\QontactService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

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
            Log::warning('Withdrawal approved WhatsApp skipped: transaction not found.', [
                'transaction_id' => $this->transactionId,
            ]);

            return;
        }

        if (($transaction->type !== 'withdrawal') || ($transaction->status !== 'completed')) {
            Log::warning('Withdrawal approved WhatsApp skipped: invalid transaction state.', [
                'transaction_id' => $transaction->id,
                'type' => $transaction->type,
                'status' => $transaction->status,
            ]);

            return;
        }

        $customerName = (string) ($transaction->customer?->name ?? '');
        $customerPhone = (string) ($transaction->customer?->phone ?? '');

        if ($customerName === '' || $customerPhone === '') {
            Log::warning('Withdrawal approved WhatsApp skipped: customer contact incomplete.', [
                'transaction_id' => $transaction->id,
                'customer_id' => $transaction->customer_id,
            ]);

            return;
        }

        $formattedAmount = 'Rp ' . number_format((float) $transaction->amount, 0, ',', '.');

        $qontactService->sendWithdrawalApproved(
            $customerName,
            $customerPhone,
            $formattedAmount
        );
    }

    public function failed(?\Throwable $exception): void
    {
        Log::error('Withdrawal approved WhatsApp job failed.', [
            'transaction_id' => $this->transactionId,
            'error' => $exception?->getMessage(),
        ]);
    }
}
