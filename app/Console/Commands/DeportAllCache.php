<?php

namespace App\Console\Commands;

use App\Http\Controllers\DepotController;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class DeportAllCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'odoo:depot-all-cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cache Depot All api';

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
        (new DepotController)->cacheDepotAll();
    }
}
