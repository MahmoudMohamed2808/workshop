<?php

namespace App\Models;

use App\Filament\Resources\RepairOrders\PaymentStatus;
use Database\Factories\RepairOrderFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * @property int $id
 * @property string $order_number
 * @property int $customer_id
 * @property int $device_type_id
 * @property int|null $brand_id
 * @property int|null $device_model_id
 * @property string|null $serial_number
 * @property string|null $device_condition
 * @property array|null $accessories
 * @property string|null $customer_complaint
 * @property string|null $technician_diagnosis
 * @property int|null $technician_id
 * @property string $status
 * @property string|null $rejection_reason
 * @property string|null $estimated_completion_date
 * @property string $labor_cost
 * @property string $additional_cost
 * @property string $discount
 * @property string $total_cost
 * @property Carbon|null $approved_at
 * @property Carbon|null $delivered_at
 * @property int|null $created_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
#[Fillable(['order_number', 'customer_id', 'device_type_id', 'brand_id', 'device_model_id', 'serial_number', 'device_condition', 'accessories', 'customer_complaint', 'technician_diagnosis', 'technician_id', 'status', 'rejection_reason', 'estimated_completion_date', 'labor_cost', 'additional_cost', 'discount', 'total_cost', 'approved_at', 'delivered_at', 'created_by'])]
class RepairOrder extends Model implements HasMedia
{
    /** @use HasFactory<RepairOrderFactory> */
    use HasFactory, InteractsWithMedia, SoftDeletes;

    /**
     * @var array<string, string>
     */
    protected $attributes = [
        'status' => 'received',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'accessories' => 'array',
            'estimated_completion_date' => 'date',
            'labor_cost' => 'decimal:2',
            'additional_cost' => 'decimal:2',
            'discount' => 'decimal:2',
            'total_cost' => 'decimal:2',
            'approved_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * @return BelongsTo<DeviceType, $this>
     */
    public function deviceType(): BelongsTo
    {
        return $this->belongsTo(DeviceType::class);
    }

    /**
     * @return BelongsTo<Brand, $this>
     */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    /**
     * @return BelongsTo<DeviceModel, $this>
     */
    public function deviceModel(): BelongsTo
    {
        return $this->belongsTo(DeviceModel::class, 'device_model_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return HasOne<Invoice>
     */
    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    /**
     * @return HasOne<Warranty>
     */
    public function warranty(): HasOne
    {
        return $this->hasOne(Warranty::class);
    }

    /**
     * @return HasMany<RepairOrderProblem>
     */
    public function orderProblems(): HasMany
    {
        return $this->hasMany(RepairOrderProblem::class);
    }

    /**
     * @return HasMany<RepairOrderPart>
     */
    public function orderParts(): HasMany
    {
        return $this->hasMany(RepairOrderPart::class);
    }

    /**
     * @return BelongsToMany<Problem>
     */
    public function problems(): BelongsToMany
    {
        return $this->belongsToMany(Problem::class, 'repair_order_problems')
            ->withPivot(['cost', 'notes'])
            ->withTimestamps();
    }

    /**
     * @return BelongsToMany<SparePart>
     */
    public function spareParts(): BelongsToMany
    {
        return $this->belongsToMany(SparePart::class, 'repair_order_parts')
            ->withPivot(['quantity', 'unit_price'])
            ->withTimestamps();
    }

    /**
     * @return HasMany<WhatsappMessage>
     */
    public function whatsappMessages(): HasMany
    {
        return $this->hasMany(WhatsappMessage::class);
    }

    /**
     * @return HasManyThrough<Payment>
     */
    public function payments(): HasManyThrough
    {
        return $this->hasManyThrough(Payment::class, Invoice::class);
    }

    /**
     * @param  Builder<RepairOrder>  $query
     */
    public function scopeStatus(Builder $query, string $status): void
    {
        $query->where('status', $status);
    }

    public function getPaymentStatusAttribute(): string
    {
        $invoice = $this->relationLoaded('invoice') ? $this->invoice : $this->invoice()->first();

        if ($invoice === null) {
            return PaymentStatus::NO_INVOICE;
        }

        return $invoice->payment_status;
    }

    /**
     * @param  Builder<RepairOrder>  $query
     */
    public function scopeWherePaymentStatus(Builder $query, string $status): void
    {
        match ($status) {
            PaymentStatus::NO_INVOICE => $query->whereDoesntHave('invoice'),
            PaymentStatus::PAID => $query->whereHas(
                'invoice',
                fn (Builder $invoice): Builder => $invoice->where('remaining_total', '<=', 0)->where('paid_total', '>', 0)
            ),
            PaymentStatus::PARTIAL => $query->whereHas(
                'invoice',
                fn (Builder $invoice): Builder => $invoice->where('paid_total', '>', 0)->where('remaining_total', '>', 0)
            ),
            PaymentStatus::UNPAID => $query->whereHas(
                'invoice',
                fn (Builder $invoice): Builder => $invoice->where('paid_total', '<=', 0)
            ),
            default => null,
        };
    }

    public function registerMediaCollections(): void
    {
        // Photo collections were removed from work orders; only the
        // invoice PDF collection on Invoice remains in use.
    }
}
