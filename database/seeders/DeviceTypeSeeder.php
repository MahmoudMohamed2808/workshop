<?php

namespace Database\Seeders;

use App\Models\DeviceType;
use Illuminate\Database\Seeder;

class DeviceTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            ['name' => 'تلاجة', 'slug' => 'refrigerator'],
            ['name' => 'غسالة ملابس', 'slug' => 'washing-machine'],
            ['name' => 'خلاط', 'slug' => 'blender'],
            ['name' => 'موتور مياه', 'slug' => 'water-motor'],
            ['name' => 'تكييف', 'slug' => 'air-conditioner'],
            ['name' => 'مروحة', 'slug' => 'fan'],
            ['name' => 'سخان كهربا', 'slug' => 'water-heater'],
            ['name' => 'ميكروويف', 'slug' => 'microwave'],
        ];

        foreach ($types as $type) {
            DeviceType::updateOrCreate(
                ['slug' => $type['slug']],
                ['name' => $type['name'], 'is_active' => true]
            );
        }
    }
}
