<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\DeviceType;
use Illuminate\Database\Seeder;

class BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * ماركات منتشرة فعلا في السوق المصري لكل نوع جهاز.
     */
    public function run(): void
    {
        $map = [
            'refrigerator' => ['توشيبا', 'شارب', 'إل جي', 'سامسونج', 'فريش', 'كريازي', 'إيديال زانوسي', 'بيكو'],
            'washing-machine' => ['إيديال زانوسي', 'توشيبا', 'إل جي', 'سامسونج', 'فريش', 'هوفر', 'وايت بوينت', 'أريستون'],
            'blender' => ['مولينكس', 'براون', 'كينوود', 'فريش', 'توشيبا', 'تورنيدو', 'فيليبس'],
            'water-motor' => ['كالبيدا', 'سيتي', 'لينز', 'ساير', 'بيدرولو', 'توتال'],
            'air-conditioner' => ['شارب', 'إل جي', 'سامسونج', 'فريش', 'كاريير', 'ميديا', 'يونيون إير', 'تورنيدو'],
            'fan' => ['فريش', 'توشيبا', 'تورنيدو', 'باناسونيك', 'جاك'],
            'water-heater' => ['أوليمبيك', 'فريش', 'تورنيدو', 'أريستون', 'وايت بوينت'],
            'microwave' => ['شارب', 'إل جي', 'سامسونج', 'فريش', 'تورنيدو', 'دايو'],
        ];

        foreach ($map as $slug => $brands) {
            $type = DeviceType::where('slug', $slug)->first();

            if (! $type) {
                continue;
            }

            foreach ($brands as $index => $name) {
                Brand::updateOrCreate(
                    ['device_type_id' => $type->id, 'name' => $name],
                    ['is_active' => $index < count($brands) - 1] // آخر ماركة موقوفة للتجربة
                );
            }
        }
    }
}
