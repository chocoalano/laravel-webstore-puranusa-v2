<?php

namespace App\Http\Requests\Dashboard;

use App\Models\Customer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAccountProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->resolveAuthenticatedCustomer() !== null;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $customerId = $this->resolveAuthenticatedCustomer()?->id;

        return [
            'username' => [
                'required',
                'string',
                'min:3',
                'max:30',
                'regex:/^[a-zA-Z0-9_.]+$/',
                Rule::unique('customers', 'username')->ignore($customerId),
            ],
            'name' => ['required', 'string', 'max:255'],
            'nik' => [
                'required',
                'string',
                'digits:16',
                Rule::unique('customers', 'nik')->ignore($customerId),
            ],
            'gender' => ['required', 'string', 'in:L,P'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('customers', 'email')->ignore($customerId),
            ],
            'phone' => [
                'required',
                'string',
                'min:8',
                'max:20',
                'regex:/^[0-9+]+$/',
                function (string $attribute, mixed $value, \Closure $fail) use ($customerId): void {
                    $phone = trim((string) $value);

                    if ($phone === '') {
                        return;
                    }

                    $currentPhone = trim((string) ($this->resolveAuthenticatedCustomer()?->phone ?? ''));

                    if ($currentPhone === $phone) {
                        return;
                    }

                    $usageCount = Customer::query()
                        ->where('phone', $phone)
                        ->when(
                            $customerId !== null,
                            fn ($query) => $query->where('id', '!=', $customerId)
                        )
                        ->count();

                    if ($usageCount >= 7) {
                        $fail('Nomor telepon/WhatsApp ini sudah digunakan oleh 7 akun.');
                    }
                },
            ],
            'bank_name' => ['required', 'string', 'max:100'],
            'bank_account' => ['required', 'string', 'min:5', 'max:50', 'regex:/^[0-9]+$/'],
            'npwp_nama' => ['nullable', 'string', 'max:255'],
            'npwp_number' => ['nullable', 'string', 'max:30', 'regex:/^[0-9.\-]+$/'],
            'npwp_jk' => ['nullable', 'integer', 'in:1,2'],
            'npwp_date' => ['nullable', 'date'],
            'npwp_alamat' => ['nullable', 'string', 'max:1000'],
            'npwp_menikah' => ['nullable', 'string', 'in:Y,N'],
            'npwp_anak' => ['nullable', 'string', 'max:2', 'regex:/^\d+$/'],
            'npwp_kerja' => ['nullable', 'string', 'in:Y,N'],
            'npwp_office' => ['nullable', 'string', 'max:255'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'username.required' => 'Username wajib diisi.',
            'username.min' => 'Username minimal 3 karakter.',
            'username.max' => 'Username maksimal 30 karakter.',
            'username.regex' => 'Username hanya boleh berisi huruf, angka, underscore, dan titik.',
            'username.unique' => 'Username sudah digunakan akun lain.',
            'name.required' => 'Nama lengkap wajib diisi.',
            'name.max' => 'Nama lengkap maksimal 255 karakter.',
            'nik.required' => 'NIK wajib diisi.',
            'nik.digits' => 'NIK harus 16 digit angka.',
            'nik.unique' => 'NIK sudah digunakan akun lain.',
            'gender.required' => 'Jenis kelamin wajib dipilih.',
            'gender.in' => 'Jenis kelamin tidak valid.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.max' => 'Email maksimal 255 karakter.',
            'email.unique' => 'Email sudah digunakan akun lain.',
            'phone.required' => 'Nomor telepon/WhatsApp wajib diisi.',
            'phone.min' => 'Nomor telepon/WhatsApp minimal 8 karakter.',
            'phone.max' => 'Nomor telepon/WhatsApp maksimal 20 karakter.',
            'phone.regex' => 'Nomor telepon/WhatsApp hanya boleh berisi angka atau tanda +.',
            'bank_name.required' => 'Nama bank wajib diisi.',
            'bank_name.max' => 'Nama bank maksimal 100 karakter.',
            'bank_account.required' => 'Nomor rekening wajib diisi.',
            'bank_account.min' => 'Nomor rekening minimal 5 digit.',
            'bank_account.max' => 'Nomor rekening maksimal 50 digit.',
            'bank_account.regex' => 'Nomor rekening hanya boleh berisi angka.',
            'npwp_nama.max' => 'Nama NPWP maksimal 255 karakter.',
            'npwp_number.max' => 'Nomor NPWP maksimal 30 karakter.',
            'npwp_number.regex' => 'Nomor NPWP hanya boleh berisi angka, titik, atau tanda hubung.',
            'npwp_jk.in' => 'Jenis kelamin NPWP tidak valid.',
            'npwp_date.date' => 'Tanggal NPWP tidak valid.',
            'npwp_alamat.max' => 'Alamat NPWP maksimal 1000 karakter.',
            'npwp_menikah.in' => 'Status menikah NPWP tidak valid.',
            'npwp_anak.max' => 'Jumlah anak NPWP maksimal 2 digit.',
            'npwp_anak.regex' => 'Jumlah anak NPWP harus berupa angka.',
            'npwp_kerja.in' => 'Status kerja NPWP tidak valid.',
            'npwp_office.max' => 'Nama kantor NPWP maksimal 255 karakter.',
        ];
    }

    /**
     * @return array{
     *   username:string,
     *   name:string,
     *   nik:string,
     *   gender:string,
     *   email:string,
     *   phone:string,
     *   bank_name:string,
     *   bank_account:string,
     *   npwp:?array{
     *     nama:?string,
     *     npwp:?string,
     *     jk:?int,
     *     npwp_date:?string,
     *     alamat:?string,
     *     menikah:?string,
     *     anak:?string,
     *     kerja:?string,
     *     office:?string
     *   }
     * }
     */
    public function payload(): array
    {
        return [
            'username' => strtolower(trim((string) $this->input('username'))),
            'name' => trim((string) $this->input('name')),
            'nik' => preg_replace('/\D+/', '', (string) $this->input('nik')) ?? '',
            'gender' => $this->normalizeGender((string) $this->input('gender')),
            'email' => strtolower(trim((string) $this->input('email'))),
            'phone' => preg_replace('/\s+/', '', trim((string) $this->input('phone'))) ?? '',
            'bank_name' => trim((string) $this->input('bank_name')),
            'bank_account' => preg_replace('/\D+/', '', (string) $this->input('bank_account')) ?? '',
            'npwp' => $this->npwpPayload(),
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'username' => strtolower(trim((string) $this->input('username', ''))),
            'name' => trim((string) $this->input('name', '')),
            'nik' => preg_replace('/\D+/', '', (string) $this->input('nik', '')) ?? '',
            'gender' => $this->normalizeGender((string) $this->input('gender', '')),
            'email' => strtolower(trim((string) $this->input('email', ''))),
            'phone' => preg_replace('/\s+/', '', trim((string) $this->input('phone', ''))) ?? '',
            'bank_name' => trim((string) $this->input('bank_name', '')),
            'bank_account' => preg_replace('/\D+/', '', (string) $this->input('bank_account', '')) ?? '',
            'npwp_nama' => trim((string) $this->input('npwp_nama', '')),
            'npwp_number' => trim((string) $this->input('npwp_number', '')),
            'npwp_jk' => $this->normalizeNpwpGender($this->input('npwp_jk')),
            'npwp_date' => trim((string) $this->input('npwp_date', '')),
            'npwp_alamat' => trim((string) $this->input('npwp_alamat', '')),
            'npwp_menikah' => $this->normalizeYn($this->input('npwp_menikah')),
            'npwp_anak' => preg_replace('/\D+/', '', (string) $this->input('npwp_anak', '')) ?? '',
            'npwp_kerja' => $this->normalizeYn($this->input('npwp_kerja')),
            'npwp_office' => trim((string) $this->input('npwp_office', '')),
        ]);
    }

    private function normalizeGender(string $gender): string
    {
        $normalized = strtoupper(trim($gender));

        return match ($normalized) {
            'L', 'LAKI-LAKI', 'MALE', 'M' => 'L',
            'P', 'PEREMPUAN', 'FEMALE', 'F' => 'P',
            default => $normalized,
        };
    }

    private function normalizeNpwpGender(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        $normalized = strtoupper(trim((string) $value));

        return match ($normalized) {
            '1', 'L' => 1,
            '2', 'P' => 2,
            default => null,
        };
    }

    private function normalizeYn(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $normalized = strtoupper(trim((string) $value));

        if (in_array($normalized, ['Y', 'N'], true)) {
            return $normalized;
        }

        return null;
    }

    /**
     * @return array{
     *   nama:?string,
     *   npwp:?string,
     *   jk:?int,
     *   npwp_date:?string,
     *   alamat:?string,
     *   menikah:?string,
     *   anak:?string,
     *   kerja:?string,
     *   office:?string
     * }|null
     */
    private function npwpPayload(): ?array
    {
        $payload = [
            'nama' => $this->nullableString($this->input('npwp_nama')),
            'npwp' => $this->nullableString($this->input('npwp_number')),
            'jk' => $this->normalizeNpwpGender($this->input('npwp_jk')),
            'npwp_date' => $this->nullableString($this->input('npwp_date')),
            'alamat' => $this->nullableString($this->input('npwp_alamat')),
            'menikah' => $this->normalizeYn($this->input('npwp_menikah')),
            'anak' => $this->nullableString($this->input('npwp_anak')),
            'kerja' => $this->normalizeYn($this->input('npwp_kerja')),
            'office' => $this->nullableString($this->input('npwp_office')),
        ];

        $hasAnyValue = collect($payload)->contains(fn (mixed $value): bool => $value !== null && $value !== '');

        if (! $hasAnyValue) {
            return null;
        }

        return $payload;
    }

    private function nullableString(mixed $value): ?string
    {
        $normalized = trim((string) ($value ?? ''));

        return $normalized === '' ? null : $normalized;
    }

    private function resolveAuthenticatedCustomer(): ?Customer
    {
        $customer = $this->user('customer');

        if ($customer instanceof Customer) {
            return $customer;
        }

        $tokenable = $this->user('sanctum');

        return $tokenable instanceof Customer ? $tokenable : null;
    }
}
