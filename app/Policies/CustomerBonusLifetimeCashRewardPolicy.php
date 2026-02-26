<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\CustomerBonusLifetimeCashReward;
use Illuminate\Auth\Access\HandlesAuthorization;

class CustomerBonusLifetimeCashRewardPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:CustomerBonusLifetimeCashReward');
    }

    public function view(AuthUser $authUser, CustomerBonusLifetimeCashReward $customerBonusLifetimeCashReward): bool
    {
        return $authUser->can('View:CustomerBonusLifetimeCashReward');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:CustomerBonusLifetimeCashReward');
    }

    public function update(AuthUser $authUser, CustomerBonusLifetimeCashReward $customerBonusLifetimeCashReward): bool
    {
        return $authUser->can('Update:CustomerBonusLifetimeCashReward');
    }

    public function delete(AuthUser $authUser, CustomerBonusLifetimeCashReward $customerBonusLifetimeCashReward): bool
    {
        return $authUser->can('Delete:CustomerBonusLifetimeCashReward');
    }

    public function restore(AuthUser $authUser, CustomerBonusLifetimeCashReward $customerBonusLifetimeCashReward): bool
    {
        return $authUser->can('Restore:CustomerBonusLifetimeCashReward');
    }

    public function forceDelete(AuthUser $authUser, CustomerBonusLifetimeCashReward $customerBonusLifetimeCashReward): bool
    {
        return $authUser->can('ForceDelete:CustomerBonusLifetimeCashReward');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:CustomerBonusLifetimeCashReward');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:CustomerBonusLifetimeCashReward');
    }

    public function replicate(AuthUser $authUser, CustomerBonusLifetimeCashReward $customerBonusLifetimeCashReward): bool
    {
        return $authUser->can('Replicate:CustomerBonusLifetimeCashReward');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:CustomerBonusLifetimeCashReward');
    }

}