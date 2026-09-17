<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Problem;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class ProblemPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Problem');
    }

    public function view(AuthUser $authUser, Problem $problem): bool
    {
        return $authUser->can('View:Problem');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Problem');
    }

    public function update(AuthUser $authUser, Problem $problem): bool
    {
        return $authUser->can('Update:Problem');
    }

    public function delete(AuthUser $authUser, Problem $problem): bool
    {
        return $authUser->can('Delete:Problem');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Problem');
    }

    public function restore(AuthUser $authUser, Problem $problem): bool
    {
        return $authUser->can('Restore:Problem');
    }

    public function forceDelete(AuthUser $authUser, Problem $problem): bool
    {
        return $authUser->can('ForceDelete:Problem');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Problem');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Problem');
    }

    public function replicate(AuthUser $authUser, Problem $problem): bool
    {
        return $authUser->can('Replicate:Problem');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Problem');
    }
}
