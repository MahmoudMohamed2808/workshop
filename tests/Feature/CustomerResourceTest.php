<?php

use App\Filament\Resources\Customers\Pages\ViewCustomer;
use App\Models\Customer;
use Livewire\Livewire;

beforeEach(function () {
    seedPanelRoles();
});

it('lists customers with Arabic labels', function () {
    $user = staffPanelUser();
    Customer::factory()->create(['name' => 'عميل اختبار']);

    $this->actingAs($user)
        ->get('/admin/customers')
        ->assertOk()
        ->assertSee('العملاء', false)
        ->assertSee('رقم الهاتف', false);
});

it('shows the customer view page with profile and summary stats', function () {
    $user = staffPanelUser();
    $customer = Customer::factory()->create();

    $this->actingAs($user)
        ->get("/admin/customers/{$customer->getRouteKey()}")
        ->assertOk()
        ->assertSee('بيانات العميل', false)
        ->assertSee('ملخص النشاط', false)
        ->assertSee('إجمالي المدفوع', false);
});

it('sends a WhatsApp message from the view page action', function () {
    $user = staffPanelUser();
    $customer = Customer::factory()->create();

    Livewire::actingAs($user)
        ->test(ViewCustomer::class, ['record' => $customer->getRouteKey()])
        ->callAction('sendWhatsappMessage', data: ['message' => 'رسالة اختبار'])
        ->assertHasNoActionErrors();

    expect($customer->whatsappMessages()->count())->toBe(1)
        ->and($customer->whatsappMessages()->first()->message)->toBe('رسالة اختبار')
        ->and($customer->whatsappMessages()->first()->status)->toBe('sent');
});
