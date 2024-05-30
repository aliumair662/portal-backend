<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = [
            [
                'first_name'=>'Yannick',
                'last_name' => 'Van Meerbeeck',
                'email'=>'yannick@yame.be',
                'company_name'=>'Weichie',
                'phone' => ' ',
                'active' => 1,
                'password' => Hash::make('admin123'),
            ],
            [
                'first_name' => 'Michael',
                'last_name' => 'Vanderschelde',
                'company_name' => 'MCore Services',
                'phone' => '+320491986605',
                'active' => '1',
                'email' => 'michael@mcore-services.be',
                'password' => Hash::make('VanWijkTest')
            ],
            [
                'first_name'=>'Admin',
                'last_name' => 'Test',
                'email'=>'admin@admin.test',
                'company_name'=>'Admin Company',
                'phone' => ' ',
                'active' => 1,
                'password' => Hash::make('admin123'),
            ],
            [
                'first_name'=>'Kenn',
                'last_name' => 'Schipper',
                'email'=>'kenn@upways.nl',
                'company_name'=>'Upways',
                'phone' => ' ',
                'active' => 1,
                'password' => Hash::make('admin123'),
            ],
            // [
            //     'first_name'=>'Nathan',
            //     'last_name' => 'Baart',
            //     'email'=>'nathan@vanwijkuitvaartkisten.nl',
            //     'company_name'=>'Van Wijk',
            //     'phone' => ' ',
            //     'active' => 1,
            //     'password' => Hash::make('admin123'),
            // ],
            [
            'first_name'=>'Api',
                'last_name' => 'Scanner',
                'email' => 'api@vanwijkuitvaartkisten.nl',
                'company_name' => 'Van Wijk API Scan',
                'phone' => ' ',
                'active' => 1,
                'password' => Hash::make('admin123'),
            ],

        ];

        foreach( $users as $user ){
            User::create( $user );
        }
    }
}
