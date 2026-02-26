<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\CustomerBonusPairing;
use Illuminate\Auth\Access\HandlesAuthorization;

class CustomerBonusPairingPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:CustomerBonusPairing');
    }

    public function view(AuthUser $authUser, CustomerBonusPairing $customerBonusPairing): bool
    {
        return $authUser->can('View:CustomerBonusPairing');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:CustomerBonusPairing');
    }

    public function update(AuthUser $authUser, CustomerBonusPairing $customerBonusPairing): bool
    {
        return $authUser->can('Update:CustomerBonusPairing');
    }

    public function delete(AuthUser $authUser, CustomerBonusPairing $customerBonusPairing): bool
    {
        return $authUser->can('Delete:CustomerBonusPairing');
    }

    public function restore(AuthUser $authUser, CustomerBonusPairing $customerBonusPairing): bool
    {
        return $authUser->can('Restore:CustomerBonusPairing');
    }

    public function forceDelete(AuthUser $authUser, CustomerBonusPairing $customerBonusPairing): bool
    {
        return $authUser->can('ForceDelete:CustomerBonusPairing');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:CustomerBonusPairing');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:CustomerBonusPairing');
    }

    public function replicate(AuthUser $authUser, CustomerBonusPairing $customerBonusPairing): bool
    {
        return $authUser->can('Replicate:CustomerBonusPairing');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:CustomerBonusPairing');
    }

}