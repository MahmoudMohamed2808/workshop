<?php

use App\Models\RepairOrder;
use App\Services\QrCodeService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('generates svg for the repair order', function () {
    $order = RepairOrder::factory()->create();

    $svg = app(QrCodeService::class)->generateForRepairOrder($order);

    expect($svg)->toContain('<svg');
});
