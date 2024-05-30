<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        /** Reset redis cache, to receive fresh odoo data */
        $schedule->call(function () {
            return \Illuminate\Support\Facades\Redis::connection()->client()->flushAll();
        })->everyThirtyMinutes();

        $schedule->command('ticket:inactivity')->daily();
        $schedule->command('odoo:company-information')->hourly();
        $schedule->command('odoo:depot-all-cache')->everyMinute();
        Log::debug("Depot all cached successfully!");
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
