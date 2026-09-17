<?php

namespace App\Providers;

use App\Services\CostCalculatorService;
use App\Services\InventoryService;
use App\Services\InvoiceService;
use App\Services\PaymentService;
use App\Services\ProblemUsageService;
use App\Services\QrCodeService;
use App\Services\RepairOrderBundleService;
use App\Services\RepairOrderWorkflowService;
use App\Services\WarrantyService;
use App\Services\WhatsappService;
use App\Services\WorkshopSettingsService;
use Illuminate\Support\ServiceProvider;

class WorkshopServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(RepairOrderWorkflowService::class);
        $this->app->singleton(RepairOrderBundleService::class);
        $this->app->singleton(CostCalculatorService::class);
        $this->app->singleton(InventoryService::class);
        $this->app->singleton(InvoiceService::class);
        $this->app->singleton(PaymentService::class);
        $this->app->singleton(WarrantyService::class);
        $this->app->singleton(WhatsappService::class);
        $this->app->singleton(WorkshopSettingsService::class);
        $this->app->singleton(QrCodeService::class);
        $this->app->singleton(ProblemUsageService::class);

        // Facade accessor bindings (short aliases used by the facades below)
        $this->app->alias(WhatsappService::class, 'whatsapp');
        $this->app->alias(InventoryService::class, 'inventory');
        $this->app->alias(InvoiceService::class, 'invoice');
        $this->app->alias(WarrantyService::class, 'warranty');
        $this->app->alias(RepairOrderWorkflowService::class, 'repair-order-workflow');
    }
}
