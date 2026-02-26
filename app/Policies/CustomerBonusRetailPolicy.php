<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\CustomerBonusRetail;
use Illuminate\Auth\Access\HandlesAuthorization;

class CustomerBonusRetailPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:CustomerBonusRetail');
    }

    public function view(AuthUser $authUser, CustomerBonusRetail $customerBonusRetail): bool
    {
        return $authUser->can('View:CustomerBonusRetail');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:CustomerBonusRetail');
    }

    public function update(AuthUser $authUser, CustomerBonusRetail $customerBonusRetail): bool
    {
        return $authUser->can('Update:CustomerBonusRetail');
    }

    public function delete(AuthUser $authUser, CustomerBonusRetail $customerBonusRetail): bool
    {
        return $authUser->can('Delete:CustomerBonusRetail');
    }

    public function restore(AuthUser $authUser, CustomerBonusRetail $customerBonusRetail): bool
    {
        return $authUser->can('Restore:CustomerBonusRetail');
    }

    public function forceDelete(AuthUser $authUser, CustomerBonusRetail $customerBonusRetail): bool
    {
        return $authUser->can('ForceDelete:CustomerBonusRetail');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:CustomerBonusRetail');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:CustomerBonusRetail');
    }

    public function replicate(AuthUser $authUser, CustomerBonusRetail $customerBonusRetail): bool
    {
        return $authUser->can('Replicate:CustomerBonusRetail');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:CustomerBonusRetail');
    }

}