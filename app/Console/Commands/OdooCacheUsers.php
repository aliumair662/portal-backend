<?php

namespace App\Console\Commands;

use App\Models\Odoo\Base;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class OdooCacheUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'odoo:users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Store Odoo users so we can access name, phone and avatar';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $con = new Base();

        $cached_users = $con->cache('users', function () use ($con) {
            $users = $con->connect()->fields(['name', 'avatar_256', 'work_phone'])->get('res.users');
            return $users;
        }, true);

        /*foreach( $cached_users as $user ){
            if( $user['avatar_256'] != null ) {
                Storage::disk('public')->put('/staff/' . $user['id'] . '.png', base64_decode($user['avatar_256']));
                $this->info($user['id'] . ' saved!');
            }
        }*/

        $this->info('Retrieved ' . count($cached_users) . ' users.');
    }
}
