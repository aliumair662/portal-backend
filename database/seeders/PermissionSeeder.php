<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Permission::create(['name' => 'view dashboard']);
        Permission::create(['name' => 'view user dashboard']);

        Permission::create(['name' => 'view requests']);
        Permission::create(['name' => 'edit requests']);

        Permission::create(['name' => 'view depot overview']);
        Permission::create(['name' => 'view depot sign offs']);

        Permission::create(['name' => 'view history']);

        Permission::create(['name' => 'view orders']);
        Permission::create(['name' => 'view order overview']);

        Permission::create(['name' => 'view settings']);
        Permission::create(['name' => 'view tickets']);

        Permission::create(['name' => 'view order']);
        Permission::create(['name' => 'view stock']);
        Permission::create(['name' => 'view base price']);
        Permission::create(['name' => 'view setting']);
    }
}
