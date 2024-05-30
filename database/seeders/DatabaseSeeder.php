<?php

namespace Database\Seeders;

use App\Enums\Roles;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(UserSeeder::class);

        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        Schema::disableForeignKeyConstraints();
        DB::table('permissions')->truncate();
        DB::table('roles')->truncate();
        Schema::enableForeignKeyConstraints();

        $this->call(RoleSeeder::class);
        $this->call(PermissionSeeder::class);

        // $user = User::find(1);
        // $user->givePermissionTo('view history');
        // $user->givePermissionTo('view requests');
        // $user->givePermissionTo('view dashboard');
        // $user->assignRole(Roles::SUPER_ADMIN);
        // $user->assignRole(Roles::VAN_WIJK);

        // $user = User::find(2);
        // $user->givePermissionTo('view history');
        // $user->givePermissionTo('view requests');
        // $user->assignRole(Roles::SUPER_ADMIN);
        // $user->assignRole(Roles::VAN_WIJK);

        // $user = User::find(3);
        // $user->givePermissionTo('view history');
        // $user->givePermissionTo('view requests');
        // $user->givePermissionTo('view dashboard');


        // /** @var User $user */
        // $user = User::find(4);
        // $user->givePermissionTo('view history');
        // $user->givePermissionTo('view requests');
        // $user->givePermissionTo('view dashboard');
        // $user->assignRole(Roles::SUPER_ADMIN);
        // $user->assignRole(Roles::VAN_WIJK);

        // $user = User::find(5);
        // $user->givePermissionTo('view history');
        // $user->givePermissionTo('view requests');
        // $user->givePermissionTo('view dashboard');
        // $user->assignRole(Roles::SUPER_ADMIN);
        // $user->assignRole(Roles::VAN_WIJK);

        // $user = User::find(6);
        // $user->givePermissionTo('view history');
        // $user->givePermissionTo('view requests');
        // $user->givePermissionTo('view dashboard');
        // $user->assignRole(Roles::SUPER_ADMIN);
        // $user->assignRole(Roles::VAN_WIJK);
    }
}
