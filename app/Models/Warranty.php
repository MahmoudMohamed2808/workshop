<?php

namespace App\Models;

use Database\Factories\WarrantyFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $repair_order_id
 * @property int $duration_days
 * @property string $starts_at
 * @property string $ends_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['repair_order_id', 'duration_days', 'starts_at', 'ends_at'])]
class Warranty extends Model
{
    /** @use HasFactory<WarrantyFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'starts_at' => 'date',
            'ends_at' => 'date',
        ];
    }

    public function repairOrder(): BelongsTo
    {
        return $this->belongsTo(RepairOrder::class);
    }

    /**
     * @param  Builder<Warranty>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('ends_at', '>=', now()->toDateString());
    }

    /**
     * @return Attribute<bool, never>
     */
    protected function isExpired(): Attribute
    {
        return Attribute::get(fn (): bool => $this->ends_at < now()->toDateString());
    }
}
