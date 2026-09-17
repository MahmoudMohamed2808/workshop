<?php

namespace Database\Seeders;

use App\Models\RepairOrder;
use App\Models\Warranty;
use Illuminate\Database\Seeder;

class WarrantySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * ضمان لكل أمر مسلم (بعضها ساري وبعضها منتهي عشان اختبار السكوبات).
     */
    public function run(): void
    {
        if (Warranty::count() > 0) {
            return;
        }

        $durations = [30, 30, 30, 60, 60, 90, 90, 180];

        RepairOrder::where('status', 'delivered')->orderBy('id')->chunkById(100, function ($orders) use ($durations): void {
            foreach ($orders as $order) {
                // 85% من المسلم ليه ضمان مسجل
                if (random_int(1, 100) > 85) {
                    continue;
                }

                $days = $durations[array_rand($durations)];
                // الضمان بيبدأ من التسليم الفعلي عشان المنتهي/الساري يطلع طبيعي
                $startsAt = ($order->delivered_at ?? $order->updated_at ?? now())->copy()->startOfDay();

                Warranty::updateOrCreate(
                    ['repair_order_id' => $order->id],
                    [
                        'duration_days' => $days,
                        'starts_at' => $startsAt->toDateString(),
                        'ends_at' => $startsAt->copy()->addDays($days)->toDateString(),
                    ]
                );
            }
        });
    }
}
