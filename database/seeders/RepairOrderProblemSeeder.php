<?php

namespace Database\Seeders;

use App\Models\Problem;
use App\Models\RepairOrder;
use App\Models\RepairOrderProblem;
use Illuminate\Database\Seeder;

class RepairOrderProblemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * بيركب 1-3 أعطال على كل أمر شغل (حسب حالته) من نفس نوع الجهاز،
     * وسعر المصنعية من رينج العطل نفسه عشان الأرقام تبقى منطقية.
     */
    public function run(): void
    {
        if (RepairOrderProblem::count() > 0) {
            return;
        }

        $noProblemStatuses = ['received', 'cancelled'];
        $notes = [
            null, null,
            'العطل متكرر عند العميل للمرة التانية',
            'تمت المعاينة قدام العميل ووافق على التكلفة',
            'العطل شامل الفك والتركيب والاختبار',
            'محتاج متابعة بعد أسبوع من التسليم',
            'العميل مستعجل وعايز الجهاز بسرعة',
        ];

        $problemsByType = Problem::where('is_active', true)->get()->groupBy('device_type_id');

        RepairOrder::with('deviceType')->chunkById(100, function ($orders) use ($problemsByType, $notes, $noProblemStatuses): void {
            foreach ($orders as $order) {
                if (in_array($order->status, $noProblemStatuses, true)) {
                    continue;
                }

                $pool = $problemsByType->get($order->device_type_id, collect());
                if ($pool->isEmpty()) {
                    $pool = Problem::where('is_active', true)->limit(20)->get();
                }
                if ($pool->isEmpty()) {
                    continue;
                }

                // الطلبات المستلمة حديثًا عطل واحد أو بدون، والباقي 1-3
                $max = $order->status === 'received' ? 1 : 3;
                $count = $order->status === 'received' && random_int(1, 100) <= 30 ? 0 : random_int(1, $max);
                $picked = $pool->shuffle()->take($count);

                foreach ($picked as $problem) {
                    $min = (float) ($problem->default_cost_min ?? 150);
                    $maxCost = (float) ($problem->default_cost_max ?? $min + 400);
                    if ($maxCost < $min) {
                        [$min, $maxCost] = [$maxCost, $min];
                    }

                    $cost = round(random_int((int) $min, (int) $maxCost) / 10) * 10;

                    $order->orderProblems()->create([
                        'problem_id' => $problem->id,
                        'cost' => $cost,
                        'notes' => $notes[array_rand($notes)],
                    ]);

                    $problem->increment('usage_count');
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
