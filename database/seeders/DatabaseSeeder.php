<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * الترتيب مهم عشان الـ Foreign Keys:
     * أدوار/مستخدمين ← أنواع/ماركات/موديلات ← أعطال/قطع غيار
     * ← عملاء ← أوامر ← (أعطال + قطع الأوامر) ← فواتير ← بنود ← مدفوعات ← ضمانات ← واتساب
     */
    public function run(): void
    {
     
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            DeviceTypeSeeder::class,
            BrandSeeder::class,
            DeviceModelSeeder::class,
            SparePartSeeder::class,
            ProblemSeeder::class,
            CustomerSeeder::class,
            RepairOrderSeeder::class,
            RepairOrderProblemSeeder::class,
            RepairOrderPartSeeder::class,
            InvoiceSeeder::class,
            InvoiceItemSeeder::class,
            PaymentSeeder::class,
            WarrantySeeder::class,
            WhatsappMessageSeeder::class,
        ]);
    }
}
