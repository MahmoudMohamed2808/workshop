<?php

namespace App\Models;

use Database\Factories\DeviceModelFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $brand_id
 * @property string $name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['brand_id', 'name'])]
class DeviceModel extends Model
{
    /** @use HasFactory<DeviceModelFactory> */
    use HasFactory;

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function problems(): HasMany
    {
        return $this->hasMany(Problem::class, 'device_model_id');
    }
}
