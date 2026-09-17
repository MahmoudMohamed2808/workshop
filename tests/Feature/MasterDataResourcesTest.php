<?php

use App\Filament\Resources\Brands\Pages\EditBrand;
use App\Filament\Resources\DeviceModels\DeviceModelResource;
use App\Filament\Resources\DeviceTypes\Pages\EditDeviceType;
use App\Models\Brand;
use App\Models\DeviceModel;
use App\Models\DeviceType;
use Livewire\Livewire;

beforeEach(function () {
    seedPanelRoles();
});

it('lists device types with Arabic labels', function () {
    $user = staffPanelUser();
    DeviceType::factory()->create(['name' => 'ثلاجة']);

    $this->actingAs($user)
        ->get('/admin/device-types')
        ->assertOk()
        ->assertSee('أنواع الأجهزة', false)
        ->assertSee('ثلاجة', false);
});

it('lists brands with Arabic labels', function () {
    $user = staffPanelUser();
    Brand::factory()->create(['name' => 'توشيبا']);

    $this->actingAs($user)
        ->get('/admin/brands')
        ->assertOk()
        ->assertSee('الماركات', false)
        ->assertSee('توشيبا', false);
});

it('hides device models from navigation but keeps the pages reachable', function () {
    expect(DeviceModelResource::shouldRegisterNavigation())->toBeFalse();

    $user = staffPanelUser();
    DeviceModel::factory()->create(['name' => 'موديل اختبار']);

    $this->actingAs($user)
        ->get('/admin/device-models')
        ->assertOk()
        ->assertSee('الموديلات', false);
});

it('toggles a device type active state from the edit page', function () {
    $user = staffPanelUser();
    $type = DeviceType::factory()->create(['is_active' => true]);

    Livewire::actingAs($user)
        ->test(EditDeviceType::class, ['record' => $type->getRouteKey()])
        ->callAction('toggleActive')
        ->assertHasNoActionErrors();

    expect($type->refresh()->is_active)->toBeFalse();
});

it('toggles a brand active state from the edit page', function () {
    $user = staffPanelUser();
    $brand = Brand::factory()->create(['is_active' => false]);

    Livewire::actingAs($user)
        ->test(EditBrand::class, ['record' => $brand->getRouteKey()])
        ->callAction('toggleActive')
        ->assertHasNoActionErrors();

    expect($brand->refresh()->is_active)->toBeTrue();
});
