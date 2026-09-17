<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\DeviceModel;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class DeviceModelPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:DeviceModel');
    }

    public function view(AuthUser $authUser, DeviceModel $deviceModel): bool
    {
        return $authUser->can('View:DeviceModel');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:DeviceModel');
    }

    public function update(AuthUser $authUser, DeviceModel $deviceModel): bool
    {
        return $authUser->can('Update:DeviceModel');
    }

    public function delete(AuthUser $authUser, DeviceModel $deviceModel): bool
    {
        return $authUser->can('Delete:DeviceModel');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:DeviceModel');
    }

    public function restore(AuthUser $authUser, DeviceModel $deviceModel): bool
    {
        return $authUser->can('Restore:DeviceModel');
    }

    public function forceDelete(AuthUser $authUser, DeviceModel $deviceModel): bool
    {
        return $authUser->can('ForceDelete:DeviceModel');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:DeviceModel');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:DeviceModel');
    }

    public function replicate(AuthUser $authUser, DeviceModel $deviceModel): bool
    {
        return $authUser->can('Replicate:DeviceModel');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:DeviceModel');
    }
}
