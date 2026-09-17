<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Throwable;

class RoleSeeder extends Seeder
{
    /**
     * توزيع الصلاحيات المعتمد:
     * - مدير النظام: كل الصلاحيات (زائد تجاوز Gate::before).
     * - موظف (استقبال/كاشير): العملاء + أوامر الإصلاح (عرض/إنشاء/تعديل)
     *   + المدفوعات + عرض قطع الغيار والبيانات الأساسية — بلا حذف
     *   وبلا إدارة مستخدمين/أدوار.
     * - فني: أوامره المسندة إليه فقط (عرض/تعديل) + عرض مرجعي للأعطال
     *   وقطع الغيار والبيانات الأساسية — بلا مالية وبلا حذف وبلا عملاء.
     * - مستخدم عادي (بلا دور): ممنوع من دخول اللوحة أصلًا.
     */
    public function run(): void
    {
        $this->ensurePermissionsExist();
        $this->migrateLegacyEnglishRoles();

        $manager = Role::firstOrCreate(['name' => User::ROLE_MANAGER, 'guard_name' => 'web']);
        $staff = Role::firstOrCreate(['name' => User::ROLE_STAFF, 'guard_name' => 'web']);
        $technician = Role::firstOrCreate(['name' => User::ROLE_TECHNICIAN, 'guard_name' => 'web']);

        $manager->syncPermissions(Permission::all());
        $staff->syncPermissions($this->staffPermissions());
        $technician->syncPermissions($this->technicianPermissions());

        app()->make(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * صلاحيات Shield تُولَّد في قاعدة البيانات (ليست Migrations)، لذلك أي
     * migrate:fresh يمسحها ويُفشل التوزيع. نعيد توليدها هنا أولًا ثم نضمن
     * وجود كل اسم يحتاجه التوزيع — السيدر يعمل دائمًا على قاعدة فاضية.
     */
    protected function ensurePermissionsExist(): void
    {
        try {
            Artisan::call('shield:generate', [
                '--all' => true,
                '--ignore-existing-policies' => true,
                '--panel' => 'admin',
                '--no-interaction' => true,
            ]);
        } catch (Throwable) {
            // يكمل للخطة البديلة: إنشاء الأسماء المطلوبة يدويًا.
        }

        foreach (self::requiredPermissions() as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        app()->make(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * كل أسماء الصلاحيات التي يتوقعها التوزيع — تُستخدم لإنشاء
     * الصلاحيات في بيئة الاختبار قبل التوزيع.
     *
     * @return array<int, string>
     */
    public static function requiredPermissions(): array
    {
        $seeder = new self;

        return array_values(array_unique(array_merge(
            $seeder->staffPermissions(),
            $seeder->technicianPermissions()
        )));
    }

    /**
     * @return array<int, string>
     */
    protected function staffPermissions(): array
    {
        return [
            // أوامر الإصلاح: تشغيل كامل بلا حذف.
            'ViewAny:RepairOrder', 'View:RepairOrder', 'Create:RepairOrder', 'Update:RepairOrder',
            // العملاء: تشغيل كامل بلا حذف.
            'ViewAny:Customer', 'View:Customer', 'Create:Customer', 'Update:Customer',
            // المالية: عرض وإدارة المدفوعات (كاشير).
            'ViewPayments', 'ManagePayments',
            // قطع الغيار: عرض فقط (إضافة القطع للأوامر تتم من شاشة الأمر).
            'ViewAny:SparePart', 'View:SparePart',
            // البيانات الأساسية: عرض + إدخال/تعديل بلا حذف.
            'ViewAny:Brand', 'View:Brand', 'Create:Brand', 'Update:Brand',
            'ViewAny:DeviceModel', 'View:DeviceModel', 'Create:DeviceModel', 'Update:DeviceModel',
            'ViewAny:DeviceType', 'View:DeviceType', 'Create:DeviceType', 'Update:DeviceType',
            'ViewAny:Problem', 'View:Problem', 'Create:Problem', 'Update:Problem',
            // الصفحات والودجتس.
            'View:RepairOrdersBoard',
            'View:TodayOverviewWidget', 'View:LowStockPartsWidget', 'View:TechniciansWorkloadWidget',
        ];
    }

    /**
     * @return array<int, string>
     */
    protected function technicianPermissions(): array
    {
        return [
            // أوامره فقط (الملكية تُفرض في RepairOrderPolicy + سكوب العرض) — بلا إنشاء وبلا حذف.
            'ViewAny:RepairOrder', 'View:RepairOrder', 'Update:RepairOrder',
            // مرجع قراءة للشغل.
            'ViewAny:Problem', 'View:Problem',
            'ViewAny:SparePart', 'View:SparePart',
            'ViewAny:Brand', 'View:Brand',
            'ViewAny:DeviceModel', 'View:DeviceModel',
            'ViewAny:DeviceType', 'View:DeviceType',
            // لوحة المتابعة (تظهر أوامره فقط).
            'View:RepairOrdersBoard',
        ];
    }

    /**
     * ترحيل الأدوار الإنجليزية القديمة (admin/staff/technician) إلى
     * نظيرتها العربية مع نقل المستخدمين والصلاحيات ثم حذفها،
     * حتى لا تنكسر البيئات التي زُرعت بالسيدر القديم.
     */
    protected function migrateLegacyEnglishRoles(): void
    {
        $map = [
            'admin' => User::ROLE_MANAGER,
            'staff' => User::ROLE_STAFF,
            'technician' => User::ROLE_TECHNICIAN,
        ];

        foreach ($map as $legacy => $current) {
            $legacyRole = Role::where('name', $legacy)->where('guard_name', 'web')->first();

            if ($legacyRole === null) {
                continue;
            }

            if ($legacy === $current) {
                continue;
            }

            $target = Role::firstOrCreate(['name' => $current, 'guard_name' => 'web']);
            $target->givePermissionTo($legacyRole->permissions);

            foreach ($legacyRole->users as $user) {
                if (! $user->hasRole($target)) {
                    $user->assignRole($target);
                }

                $user->removeRole($legacyRole);
            }

            $legacyRole->delete();
        }
    }
}
