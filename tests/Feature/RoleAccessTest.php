<?php

use App\Models\Payment;
use App\Models\RepairOrder;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

beforeEach(function () {
    foreach (roleAccessPermissions() as $name) {
        Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
    }

    $this->seed(RoleSeeder::class);
});

function roleAccessPermissions(): array
{
    return [
        'ViewAny:RepairOrder', 'View:RepairOrder', 'Create:RepairOrder', 'Update:RepairOrder',
        'Delete:RepairOrder', 'DeleteAny:RepairOrder',
        'ViewAny:Customer', 'View:Customer', 'Create:Customer', 'Update:Customer',
        'ViewPayments', 'ManagePayments',
        'ViewAny:SparePart', 'View:SparePart',
        'ViewAny:Brand', 'View:Brand', 'Create:Brand', 'Update:Brand',
        'ViewAny:DeviceModel', 'View:DeviceModel', 'Create:DeviceModel', 'Update:DeviceModel',
        'ViewAny:DeviceType', 'View:DeviceType', 'Create:DeviceType', 'Update:DeviceType',
        'ViewAny:Problem', 'View:Problem', 'Create:Problem', 'Update:Problem',
        'ViewAny:User', 'View:User',
        'ViewAny:Role', 'View:Role',
        'View:RepairOrdersBoard',
        'View:TodayOverviewWidget', 'View:LowStockPartsWidget', 'View:TechniciansWorkloadWidget',
    ];
}

function userWithRole(string $role): User
{
    $user = User::factory()->create();

    $user->assignRole($role);

    return $user->fresh();
}

test('blocks panel access for users without roles', function () {
    $plain = User::factory()->create();

    $this->actingAs($plain)->get('/admin')->assertForbidden();
});

test('allows panel access for managers, staff, and technicians', function () {
    foreach ([User::ROLE_MANAGER, User::ROLE_STAFF, User::ROLE_TECHNICIAN] as $role) {
        $this->actingAs(userWithRole($role))->get('/admin')->assertOk();

        auth()->logout();
    }
});

test('grants managers every current and future permission', function () {
    $manager = userWithRole(User::ROLE_MANAGER);

    Permission::create(['name' => 'Future:Something', 'guard_name' => 'web']);

    expect($manager->can('Delete:RepairOrder'))->toBeTrue()
        ->and($manager->can('ViewAny:User'))->toBeTrue()
        ->and($manager->can('viewAny', Payment::class))->toBeTrue()
        ->and($manager->can('Future:Something'))->toBeTrue();
});

test('lets staff run orders and payments without deleting or managing users', function () {
    $staff = userWithRole(User::ROLE_STAFF);
    $order = RepairOrder::factory()->create();

    expect($staff->can('viewAny', RepairOrder::class))->toBeTrue()
        ->and($staff->can('view', $order))->toBeTrue()
        ->and($staff->can('create', RepairOrder::class))->toBeTrue()
        ->and($staff->can('update', $order))->toBeTrue()
        ->and($staff->can('delete', $order))->toBeFalse()
        ->and($staff->can('viewAny', Payment::class))->toBeTrue()
        ->and($staff->can('create', Payment::class))->toBeTrue()
        ->and($staff->can('viewAny', User::class))->toBeFalse();
});

test('restricts technicians to their assigned orders without finance access', function () {
    $technician = userWithRole(User::ROLE_TECHNICIAN);
    $ownOrder = RepairOrder::factory()->create(['technician_id' => $technician->id]);
    $otherOrder = RepairOrder::factory()->create();
    $payment = Payment::factory()->create();

    expect($technician->can('view', $ownOrder))->toBeTrue()
        ->and($technician->can('update', $ownOrder))->toBeTrue()
        ->and($technician->can('view', $otherOrder))->toBeFalse()
        ->and($technician->can('update', $otherOrder))->toBeFalse()
        ->and($technician->can('delete', $ownOrder))->toBeFalse()
        ->and($technician->can('create', RepairOrder::class))->toBeFalse()
        ->and($technician->can('viewAny', Payment::class))->toBeFalse()
        ->and($technician->can('view', $payment))->toBeFalse()
        ->and($technician->can('viewAny', User::class))->toBeFalse();
});

test('denies plain users any resource or payment access', function () {
    $plain = User::factory()->create();
    $order = RepairOrder::factory()->create();
    $payment = Payment::factory()->create();

    expect($plain->can('viewAny', RepairOrder::class))->toBeFalse()
        ->and($plain->can('view', $order))->toBeFalse()
        ->and($plain->can('viewAny', Payment::class))->toBeFalse()
        ->and($plain->can('view', $payment))->toBeFalse();
});
