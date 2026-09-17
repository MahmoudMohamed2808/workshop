<?php

use App\Models\Customer;
use App\Models\RepairOrder;
use App\Services\WhatsappService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('sends a plain message', function () {
    $customer = Customer::factory()->create();

    $message = app(WhatsappService::class)->send($customer, 'مرحبًا بكم في مركز الصيانة');

    expect($message->type)->toBe('other')
        ->and($message->status)->toBe('sent')
        ->and($message->sent_at)->not->toBeNull();
});

test('renders the intake template in arabic', function () {
    $customer = Customer::factory()->create();
    $order = RepairOrder::factory()->create(['customer_id' => $customer->id]);

    $message = app(WhatsappService::class)->sendTemplate($customer, 'intake', [
        'device' => 'التلاجة',
        'order' => $order->order_number,
    ], $order);

    expect($message->type)->toBe('intake')
        ->and($message->repair_order_id)->toBe($order->id)
        ->and($message->message)->toContain($order->order_number)
        ->and($message->status)->toBe('sent');
});

test('retries a failed message', function () {
    $service = app(WhatsappService::class);
    $customer = Customer::factory()->create();

    $message = $customer->whatsappMessages()->create([
        'type' => 'ready',
        'message' => 'جهازك جاهز',
        'status' => 'failed',
        'error_message' => 'provider timeout',
    ]);

    $retried = $service->resend($message);

    expect($retried->status)->toBe('sent')
        ->and($retried->error_message)->toBeNull();
});

test('marks messages failed with the error', function () {
    $service = app(WhatsappService::class);
    $customer = Customer::factory()->create();

    $message = $customer->whatsappMessages()->create([
        'type' => 'other',
        'message' => 'test',
        'status' => 'pending',
    ]);

    $service->markFailed($message, 'provider timeout');

    expect($message->fresh()->status)->toBe('failed')
        ->and($message->fresh()->error_message)->toBe('provider timeout');
});
