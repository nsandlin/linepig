<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('search:import')->weeklyOn(1, '10:00');
        $schedule->command('multimedia:import')->weeklyOn(1, '10:10');
        $schedule->command('catalog:import')->weeklyOn(1, '10:20');
        $schedule->command('taxonomy:import')->weeklyOn(1, '10:30');
        $schedule->command('sitemap:create')->weeklyOn(1, '10:40');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
