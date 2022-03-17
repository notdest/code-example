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
        $schedule->command("run:void parseTrends")         ->hourlyAt(2);

        $schedule->command("rss:import")                   ->everyFifteenMinutes()->withoutOverlapping(120);

        $schedule->command("run:void instagramApiPosts")   ->everyMinute();                         // сторис здесь же

        $schedule->command("run:void parseEvents")         ->dailyAt('4:07');
        $schedule->command("Instagram:clearCache  1")      ->monthlyOn(2, '4:21');

        $schedule->command("run:oneParam parseLiveinternet yesterday")      ->dailyAt('4:09');
        $schedule->command("run:oneParam parseLiveinternetPages yesterday") ->dailyAt('4:26');
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
