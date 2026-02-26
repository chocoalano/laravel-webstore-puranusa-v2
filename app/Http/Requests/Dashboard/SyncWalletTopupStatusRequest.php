<?php

namespace App\Http\Requests\Dashboard;

use App\Models\CustomerWalletTransaction;
use Illuminate\Foundation\Http\FormRequest;

class SyncWalletTopupStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        $customer = $this->user('customer');
        $walletTransaction = $this->route('walletTransaction');

        return $customer !== null
            && $walletTransaction instanceof CustomerWalletTransaction
            && (int) $walletTransaction->customer_id === (int) $customer->id
            && strtolower((string) $walletTransaction->type) === 'topup';
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [];
    }
}
