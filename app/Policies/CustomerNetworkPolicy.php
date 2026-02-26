<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\CustomerNetwork;
use Illuminate\Auth\Access\HandlesAuthorization;

class CustomerNetworkPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:CustomerNetwork');
    }

    public function view(AuthUser $authUser, CustomerNetwork $customerNetwork): bool
    {
        return $authUser->can('View:CustomerNetwork');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:CustomerNetwork');
    }

    public function update(AuthUser $authUser, CustomerNetwork $customerNetwork): bool
    {
        return $authUser->can('Update:CustomerNetwork');
    }

    public function delete(AuthUser $authUser, CustomerNetwork $customerNetwork): bool
    {
        return $authUser->can('Delete:CustomerNetwork');
    }

    public function restore(AuthUser $authUser, CustomerNetwork $customerNetwork): bool
    {
        return $authUser->can('Restore:CustomerNetwork');
    }

    public function forceDelete(AuthUser $authUser, CustomerNetwork $customerNetwork): bool
    {
        return $authUser->can('ForceDelete:CustomerNetwork');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:CustomerNetwork');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:CustomerNetwork');
    }

    public function replicate(AuthUser $authUser, CustomerNetwork $customerNetwork): bool
    {
        return $authUser->can('Replicate:CustomerNetwork');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:CustomerNetwork');
    }

}