<?php

namespace App\Services\Auth;

use App\Http\Requests\Auth\RegisterRequest;
use App\Models\Customer;
use App\Repositories\Auth\Contracts\CustomerRegistrationRepositoryInterface;
use Illuminate\Support\Str;

class CustomerRegistrationService
{
    public function __construct(
        private readonly CustomerRegistrationRepositoryInterface $repository
    ) {}

    public function register(RegisterRequest $request): Customer
    {
        $sponsor = null;

        if ($request->filled('referral_code')) {
            $sponsor = $this->repository->findBySponsorCode(
                $request->string('referral_code')->toString()
            );
        }

        return $this->repository->create([
            'name'       => $request->string('name')->trim()->toString(),
            'username'   => $request->string('username')->trim()->lower()->toString(),
            'email'      => $request->string('email')->trim()->lower()->toString(),
            'phone'      => $request->string('telp')->trim()->toString(),
            'nik'        => $request->filled('nik') ? $request->string('nik')->trim()->toString() : null,
            'gender'     => $request->string('gender')->toString(),
            'alamat'     => $request->filled('alamat') ? $request->string('alamat')->trim()->toString() : null,
            'password'   => $request->string('password')->toString(),
            'ref_code'   => $this->generateUniqueRefCode(),
            'sponsor_id' => $sponsor?->id,
            'status'     => 1,
        ]);
    }

    private function generateUniqueRefCode(): string
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (Customer::query()->where('ref_code', $code)->exists());

        return $code;
    }
}
