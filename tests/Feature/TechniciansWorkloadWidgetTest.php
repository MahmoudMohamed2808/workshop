<?php

use App\Filament\Resources\RepairOrders\RepairOrderStatus;
use App\Filament\Widgets\TechniciansWorkloadWidget;
use App\Models\RepairOrder;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => User::ROLE_TECHNICIAN, 'guard_name' => 'web']);
});

it('shows only technicians with their active and ready order counts', function () {
    $viewer = User::factory()->create();
    $technician = User::factory()->create(['name' => 'فني الاختبار']);
    $technician->assignRole(User::ROLE_TECHNICIAN);

    RepairOrder::factory()->create(['technician_id' => $technician->id, 'status' => RepairOrderStatus::RECEIVED]);
    RepairOrder::factory()->create(['technician_id' => $technician->id, 'status' => RepairOrderStatus::IN_REPAIR]);
    RepairOrder::factory()->create(['technician_id' => $technician->id, 'status' => RepairOrderStatus::READY_FOR_DELIVERY]);
    RepairOrder::factory()->create(['technician_id' => $technician->id, 'status' => RepairOrderStatus::DELIVERED]);

    $nonTechnician = User::factory()->create(['name' => 'موظف استقبال']);
    RepairOrder::factory()->create(['technician_id' => $nonTechnician->id, 'status' => RepairOrderStatus::RECEIVED]);

    Livewire::actingAs($viewer)
        ->test(TechniciansWorkloadWidget::class)
        ->assertOk()
        ->assertCanSeeTableRecords([$technician])
        ->assertCanNotSeeTableRecords([$nonTechnician])
        ->assertCountTableRecords(1);

    expect(User::technicians()->withWorkloadCounts()->find($technician->id))
        ->active_orders_count->toBe(3)
        ->ready_orders_count->toBe(1);
});

it('shows an empty state when no technicians exist', function () {
    $viewer = User::factory()->create();

    Livewire::actingAs($viewer)
        ->test(TechniciansWorkloadWidget::class)
        ->assertOk()
        ->assertCountTableRecords(0)
        ->assertSee('لا يوجد فنيون بعد', false);
});
