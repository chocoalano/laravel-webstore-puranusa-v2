<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, list<string|\Illuminate\Contracts\Validation\Rule>> */
    public function rules(): array
    {
        return [
            'name'          => ['required', 'string', 'max:255'],
            'username'      => ['required', 'string', 'min:3', 'max:30', 'regex:/^[a-zA-Z0-9_.]+$/', 'unique:customers,username'],
            'email'         => ['required', 'string', 'email', 'max:255', 'unique:customers,email'],
            'telp'          => ['required', 'string', 'min:8', 'max:16'],
            'nik'           => ['nullable', 'string', 'digits:16'],
            'gender'        => ['required', 'string', 'in:L,P'],
            'alamat'        => ['nullable', 'string', 'max:500'],
            'referral_code' => ['nullable', 'string', 'exists:customers,ref_code'],
            'password'      => ['required', 'string', 'confirmed', Password::min(8)],
            'terms'         => ['required', 'accepted'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'name.required'             => 'Nama lengkap wajib diisi.',
            'username.required'         => 'Username wajib diisi.',
            'username.min'              => 'Username minimal 3 karakter.',
            'username.max'              => 'Username maksimal 30 karakter.',
            'username.regex'            => 'Username hanya boleh berisi huruf, angka, underscore, dan titik.',
            'username.unique'           => 'Username sudah digunakan, pilih yang lain.',
            'email.required'            => 'Email wajib diisi.',
            'email.email'               => 'Format email tidak valid.',
            'email.unique'              => 'Email sudah terdaftar.',
            'telp.required'             => 'Nomor WhatsApp wajib diisi.',
            'telp.min'                  => 'Nomor WhatsApp minimal 8 digit.',
            'nik.digits'                => 'NIK harus 16 digit.',
            'gender.required'           => 'Jenis kelamin wajib dipilih.',
            'gender.in'                 => 'Jenis kelamin tidak valid.',
            'referral_code.exists'      => 'Kode referral tidak ditemukan.',
            'password.required'         => 'Kata sandi wajib diisi.',
            'password.confirmed'        => 'Konfirmasi kata sandi tidak cocok.',
            'password.min'              => 'Kata sandi minimal 8 karakter.',
            'terms.required'            => 'Anda harus menyetujui Syarat & Ketentuan.',
            'terms.accepted'            => 'Anda harus menyetujui Syarat & Ketentuan.',
        ];
    }
}
