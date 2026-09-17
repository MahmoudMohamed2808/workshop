<?php

use App\Models\Brand;
use App\Models\DeviceType;
use App\Models\Problem;
use App\Services\ProblemUsageService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('tracks usage and surfaces most used first', function () {
    $service = app(ProblemUsageService::class);
    $type = DeviceType::factory()->create();

    $popular = Problem::factory()->create(['device_type_id' => $type->id, 'usage_count' => 0]);
    $rare = Problem::factory()->create(['device_type_id' => $type->id, 'usage_count' => 0]);

    $service->incrementUsage($popular);
    $service->incrementUsage($popular);

    $top = $service->mostUsedFor($type);

    expect($popular->fresh()->usage_count)->toBe(2)
        ->and($top->first()->id)->toBe($popular->id)
        ->and($top->last()->id)->toBe($rare->id);
});

test('scopes most used to brand with general fallback', function () {
    $service = app(ProblemUsageService::class);
    $type = DeviceType::factory()->create();
    $brand = Brand::factory()->create(['device_type_id' => $type->id]);

    $specific = Problem::factory()->create([
        'device_type_id' => $type->id,
        'brand_id' => $brand->id,
        'usage_count' => 5,
    ]);
    $general = Problem::factory()->create([
        'device_type_id' => $type->id,
        'brand_id' => null,
        'usage_count' => 1,
    ]);

    $results = $service->mostUsedFor($type, $brand);

    expect($results->pluck('id')->all())->toBe([$specific->id, $general->id]);
});
