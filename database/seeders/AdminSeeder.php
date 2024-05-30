<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Enums\Roles;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::create( [
            'first_name'=>'Nathan',
            'last_name' => 'Baart',
            'email'=>'nathan@vanwijkuitvaartkisten.nl',
            'company_name'=>'Van Wijk',
            'phone' => ' ',
            'active' => 1,
            'password' => Hash::make('admin123'),
        ]);
        $user->givePermissionTo('view history');
        $user->givePermissionTo('view requests');
        $user->givePermissionTo('view dashboard');
        $user->assignRole(Roles::SUPER_ADMIN);
        $user->assignRole(Roles::VAN_WIJK);
    }
}
