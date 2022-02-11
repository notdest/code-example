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

        $schedule->command("run:void instagramApiPosts")   ->everyMinute();

        $schedule->command("run:twoParam instagramApiStories 4 1")  ->cron('0,5,10,15,20,25,30,35,40,45,50,55 * * * *');
        $schedule->command("run:twoParam instagramApiStories 4 2")  ->cron('1,6,11,16,21,26,31,36,41,46,51,56 * * * *');
        $schedule->command("run:twoParam instagramApiStories 4 3")  ->cron('2,7,12,17,22,27,32,37,42,47,52,57 * * * *');
        $schedule->command("run:twoParam instagramApiStories 4 4")  ->cron('3,8,13,18,23,28,33,38,43,48,53,58 * * * *');

        $schedule->command("run:twoParam storiesByOne 7 1") ->dailyAt('3:07');
        $schedule->command("run:twoParam storiesByOne 7 2") ->dailyAt('3:12');
        $schedule->command("run:twoParam storiesByOne 7 3") ->dailyAt('3:17');
        $schedule->command("run:twoParam storiesByOne 7 4") ->dailyAt('3:22');
        $schedule->command("run:twoParam storiesByOne 7 5") ->dailyAt('3:27');
        $schedule->command("run:twoParam storiesByOne 7 6") ->dailyAt('3:32');
        $schedule->command("run:twoParam storiesByOne 7 7") ->dailyAt('3:37');

        $schedule->command("run:void parseEvents")         ->dailyAt('4:07');
        $schedule->command("Instagram:clearCache  1")      ->monthlyOn(2, '4:21');
        $schedule->command("run:oneParam parseLiveinternet yesterday")  ->dailyAt('4:09');
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
