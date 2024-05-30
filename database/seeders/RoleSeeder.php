<?php

namespace Database\Seeders;

use App\Enums\Roles;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Role::create(['name' => Roles::SUPER_ADMIN]);
        Role::create(['name' => Roles::VAN_WIJK]);
        Role::create(['name' => Roles::PORTAL]);
        Role::create(['name' => Roles::PORTAL_ACCOUNT]);
        Role::create(['name' => Roles::PORTAL_CLIENT]);
        Role::create(['name' => Roles::PORTAL_FUNERAL_DIRECTOR]);
    }
}
