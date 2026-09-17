<?php

namespace App\Http\Controllers;

use App\Models\RepairOrder;
use App\Models\User;
use App\Services\InvoiceService;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class RepairOrderPrintController extends Controller
{
    public function salePrint(RepairOrder $repairOrder, InvoiceService $invoices): View
    {
        // التفويض: المشاهدة حسب الصلاحية، والفني ممنوع من طباعة الفواتير.
        Gate::authorize('view', $repairOrder);

        $user = request()->user();

        abort_if($user instanceof User && $user->isTechnician(), 403);

        // الطباعة للحالات الجاهزة فقط (جاهز للتسليم / تم التسليم).
        // لو الفاتورة موجودة نطبعها مباشرة، لو لا نولدها تلقائيا عند الجاهزية.
        abort_unless(
            $repairOrder->invoice()->exists()
                || in_array($repairOrder->status, ['ready_for_delivery', 'delivered'], true),
            403,
            __('resources.repair_orders.actions.print_sale_invoice.not_ready')
        );

        if (! $repairOrder->invoice()->exists()) {
            try {
                $invoices->generateFromRepairOrder($repairOrder->refresh());
            } catch (\Throwable) {
                // Fall through and render the order data without an invoice.
            }
        }

        $repairOrder->loadMissing([
            'customer',
            'deviceType',
            'brand',
            'deviceModel',
            'technician',
            'orderProblems.problem',
            'orderParts.sparePart',
            'invoice',
        ]);

        return view('invoices.sale-print', [
            'order' => $repairOrder,
            'invoice' => $repairOrder->invoice,
        ]);
    }
}
