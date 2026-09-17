<?php

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}

/*
|--------------------------------------------------------------------------
| Panel Access Helpers
|--------------------------------------------------------------------------
|
| Most panel tests need seeded Shield roles/permissions. Call
| seedPanelRoles() in beforeEach, then authenticate via staffPanelUser()
| (reception/cashier flow) or managerPanelUser() (full access).
|
*/

function seedPanelRoles(): void
{
    foreach (RoleSeeder::requiredPermissions() as $name) {
        Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
    }

    app(RoleSeeder::class)->run();
}

function staffPanelUser(): User
{
    $user = User::factory()->create();

    $user->assignRole(User::ROLE_STAFF);

    return $user->fresh();
}

function managerPanelUser(): User
{
    $user = User::factory()->create();

    $user->assignRole(User::ROLE_MANAGER);

    return $user->fresh();
}
