<?php

namespace App\Http\Requests\Dashboard;

use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;

class CheckOrderPaymentStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        $customer = $this->user('customer');
        $order = $this->route('order');

        return $customer !== null
            && $order instanceof Order
            && (int) $order->customer_id === (int) $customer->id;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [];
    }
}
