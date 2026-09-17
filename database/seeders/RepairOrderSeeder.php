<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Customer;
use App\Models\DeviceModel;
use App\Models\DeviceType;
use App\Models\RepairOrder;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class RepairOrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * 450 أمر صيانة موزعة على 6 شهور بكل الحالات.
     * التكاليف التفصيلية (الأعطال/قطع الغيار) تتركب في السيدرز التالية
     * وهنا بنسجل أتعاب التشخيص الأولية فقط.
     */
    public function run(): void
    {
        if (RepairOrder::count() > 0) {
            return;
        }

        $customerIds = Customer::pluck('id')->all();
        $technicians = User::role(User::ROLE_TECHNICIAN)->pluck('id')->all();
        $staff = User::role(User::ROLE_STAFF)->pluck('id')->all();
        $admins = User::role(User::ROLE_MANAGER)->pluck('id')->all();
        $creators = array_merge($staff, $admins);

        if (empty($customerIds) || empty($technicians) || empty($creators)) {
            $this->command->warn('RepairOrderSeeder: users/customers missing, skipping.');

            return;
        }

        $types = DeviceType::with('brands.deviceModels')->get()->keyBy('slug');

        $complaints = [
            'refrigerator' => ['التلاجة مش بتسقع والحاجة بتبوظ فيها', 'في مياه بتنزل من تحت التلاجة', 'التلاجة صوتها عالي أوي بالليل ومش عارفين ننام', 'الفريزر عامل تلج كتير والباب مش بيقفل', 'التلاجة فصلت مرة واحدة ومش راضية تقوم', 'الكهربا بتكهربنا لما بنلمس التلاجة'],
            'washing-machine' => ['الغسالة مش بتعصر والهدوم بتطلع مبلولة', 'الغسالة بتترج جامد وبتتحرك من مكانها في العصر', 'المياه بتنزل من باب الغسالة على الأرض', 'الغسالة مش بتسحب مياه وواقفة على أول البرنامج', 'الباب مش بيفتح بعد ما الغسيل يخلص', 'الغسالة بتطلع ريحة وحشة في الهدوم'],
            'blender' => ['الخلاط مش شغال خالص', 'الخلاط بيطلع ريحة شياط لما نشغله', 'الشفشق اتكسر وعايزين واحد جديد', 'الخلاط بيسرب من تحت وبيغرق الموتور', 'السرعات مش شغالة والزرار بيعلق'],
            'water-motor' => ['الموتور شغال بس مفيش مياه طالعة', 'الموتور بيشتغل ويفصل لوحده كل شوية', 'صوت الموتور عالي أوي وفي خشونة', 'المياه ضعيفة في الدور التالت', 'الموتور بينقط مياه من تحت'],
            'air-conditioner' => ['التكييف مش بيسقع والهوا طالع سخن', 'التكييف بينقط مياه جوه الأوضة', 'الوحدة الخارجية مش شغالة خالص', 'التكييف بيطلع ريحة مكمكمة', 'التكييف بيفصل لوحده في عز الحر', 'عايزين غسيل وصيانة قبل الصيف'],
            'fan' => ['المروحة مش شغالة خالص', 'المروحة بطيئة حتى على أعلى سرعة', 'المروحة بتعمل صوت حكة مزعج', 'المروحة مش بتلف يمين وشمال'],
            'water-heater' => ['السخان مش بيسخن والمياه ساقعة', 'السخان بينقط مياه من تحت', 'السخان بيكهرب في المياه', 'السخان بيفصل بسرعة والمياه مش بتسخن كويس'],
            'microwave' => ['الميكروويف مش بيسخن الأكل', 'الطبق مش بيلف جوه الميكروويف', 'في شرز وصوت فرقعة لما نشغله', 'الميكروويف فاصل ومش بينور خالص'],
        ];

        $diagnoses = [
            'كشفت على الجهاز ولقيت {x}، محتاج يتغير والجهاز يرجع زي الفل',
            'العطل واضح: {x}، والتكلفة تشمل الفك والتركيب والاختبار',
            'بعد القياس بالأفوميتر تبين إن {x}، وهنحتاج قطعة غيار أصلية',
            'الجهاز محتاج {x}، ونضفنا الدايرة كلها وبقى تمام',
        ];

        $conditions = ['الجهاز حالته كويسة بس العطل المذكور', 'في خدوش بسيطة في الجسم الخارجي', 'الجهاز متهالك شوية ومحتاج تنضيف شامل', 'الجسم سليم ومفيش كسور، العطل داخلي فقط', 'في صدأ بسيط من تحت ومحتاج عزل', 'الجهاز كان متفكك قبل كده عند حد تاني'];
        $accessoriesByType = [
            'refrigerator' => [['رفوف', 'أدراج'], ['كتيب الضمان'], ['رفوف', 'أدراج', 'علب البيض'], []],
            'washing-machine' => [['خرطوم الصرف'], ['خرطوم المياه', 'الكتالوج'], [], ['درج المسحوق']],
            'blender' => [['المطحنة'], ['الشفشق الاحتياطي'], [], ['الغطا + المطحنة']],
            'water-motor' => [[], [], ['الأوتوماتيك القديم'], ['محبس + وصلات']],
            'air-conditioner' => [['الريموت'], ['الريموت', 'الحجارة'], ['الكتالوج'], []],
            'fan' => [[], ['الريموت'], ['عامود التطويل']],
            'water-heater' => [[], ['الوصلات المرنة'], ['الكتالوج']],
            'microwave' => [['الطبق الزجاج'], ['الطبق + الحلقة الدوارة'], []],
        ];

        $rejectionReasons = ['العميل رفض التكلفة بعد المقايسة', 'تكلفة الإصلاح أعلى من قيمة الجهاز والعميل فضل يشتري جديد', 'مفيش قطع غيار متوفرة للموديل القديم ده', 'الجهاز محروق بالكامل ومش مستاهل تصليح', 'العميل لغى الطلب لظروف شخصية'];
        $beyondReasons = ['الكباس محروق والبودي متهالك، الجهاز خارج الخدمة', 'الحلة مخرومة والكارتة محروقة، التصليح مش مجدي', 'الملفات محروقة والهيكل مصدي بالكامل'];

        // توزيع الحالات: delivered هو الغالب عشان الفواتير والضمانات
        $statusPool = array_merge(
            array_fill(0, 180, 'delivered'),
            array_fill(0, 45, 'ready_for_delivery'),
            array_fill(0, 120, 'in_repair'),
            array_fill(0, 50, 'received'),
            array_fill(0, 30, 'cancelled'),
            array_fill(0, 25, 'beyond_repair'),
        );
        shuffle($statusPool);

        $slugs = $types->keys()->all();
        // أوزان واقعية: التلاجة والغسالة والتكييف هما الشغل الأكبر
        $weightedSlugs = ['refrigerator', 'refrigerator', 'refrigerator', 'washing-machine', 'washing-machine', 'washing-machine', 'air-conditioner', 'air-conditioner', 'water-motor', 'water-motor', 'blender', 'fan', 'water-heater', 'microwave'];

        $now = Carbon::now();
        $counter = 1;

        foreach ($statusPool as $status) {
            $slug = $weightedSlugs[array_rand($weightedSlugs)] ?? $slugs[array_rand($slugs)];
            $type = $types[$slug] ?? $types->first();
            $brand = $type->brands->isNotEmpty() ? $type->brands->random() : Brand::inRandomOrder()->first();
            $model = null;
            if ($brand && $brand->deviceModels->isNotEmpty() && random_int(1, 100) <= 90) {
                $model = $brand->deviceModels->random();
            } elseif ($brand) {
                $model = DeviceModel::where('brand_id', $brand->id)->inRandomOrder()->first();
            }

            $createdAt = $now->copy()->subDays(random_int(0, 180))->subHours(random_int(0, 20));
            $needsWork = ! in_array($status, ['received', 'cancelled'], true);
            $hasDiagnosis = in_array($status, ['in_repair', 'ready_for_delivery', 'delivered'], true);

            $labor = $needsWork ? [150, 200, 250, 300, 350, 400, 500][array_rand([150, 200, 250, 300, 350, 400, 500])] : 0;
            $additional = ($needsWork && random_int(1, 100) <= 30) ? random_int(50, 300) : 0;
            $discount = ($needsWork && random_int(1, 100) <= 22) ? random_int(50, 250) : 0;

            $complaintList = $complaints[$type->slug] ?? ['الجهاز عطلان ومحتاج صيانة'];
            $accList = $accessoriesByType[$type->slug] ?? [[]];

            $diagnosis = null;
            if ($hasDiagnosis) {
                $template = $diagnoses[array_rand($diagnoses)];
                $hints = ['الترموستات ضعيف', 'الطلمبة مسدودة', 'المكثف ضعيف', 'الكارتة فيها مشكلة', 'السخان فاصل', 'السير متاكل'];
                $diagnosis = str_replace('{x}', $hints[array_rand($hints)], $template).'. العميل اتبلغ بالتكلفة ووافق.';
            }

            $approvedAt = in_array($status, ['in_repair', 'ready_for_delivery', 'delivered'], true)
                ? $createdAt->copy()->addDays(random_int(0, 3)) : null;
            $deliveredAt = $status === 'delivered' ? ($approvedAt ?? $createdAt)->copy()->addDays(random_int(1, 8)) : null;

            $order = RepairOrder::create([
                'order_number' => sprintf('RO-2026-%06d', $counter++),
                'customer_id' => $customerIds[array_rand($customerIds)],
                'device_type_id' => $type->id,
                'brand_id' => $brand?->id,
                'device_model_id' => $model?->id,
                'serial_number' => random_int(1, 100) <= 65 ? strtoupper(substr($brand?->name ?? 'GEN', 0, 2)).random_int(10000000, 99999999) : null,
                'device_condition' => $conditions[array_rand($conditions)],
                'accessories' => $accList[array_rand($accList)],
                'customer_complaint' => $complaintList[array_rand($complaintList)],
                'technician_diagnosis' => $diagnosis,
                'technician_id' => $technicians[array_rand($technicians)],
                'status' => $status,
                'rejection_reason' => $status === 'beyond_repair' ? $beyondReasons[array_rand($beyondReasons)] : ($status === 'cancelled' && random_int(1, 100) <= 60 ? 'العميل لغى الطلب قبل الكشف' : null),
                'estimated_completion_date' => $needsWork ? $createdAt->copy()->addDays(random_int(2, 7))->toDateString() : null,
                'labor_cost' => $labor,
                'additional_cost' => $additional,
                'discount' => $discount,
                'total_cost' => max(0, $labor + $additional - $discount),
                'approved_at' => $approvedAt,
                'delivered_at' => $deliveredAt,
                'created_by' => $creators[array_rand($creators)],
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            // تنويع تواريخ التحديث
            $order->timestamps = false;
            $order->updated_at = $deliveredAt ?? $approvedAt ?? $createdAt->copy()->addDay();
            $order->save();
            $order->timestamps = true;
        }
    }
}
