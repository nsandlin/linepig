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
        $schedule->command('search:import')->withoutOverlapping()->weekly()->mondays()->at('08:00');
        $schedule->command('multimedia:import')->withoutOverlapping()->weekly()->mondays()->at('08:30');
        $schedule->command('catalog:import')->withoutOverlapping()->weekly()->mondays()->at('09:00');
        $schedule->command('taxonomy:import')->withoutOverlapping()->weekly()->mondays()->at('09:10');
        $schedule->command('sitemap:create')->withoutOverlapping()->weekly()->mondays()->at('09:20');
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
