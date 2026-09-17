<?php

namespace App\Models;

use Database\Factories\WhatsappMessageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $customer_id
 * @property int|null $repair_order_id
 * @property string $type
 * @property string $message
 * @property string|null $media_url
 * @property string $status
 * @property string|null $error_message
 * @property Carbon|null $sent_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['customer_id', 'repair_order_id', 'type', 'message', 'media_url', 'status', 'error_message', 'sent_at'])]
class WhatsappMessage extends Model
{
    /** @use HasFactory<WhatsappMessageFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function repairOrder(): BelongsTo
    {
        return $this->belongsTo(RepairOrder::class);
    }

    /**
     * @param  Builder<WhatsappMessage>  $query
     */
    public function scopeFailed(Builder $query): void
    {
        $query->where('status', 'failed');
    }

    /**
     * @param  Builder<WhatsappMessage>  $query
     */
    public function scopePending(Builder $query): void
    {
        $query->where('status', 'pending');
    }
}
