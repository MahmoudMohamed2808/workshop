<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\RepairOrder;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RepairOrderPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('ViewAny:RepairOrder');
    }

    public function view(User $user, RepairOrder $repairOrder): bool
    {
        if (! $user->can('View:RepairOrder')) {
            return false;
        }

        return $this->passesOwnership($user, $repairOrder);
    }

    public function create(User $user): bool
    {
        return $user->can('Create:RepairOrder');
    }

    public function update(User $user, RepairOrder $repairOrder): bool
    {
        if (! $user->can('Update:RepairOrder')) {
            return false;
        }

        return $this->passesOwnership($user, $repairOrder);
    }

    public function delete(User $user, RepairOrder $repairOrder): bool
    {
        // الفني لا يحذف أوامر أبدًا — حتى لو مُنح الصلاحية من الواجهة.
        if ($user->isTechnician()) {
            return false;
        }

        return $user->can('Delete:RepairOrder');
    }

    public function deleteAny(User $user): bool
    {
        if ($user->isTechnician()) {
            return false;
        }

        return $user->can('DeleteAny:RepairOrder');
    }

    public function restore(User $user, RepairOrder $repairOrder): bool
    {
        if ($user->isTechnician()) {
            return false;
        }

        return $user->can('Restore:RepairOrder');
    }

    public function forceDelete(User $user, RepairOrder $repairOrder): bool
    {
        if ($user->isTechnician()) {
            return false;
        }

        return $user->can('ForceDelete:RepairOrder');
    }

    public function forceDeleteAny(User $user): bool
    {
        if ($user->isTechnician()) {
            return false;
        }

        return $user->can('ForceDeleteAny:RepairOrder');
    }

    public function restoreAny(User $user): bool
    {
        if ($user->isTechnician()) {
            return false;
        }

        return $user->can('RestoreAny:RepairOrder');
    }

    public function replicate(User $user, RepairOrder $repairOrder): bool
    {
        if (! $user->can('Replicate:RepairOrder')) {
            return false;
        }

        return $this->passesOwnership($user, $repairOrder);
    }

    public function reorder(User $user): bool
    {
        return $user->can('Reorder:RepairOrder');
    }

    /**
     * الفني يتعامل مع أوامره المسندة إليه فقط.
     * باقي الأدوار (موظف/مدير) بلا قيد ملكية.
     */
    protected function passesOwnership(User $user, RepairOrder $repairOrder): bool
    {
        if ($user->isTechnician()) {
            return (int) $repairOrder->technician_id === (int) $user->getKey();
        }

        return true;
    }
}
