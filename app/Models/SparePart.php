<?php

namespace App\Models;

use Database\Factories\SparePartFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string $purchase_price
 * @property string $selling_price
 * @property int $quantity
 * @property int $minimum_stock
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['code', 'name', 'purchase_price', 'selling_price', 'quantity', 'minimum_stock'])]
class SparePart extends Model
{
    /** @use HasFactory<SparePartFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'purchase_price' => 'decimal:2',
            'selling_price' => 'decimal:2',
        ];
    }

    /**
     * @param  Builder<SparePart>  $query
     */
    public function scopeLowStock(Builder $query): void
    {
        $query->whereColumn('quantity', '<=', 'minimum_stock');
    }

    /**
     * @return HasMany<RepairOrderPart>
     */
    public function orderParts(): HasMany
    {
        return $this->hasMany(RepairOrderPart::class);
    }

    /**
     * @return BelongsToMany<RepairOrder>
     */
    public function repairOrders(): BelongsToMany
    {
        return $this->belongsToMany(RepairOrder::class, 'repair_order_parts')
            ->withPivot(['quantity', 'unit_price'])
            ->withTimestamps();
    }

    /**
     * @return BelongsToMany<Problem>
     */
    public function suggestedForProblems(): BelongsToMany
    {
        return $this->belongsToMany(Problem::class, 'problem_spare_part')
            ->withPivot(['default_quantity'])
            ->withTimestamps();
    }

    /**
     * @return Attribute<bool, never>
     */
    protected function isLowStock(): Attribute
    {
        return Attribute::get(fn (): bool => $this->quantity <= $this->minimum_stock);
    }
}
