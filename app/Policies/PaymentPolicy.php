<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;

class PaymentPolicy
{
    /**
     * المالية (مدفوعات/فواتير) لمدير النظام والموظف (استقبال/كاشير) فقط.
     * الفني والمستخدم العادي ممنوعان — تُدار عبر صلاحيتي
     * ViewPayments / ManagePayments (تُمنح للموظف، ومدير النظام
     * يتخطى الفحص أصلًا عبر Gate::before).
     */
    public function viewAny(User $user): bool
    {
        return $user->can('ViewPayments') || $user->can('ManagePayments');
    }

    public function view(User $user, Payment $payment): bool
    {
        return $user->can('ViewPayments') || $user->can('ManagePayments');
    }

    public function create(User $user): bool
    {
        return $user->can('ManagePayments');
    }

    public function update(User $user, Payment $payment): bool
    {
        return $user->can('ManagePayments');
    }

    public function delete(User $user, Payment $payment): bool
    {
        return $user->can('ManagePayments');
    }

    public function restore(User $user, Payment $payment): bool
    {
        return $user->can('ManagePayments');
    }

    public function forceDelete(User $user, Payment $payment): bool
    {
        return $user->can('ManagePayments');
    }
}
