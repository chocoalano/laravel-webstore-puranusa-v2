<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\CustomerBonusMatching;
use Illuminate\Auth\Access\HandlesAuthorization;

class CustomerBonusMatchingPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:CustomerBonusMatching');
    }

    public function view(AuthUser $authUser, CustomerBonusMatching $customerBonusMatching): bool
    {
        return $authUser->can('View:CustomerBonusMatching');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:CustomerBonusMatching');
    }

    public function update(AuthUser $authUser, CustomerBonusMatching $customerBonusMatching): bool
    {
        return $authUser->can('Update:CustomerBonusMatching');
    }

    public function delete(AuthUser $authUser, CustomerBonusMatching $customerBonusMatching): bool
    {
        return $authUser->can('Delete:CustomerBonusMatching');
    }

    public function restore(AuthUser $authUser, CustomerBonusMatching $customerBonusMatching): bool
    {
        return $authUser->can('Restore:CustomerBonusMatching');
    }

    public function forceDelete(AuthUser $authUser, CustomerBonusMatching $customerBonusMatching): bool
    {
        return $authUser->can('ForceDelete:CustomerBonusMatching');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:CustomerBonusMatching');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:CustomerBonusMatching');
    }

    public function replicate(AuthUser $authUser, CustomerBonusMatching $customerBonusMatching): bool
    {
        return $authUser->can('Replicate:CustomerBonusMatching');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:CustomerBonusMatching');
    }

}