<?php

namespace App\Services;

use App\Models\Brand;
use App\Models\DeviceModel;
use App\Models\DeviceType;
use App\Models\Problem;
use Illuminate\Support\Collection;

class ProblemUsageService
{
    public function incrementUsage(Problem $problem): void
    {
        $problem->increment('usage_count');
    }

    public function mostUsedFor(DeviceType $type, ?Brand $brand = null, ?DeviceModel $model = null, int $limit = 5): Collection
    {
        return Problem::query()
            ->active()
            ->mostUsed()
            ->where('device_type_id', $type->id)
            ->when($brand !== null, function ($query) use ($brand) {
                $query->where(function ($scoped) use ($brand) {
                    $scoped->where('brand_id', $brand->id)->orWhereNull('brand_id');
                });
            })
            ->when($model !== null, function ($query) use ($model) {
                $query->where(function ($scoped) use ($model) {
                    $scoped->where('device_model_id', $model->id)->orWhereNull('device_model_id');
                });
            })
            ->limit($limit)
            ->get();
    }
}
