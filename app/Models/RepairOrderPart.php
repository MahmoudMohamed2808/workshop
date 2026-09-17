<?php

namespace App\Models;

use Database\Factories\RepairOrderPartFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $repair_order_id
 * @property int $spare_part_id
 * @property int $quantity
 * @property string $unit_price
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['repair_order_id', 'spare_part_id', 'quantity', 'unit_price'])]
class RepairOrderPart extends Model
{
    /** @use HasFactory<RepairOrderPartFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
        ];
    }

    public function repairOrder(): BelongsTo
    {
        return $this->belongsTo(RepairOrder::class);
    }

    public function sparePart(): BelongsTo
    {
        return $this->belongsTo(SparePart::class);
    }
}
