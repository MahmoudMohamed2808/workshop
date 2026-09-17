<?php

namespace Database\Seeders;

use App\Models\RepairOrder;
use App\Models\RepairOrderPart;
use App\Models\SparePart;
use Illuminate\Database\Seeder;

class RepairOrderPartSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * قطع الغيار بتتركب على الأوامر اللي عدت مرحلة الموافقة،
     * وبسعر البيع المسجل على قطعة الغيار عشان الحسابات تطلع مظبوطة.
     */
    public function run(): void
    {
        if (RepairOrderPart::count() > 0) {
            return;
        }

        // خريطة تقريبية: أنهي قطع غيار مناسبة لأنهي نوع جهاز
        $partHints = [
            'refrigerator' => ['TLG'],
            'washing-machine' => ['GSL'],
            'blender' => ['KHL'],
            'water-motor' => ['MTR'],
            'air-conditioner' => ['TKF'],
            'fan' => ['MRW'],
            'water-heater' => ['SKH'],
            'microwave' => ['MKW'],
        ];

        $generic = SparePart::whereIn('code', ['PRT-GEN-001', 'PRT-GEN-002', 'PRT-GEN-003', 'PRT-GEN-004'])->get();
        $byPrefix = SparePart::all()->groupBy(fn ($part) => explode('-', $part->code)[1] ?? 'GEN');

        $eligible = ['in_repair', 'ready_for_delivery', 'delivered'];

        RepairOrder::with('deviceType')->whereIn('status', $eligible)->chunkById(100, function ($orders) use ($partHints, $byPrefix, $generic): void {
            foreach ($orders as $order) {
                // 75% من الأوامر المؤهلة بتحتاج قطع غيار
                if (random_int(1, 100) > 75) {
                    $this->recalculate($order);

                    continue;
                }

                $prefixes = $partHints[$order->deviceType->slug] ?? [];
                $pool = collect();
                foreach ($prefixes as $prefix) {
                    $pool = $pool->merge($byPrefix->get($prefix, collect()));
                }
                // 30% فرصة إضافة خامات عامة (سلك/مسامير/لحام)
                if (random_int(1, 100) <= 30) {
                    $pool = $pool->merge($generic);
                }
                if ($pool->isEmpty()) {
                    $pool = SparePart::limit(20)->get();
                }

                $picked = $pool->shuffle()->take(random_int(1, 3));
                foreach ($picked as $part) {
                    $order->orderParts()->create([
                        'spare_part_id' => $part->id,
                        'quantity' => random_int(1, 2),
                        'unit_price' => $part->selling_price,
                    ]);
                }

                $this->recalculate($order);
            }
        });
    }

    private function recalculate(RepairOrder $order): void
    {
        $problemsTotal = (float) $order->orderProblems()->sum('cost');
        $partsTotal = (float) $order->orderParts()->selectRaw('COALESCE(SUM(quantity * unit_price), 0) as aggregate')->value('aggregate');
        $order->total_cost = max(0, $problemsTotal + $partsTotal + (float) $order->labor_cost + (float) $order->additional_cost - (float) $order->discount);
        $order->timestamps = false;
        $order->save();
        $order->timestamps = true;
    }
}
