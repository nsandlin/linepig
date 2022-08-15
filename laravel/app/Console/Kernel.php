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
        $schedule->command('search:import')->weekly()->mondays()->at('08:15');
        $schedule->command('sitemap:create')->weekly()->mondays()->at('08:35');
        $schedule->command('test --testsuite=Feature --stop-on-failure')->hourly();

        // Take the site down for Ross EMu maintenance on Saturdays.
        $schedule->command('down')->weekly()->saturdays()->at('03:30');
        $schedule->command('up')->weekly()->saturdays()->at('12:30');
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
