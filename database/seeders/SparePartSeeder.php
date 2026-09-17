<?php

namespace Database\Seeders;

use App\Models\SparePart;
use Illuminate\Database\Seeder;

class SparePartSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * قطع غيار حقيقية بأسعار سوق (جنيه مصري) وكميات متنوعة
     * فيها نواقص مخزون عشان اختبار تنبيهات المخزون.
     */
    public function run(): void
    {
        $parts = [
            // تلاجات
            ['code' => 'PRT-TLG-001', 'name' => 'موتور (كباس) تلاجة 1/4 حصان', 'purchase_price' => 2800, 'selling_price' => 3500, 'quantity' => 12, 'minimum_stock' => 4],
            ['code' => 'PRT-TLG-002', 'name' => 'ترموستات تلاجة نوفروست', 'purchase_price' => 180, 'selling_price' => 280, 'quantity' => 45, 'minimum_stock' => 10],
            ['code' => 'PRT-TLG-003', 'name' => 'تايمر نوفروست', 'purchase_price' => 150, 'selling_price' => 240, 'quantity' => 38, 'minimum_stock' => 8],
            ['code' => 'PRT-TLG-004', 'name' => 'سخان (هيتر) تلاجة نوفروست', 'purchase_price' => 220, 'selling_price' => 350, 'quantity' => 30, 'minimum_stock' => 8],
            ['code' => 'PRT-TLG-005', 'name' => 'مروحة فريزر داخلية', 'purchase_price' => 260, 'selling_price' => 400, 'quantity' => 25, 'minimum_stock' => 6],
            ['code' => 'PRT-TLG-006', 'name' => 'ريلاي + أوفرلود موتور', 'purchase_price' => 90, 'selling_price' => 150, 'quantity' => 60, 'minimum_stock' => 15],
            ['code' => 'PRT-TLG-007', 'name' => 'فلتر + شحن فريون', 'purchase_price' => 300, 'selling_price' => 550, 'quantity' => 40, 'minimum_stock' => 10],
            ['code' => 'PRT-TLG-008', 'name' => 'كاوتش باب تلاجة (طقم)', 'purchase_price' => 350, 'selling_price' => 550, 'quantity' => 20, 'minimum_stock' => 5],
            ['code' => 'PRT-TLG-009', 'name' => 'كارتة تلاجة إنفرتر', 'purchase_price' => 1400, 'selling_price' => 1900, 'quantity' => 3, 'minimum_stock' => 5],
            ['code' => 'PRT-TLG-010', 'name' => 'حساس (سينسور) تلاجة', 'purchase_price' => 120, 'selling_price' => 200, 'quantity' => 50, 'minimum_stock' => 12],
            ['code' => 'PRT-TLG-011', 'name' => 'رف زجاج تلاجة', 'purchase_price' => 280, 'selling_price' => 420, 'quantity' => 15, 'minimum_stock' => 5],
            ['code' => 'PRT-TLG-012', 'name' => 'مقبض باب تلاجة', 'purchase_price' => 140, 'selling_price' => 230, 'quantity' => 22, 'minimum_stock' => 6],
            // غسالات
            ['code' => 'PRT-GSL-001', 'name' => 'سير غسالة', 'purchase_price' => 80, 'selling_price' => 150, 'quantity' => 70, 'minimum_stock' => 15],
            ['code' => 'PRT-GSL-002', 'name' => 'طلمبة طرد غسالة', 'purchase_price' => 320, 'selling_price' => 500, 'quantity' => 28, 'minimum_stock' => 8],
            ['code' => 'PRT-GSL-003', 'name' => 'مساعدين غسالة (طقم 2)', 'purchase_price' => 380, 'selling_price' => 600, 'quantity' => 18, 'minimum_stock' => 6],
            ['code' => 'PRT-GSL-004', 'name' => 'كاوتشة باب غسالة فول أتوماتيك', 'purchase_price' => 420, 'selling_price' => 650, 'quantity' => 14, 'minimum_stock' => 5],
            ['code' => 'PRT-GSL-005', 'name' => 'كارتة غسالة', 'purchase_price' => 1600, 'selling_price' => 2200, 'quantity' => 2, 'minimum_stock' => 4],
            ['code' => 'PRT-GSL-006', 'name' => 'لوك باب غسالة', 'purchase_price' => 260, 'selling_price' => 420, 'quantity' => 24, 'minimum_stock' => 6],
            ['code' => 'PRT-GSL-007', 'name' => 'سخان غسالة', 'purchase_price' => 340, 'selling_price' => 520, 'quantity' => 16, 'minimum_stock' => 5],
            ['code' => 'PRT-GSL-008', 'name' => 'صمام مياه (سلونايد) مجوز', 'purchase_price' => 230, 'selling_price' => 370, 'quantity' => 32, 'minimum_stock' => 8],
            ['code' => 'PRT-GSL-009', 'name' => 'رولمان بلي + أويل سيل (طقم)', 'purchase_price' => 450, 'selling_price' => 700, 'quantity' => 10, 'minimum_stock' => 4],
            ['code' => 'PRT-GSL-010', 'name' => 'موتور غسالة (شربون)', 'purchase_price' => 950, 'selling_price' => 1350, 'quantity' => 8, 'minimum_stock' => 3],
            ['code' => 'PRT-GSL-011', 'name' => 'درج مسحوق غسالة', 'purchase_price' => 180, 'selling_price' => 300, 'quantity' => 20, 'minimum_stock' => 5],
            ['code' => 'PRT-GSL-012', 'name' => 'خرطوم صرف غسالة', 'purchase_price' => 110, 'selling_price' => 190, 'quantity' => 55, 'minimum_stock' => 12],
            // خلاطات
            ['code' => 'PRT-KHL-001', 'name' => 'موتور خلاط 1000 وات', 'purchase_price' => 520, 'selling_price' => 800, 'quantity' => 15, 'minimum_stock' => 5],
            ['code' => 'PRT-KHL-002', 'name' => 'شفشق خلاط بلاستيك', 'purchase_price' => 190, 'selling_price' => 320, 'quantity' => 26, 'minimum_stock' => 8],
            ['code' => 'PRT-KHL-003', 'name' => 'شفشق خلاط زجاج', 'purchase_price' => 350, 'selling_price' => 550, 'quantity' => 12, 'minimum_stock' => 4],
            ['code' => 'PRT-KHL-004', 'name' => 'سكينة خلاط (طقم)', 'purchase_price' => 90, 'selling_price' => 160, 'quantity' => 65, 'minimum_stock' => 15],
            ['code' => 'PRT-KHL-005', 'name' => 'جلدة + وردة خلاط', 'purchase_price' => 25, 'selling_price' => 50, 'quantity' => 120, 'minimum_stock' => 30],
            ['code' => 'PRT-KHL-006', 'name' => 'مفتاح سرعات خلاط', 'purchase_price' => 110, 'selling_price' => 190, 'quantity' => 34, 'minimum_stock' => 8],
            ['code' => 'PRT-KHL-007', 'name' => 'مطحنة خلاط', 'purchase_price' => 160, 'selling_price' => 270, 'quantity' => 28, 'minimum_stock' => 8],
            ['code' => 'PRT-KHL-008', 'name' => 'شربون موتور (طقم)', 'purchase_price' => 40, 'selling_price' => 80, 'quantity' => 100, 'minimum_stock' => 25],
            // مواتير مياه
            ['code' => 'PRT-MTR-001', 'name' => 'أوتوماتيك موتور مياه', 'purchase_price' => 380, 'selling_price' => 600, 'quantity' => 22, 'minimum_stock' => 6],
            ['code' => 'PRT-MTR-002', 'name' => 'بالونة موتور (خزان ضغط)', 'purchase_price' => 550, 'selling_price' => 850, 'quantity' => 10, 'minimum_stock' => 4],
            ['code' => 'PRT-MTR-003', 'name' => 'ساقية موتور نحاس', 'purchase_price' => 300, 'selling_price' => 480, 'quantity' => 18, 'minimum_stock' => 5],
            ['code' => 'PRT-MTR-004', 'name' => 'أويل سيل موتور مياه', 'purchase_price' => 60, 'selling_price' => 120, 'quantity' => 80, 'minimum_stock' => 20],
            ['code' => 'PRT-MTR-005', 'name' => 'مكثف (كوندنسر) موتور', 'purchase_price' => 95, 'selling_price' => 170, 'quantity' => 48, 'minimum_stock' => 12],
            ['code' => 'PRT-MTR-006', 'name' => 'لف موتور مياه 1 حصان (مصنعية + خامات)', 'purchase_price' => 700, 'selling_price' => 1100, 'quantity' => 6, 'minimum_stock' => 3],
            // تكييفات
            ['code' => 'PRT-TKF-001', 'name' => 'كباس تكييف 1.5 حصان', 'purchase_price' => 5200, 'selling_price' => 6800, 'quantity' => 4, 'minimum_stock' => 3],
            ['code' => 'PRT-TKF-002', 'name' => 'كباس تكييف 2.25 حصان', 'purchase_price' => 6800, 'selling_price' => 8600, 'quantity' => 2, 'minimum_stock' => 2],
            ['code' => 'PRT-TKF-003', 'name' => 'كارتة تكييف داخلية', 'purchase_price' => 850, 'selling_price' => 1250, 'quantity' => 9, 'minimum_stock' => 4],
            ['code' => 'PRT-TKF-004', 'name' => 'موتور مروحة وحدة داخلية', 'purchase_price' => 620, 'selling_price' => 950, 'quantity' => 11, 'minimum_stock' => 4],
            ['code' => 'PRT-TKF-005', 'name' => 'موتور مروحة وحدة خارجية', 'purchase_price' => 750, 'selling_price' => 1100, 'quantity' => 8, 'minimum_stock' => 3],
            ['code' => 'PRT-TKF-006', 'name' => 'حساسات تكييف (طقم)', 'purchase_price' => 140, 'selling_price' => 250, 'quantity' => 42, 'minimum_stock' => 10],
            ['code' => 'PRT-TKF-007', 'name' => 'شحن فريون 410 (1.5 حصان)', 'purchase_price' => 450, 'selling_price' => 800, 'quantity' => 30, 'minimum_stock' => 8],
            ['code' => 'PRT-TKF-008', 'name' => 'كابستور تقويم', 'purchase_price' => 120, 'selling_price' => 220, 'quantity' => 52, 'minimum_stock' => 12],
            ['code' => 'PRT-TKF-009', 'name' => 'ريموت تكييف أصلي', 'purchase_price' => 220, 'selling_price' => 380, 'quantity' => 25, 'minimum_stock' => 8],
            ['code' => 'PRT-TKF-010', 'name' => 'خرطوم صرف + عزل (متر)', 'purchase_price' => 45, 'selling_price' => 90, 'quantity' => 150, 'minimum_stock' => 40],
            // مراوح وسخانات وميكروويف
            ['code' => 'PRT-MRW-001', 'name' => 'موتور مروحة سقف', 'purchase_price' => 480, 'selling_price' => 750, 'quantity' => 13, 'minimum_stock' => 4],
            ['code' => 'PRT-MRW-002', 'name' => 'مكثف مروحة', 'purchase_price' => 35, 'selling_price' => 70, 'quantity' => 110, 'minimum_stock' => 25],
            ['code' => 'PRT-MRW-003', 'name' => 'ريشة مروحة استاند', 'purchase_price' => 130, 'selling_price' => 220, 'quantity' => 30, 'minimum_stock' => 8],
            ['code' => 'PRT-MRW-004', 'name' => 'جيربوكس مروحة', 'purchase_price' => 160, 'selling_price' => 280, 'quantity' => 20, 'minimum_stock' => 6],
            ['code' => 'PRT-SKH-001', 'name' => 'هيتر سخان 1500 وات', 'purchase_price' => 280, 'selling_price' => 450, 'quantity' => 24, 'minimum_stock' => 6],
            ['code' => 'PRT-SKH-002', 'name' => 'ترموستات سخان', 'purchase_price' => 150, 'selling_price' => 260, 'quantity' => 36, 'minimum_stock' => 10],
            ['code' => 'PRT-SKH-003', 'name' => 'شيك بلف سخان', 'purchase_price' => 90, 'selling_price' => 160, 'quantity' => 44, 'minimum_stock' => 12],
            ['code' => 'PRT-MKW-001', 'name' => 'ماجنترون ميكروويف', 'purchase_price' => 680, 'selling_price' => 1050, 'quantity' => 7, 'minimum_stock' => 3],
            ['code' => 'PRT-MKW-002', 'name' => 'طبق ميكروويف زجاج', 'purchase_price' => 240, 'selling_price' => 400, 'quantity' => 14, 'minimum_stock' => 5],
            ['code' => 'PRT-MKW-003', 'name' => 'فيوز ميكروويف حراري', 'purchase_price' => 45, 'selling_price' => 90, 'quantity' => 90, 'minimum_stock' => 20],
            // عامة
            ['code' => 'PRT-GEN-001', 'name' => 'فيشة + سلك كهربا 3 متر', 'purchase_price' => 85, 'selling_price' => 150, 'quantity' => 75, 'minimum_stock' => 20],
            ['code' => 'PRT-GEN-002', 'name' => 'مفتاح تشغيل + لمبة بيان', 'purchase_price' => 55, 'selling_price' => 110, 'quantity' => 85, 'minimum_stock' => 20],
            ['code' => 'PRT-GEN-003', 'name' => 'مسامير وكلبسات (علبة مشكلة)', 'purchase_price' => 120, 'selling_price' => 200, 'quantity' => 60, 'minimum_stock' => 15],
            ['code' => 'PRT-GEN-004', 'name' => 'منظف دائرة تبريد + لحام', 'purchase_price' => 180, 'selling_price' => 320, 'quantity' => 35, 'minimum_stock' => 10],
        ];

        foreach ($parts as $part) {
            SparePart::updateOrCreate(['code' => $part['code']], $part);
        }
    }
}
