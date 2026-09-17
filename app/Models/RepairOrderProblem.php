<?php

namespace App\Models;

use Database\Factories\RepairOrderProblemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $repair_order_id
 * @property int $problem_id
 * @property string $cost
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['repair_order_id', 'problem_id', 'cost', 'notes'])]
class RepairOrderProblem extends Model
{
    /** @use HasFactory<RepairOrderProblemFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'cost' => 'decimal:2',
        ];
    }

    public function repairOrder(): BelongsTo
    {
        return $this->belongsTo(RepairOrder::class);
    }

    public function problem(): BelongsTo
    {
        return $this->belongsTo(Problem::class);
    }
}
