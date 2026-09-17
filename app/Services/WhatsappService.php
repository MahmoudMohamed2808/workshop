<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\RepairOrder;
use App\Models\WhatsappMessage;
use Illuminate\Support\Facades\Log;

class WhatsappService
{
    public function send(Customer $customer, string $message, ?string $mediaUrl = null): WhatsappMessage
    {
        $record = $customer->whatsappMessages()->create([
            'type' => 'other',
            'message' => $message,
            'media_url' => $mediaUrl,
            'status' => 'pending',
        ]);

        return $this->dispatch($record);
    }

    public function sendTemplate(Customer $customer, string $type, array $data, ?RepairOrder $order = null): WhatsappMessage
    {
        $record = $customer->whatsappMessages()->create([
            'repair_order_id' => $order?->id,
            'type' => $type,
            'message' => __('whatsapp.'.$type, $data, 'ar'),
            'status' => 'pending',
        ]);

        return $this->dispatch($record);
    }

    public function sendMedia(Customer $customer, string $fileUrl, string $caption): void
    {
        $record = $customer->whatsappMessages()->create([
            'type' => 'other',
            'message' => $caption,
            'media_url' => $fileUrl,
            'status' => 'pending',
        ]);

        $this->dispatch($record);
    }

    public function resend(WhatsappMessage $message): WhatsappMessage
    {
        if ($message->status === 'sent') {
            return $message;
        }

        $message->update(['status' => 'pending', 'error_message' => null]);

        return $this->dispatch($message->fresh());
    }

    public function markSent(WhatsappMessage $message): void
    {
        $message->update([
            'status' => 'sent',
            'error_message' => null,
            'sent_at' => now(),
        ]);
    }

    public function markFailed(WhatsappMessage $message, string $error): void
    {
        $message->update([
            'status' => 'failed',
            'error_message' => $error,
        ]);
    }

    protected function dispatch(WhatsappMessage $message): WhatsappMessage
    {
        // No live provider is wired in this environment: the log driver
        // records the outbound message and marks it sent, mirroring
        // Laravel's mail log driver. A real Twilio/Meta driver slots in here.
        if (config('services.whatsapp.driver', 'log') === 'log') {
            Log::info('WhatsApp message dispatched.', [
                'id' => $message->id,
                'to' => $message->customer->phone,
                'type' => $message->type,
            ]);

            $this->markSent($message);

            return $message->fresh();
        }

        $this->markFailed($message, 'No WhatsApp provider configured.');

        return $message->fresh();
    }
}
