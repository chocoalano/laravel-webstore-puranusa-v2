<?php

namespace App\Repositories\Auth;

use App\Models\Customer;
use App\Repositories\Auth\Contracts\CustomerAuthRepositoryInterface;

class CustomerAuthRepository implements CustomerAuthRepositoryInterface
{
    public function findByUsername(string $username): ?Customer
    {
        return Customer::query()->where('username', $username)->first();
    }
}
