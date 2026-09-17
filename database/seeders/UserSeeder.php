<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ([User::ROLE_MANAGER, User::ROLE_STAFF, User::ROLE_TECHNICIAN] as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        $users = [
            // الإدارة
            ['name' => 'م. محمود محمد ', 'email' => 'manager@workshop.test', 'role' => User::ROLE_MANAGER],
            ['name' => 'احمد السيد', 'email' => 'admin@workshop.test', 'role' => User::ROLE_MANAGER],
            // الاستقبال والكاشير
            ['name' => 'أحمد سمير', 'email' => 'reception1@workshop.test', 'role' => User::ROLE_STAFF],
            ['name' => 'كريم عادل', 'email' => 'reception2@workshop.test', 'role' => User::ROLE_STAFF],
            ['name' => 'سارة محمود', 'email' => 'cashier@workshop.test', 'role' => User::ROLE_STAFF],
            ['name' => 'نهى إبراهيم', 'email' => 'support@workshop.test', 'role' => User::ROLE_STAFF],
            // الفنيين — كل واحد متخصص في حاجة
            ['name' => 'الأسطى رمضان حسني', 'email' => 'tech.ramadan@workshop.test', 'role' => User::ROLE_TECHNICIAN],
            ['name' => 'الأسطى سيد عبد الله', 'email' => 'tech.sayed@workshop.test', 'role' => User::ROLE_TECHNICIAN],
            ['name' => 'مصطفى كامل', 'email' => 'tech.mostafa@workshop.test', 'role' => User::ROLE_TECHNICIAN],
            ['name' => 'حمادة الشحات', 'email' => 'tech.hamada@workshop.test', 'role' => User::ROLE_TECHNICIAN],
            ['name' => 'وليد فتحي', 'email' => 'tech.walid@workshop.test', 'role' => User::ROLE_TECHNICIAN],
            ['name' => 'إسلام جمال', 'email' => 'tech.islam@workshop.test', 'role' => User::ROLE_TECHNICIAN],
            ['name' => 'رضا محمد', 'email' => 'tech.reda@workshop.test', 'role' => User::ROLE_TECHNICIAN],
            ['name' => 'هاني عزت', 'email' => 'tech.hany@workshop.test', 'role' => User::ROLE_TECHNICIAN],
        ];

        foreach ($users as $data) {
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make('password123'),
                    'email_verified_at' => now(),
                ]
            );

            if (! $user->hasRole($data['role'])) {
                $user->assignRole($data['role']);
            }
        }
    }
}
