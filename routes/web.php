<?php

use App\Http\Controllers\RepairOrderPrintController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    Route::get('repair-orders/{repairOrder}/sale-print', [RepairOrderPrintController::class, 'salePrint'])
        ->name('repair-orders.sale-print');
});

require __DIR__.'/settings.php';
