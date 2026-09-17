<?php

namespace App\Models;

use App\Filament\Resources\RepairOrders\PaymentStatus;
use Database\Factories\InvoiceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * @property int $id
 * @property string $invoice_number
 * @property int $repair_order_id
 * @property string $subtotal
 * @property string $discount
 * @property string $total
 * @property string $paid_total
 * @property string $remaining_total
 * @property Carbon|null $issued_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['invoice_number', 'repair_order_id', 'subtotal', 'discount', 'total', 'paid_total', 'remaining_total', 'issued_at'])]
class Invoice extends Model implements HasMedia
{
    /** @use HasFactory<InvoiceFactory> */
    use HasFactory, InteractsWithMedia;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'total' => 'decimal:2',
            'paid_total' => 'decimal:2',
            'remaining_total' => 'decimal:2',
            'issued_at' => 'datetime',
        ];
    }

    public function repairOrder(): BelongsTo
    {
        return $this->belongsTo(RepairOrder::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * @return Attribute<bool, never>
     */
    protected function isFullyPaid(): Attribute
    {
        return Attribute::get(fn (): bool => (float) $this->remaining_total <= 0 && (float) $this->paid_total > 0);
    }

    /**
     * @return Attribute<string, never>
     */
    protected function paymentStatus(): Attribute
    {
        return Attribute::get(fn (): string => PaymentStatus::forInvoice(
            (float) $this->total,
            (float) $this->paid_total,
            (float) $this->remaining_total,
        ));
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('invoice_pdf')->singleFile();
    }
}
