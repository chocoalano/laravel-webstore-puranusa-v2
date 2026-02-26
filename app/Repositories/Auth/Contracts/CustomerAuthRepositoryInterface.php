<?php

namespace App\Repositories\Auth\Contracts;

use App\Models\Customer;

interface CustomerAuthRepositoryInterface
{
    public function findByUsername(string $username): ?Customer;
}
