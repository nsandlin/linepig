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
        Commands\SearchImport::class,
        Commands\MultimediaImport::class,
        Commands\CatalogImport::class,
        Commands\TaxonomyImport::class,
        Commands\ElasticsearchImport::class,
        Commands\SitemapGenerator::class,
        Commands\BOLDImport::class,
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
        $schedule->command('multimedia:import')->weeklyOn(1, '10:05');
        $schedule->command('catalog:import')->weeklyOn(1, '10:10');
        $schedule->command('taxonomy:import')->weeklyOn(1, '10:15');
        $schedule->command('sitemap:create')->weeklyOn(1, '10:20');
        $schedule->command('elasticsearch:import')->weeklyOn(1, '10:25');
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }

    /**
     * Get the timezone that should be used by default for scheduled events.
     *
     * @return \DateTimeZone|string|null
     */
    protected function scheduleTimezone()
    {
        return 'America/Chicago';
    }
}
