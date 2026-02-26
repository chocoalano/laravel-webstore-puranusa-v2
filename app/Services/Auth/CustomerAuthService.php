<?php

namespace App\Services\Auth;

use App\Repositories\Auth\Contracts\CustomerAuthRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerAuthService
{
    public function __construct(
        private readonly CustomerAuthRepositoryInterface $repository
    ) {}

    public function attemptLogin(string $username, string $password, bool $remember): bool
    {
        return Auth::guard('customer')->attempt(
            ['username' => $username, 'password' => $password],
            $remember
        );
    }

    public function logout(Request $request): void
    {
        Auth::guard('customer')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }
}
