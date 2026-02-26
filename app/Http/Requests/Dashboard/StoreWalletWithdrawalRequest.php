<?php

namespace App\Http\Requests\Dashboard;

use Illuminate\Foundation\Http\FormRequest;

class StoreWalletWithdrawalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('customer') !== null;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:10000'],
            'password' => ['required', 'string'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'amount.required' => 'Nominal withdrawal wajib diisi.',
            'amount.numeric' => 'Nominal withdrawal harus berupa angka.',
            'amount.min' => 'Nominal withdrawal minimal Rp 10.000.',
            'password.required' => 'Password wajib diisi untuk konfirmasi withdrawal.',
            'notes.max' => 'Catatan withdrawal maksimal 500 karakter.',
        ];
    }

    /**
     * @return array{amount:float,password:string,notes:string|null}
     */
    public function payload(): array
    {
        $notes = trim((string) $this->input('notes', ''));

        return [
            'amount' => (float) $this->input('amount'),
            'password' => (string) $this->input('password'),
            'notes' => $notes !== '' ? $notes : null,
        ];
    }
}
