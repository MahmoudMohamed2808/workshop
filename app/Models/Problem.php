<?php

namespace App\Models;

use Database\Factories\ProblemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;
use Spatie\Tags\HasTags;

/**
 * @property int $id
 * @property int $device_type_id
 * @property int|null $brand_id
 * @property int|null $device_model_id
 * @property string $name
 * @property string|null $description
 * @property array<int,string>|null $suggested_accessories
 * @property int|null $suggested_technician_id
 * @property string|null $default_cost_min
 * @property string|null $default_cost_max
 * @property int $usage_count
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['device_type_id', 'brand_id', 'device_model_id', 'name', 'description', 'suggested_accessories', 'suggested_technician_id', 'default_cost_min', 'default_cost_max', 'usage_count', 'is_active'])]
class Problem extends Model
{
    /** @use HasFactory<ProblemFactory> */
    use HasFactory, HasTags;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'suggested_accessories' => 'array',
            'default_cost_min' => 'decimal:2',
            'default_cost_max' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return BelongsToMany<SparePart>
     */
    public function suggestedParts(): BelongsToMany
    {
        return $this->belongsToMany(SparePart::class, 'problem_spare_part')
            ->withPivot(['default_quantity'])
            ->withTimestamps();
    }

    public function suggestedTechnician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'suggested_technician_id');
    }

    public function deviceType(): BelongsTo
    {
        return $this->belongsTo(DeviceType::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function deviceModel(): BelongsTo
    {
        return $this->belongsTo(DeviceModel::class, 'device_model_id');
    }

    /**
     * @param  Builder<Problem>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * @param  Builder<Problem>  $query
     */
    public function scopeMostUsed(Builder $query): void
    {
        $query->orderByDesc('usage_count');
    }

    /**
     * @return Attribute<string|null, never>
     */
    protected function defaultCostRange(): Attribute
    {
        return Attribute::get(function (): ?string {
            if ($this->default_cost_min === null && $this->default_cost_max === null) {
                return null;
            }

            return $this->default_cost_min.'–'.$this->default_cost_max;
        });
    }
}
