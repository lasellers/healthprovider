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
    'App\Console\Commands\NursingHomeCompare',
    'App\Console\Commands\HomeHealthCompare'//,
    //'App\Console\Commands\NHC',
    //'App\Console\Commands\HHC',
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        /* http://laravel.com/docs/master/scheduling */

//      $schedule->command('inspire')
//      ->hourly();

//      $schedule->command('fetch','http://lewisasellers.com/feed/')
//      ->everyFiveMinutes();

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
}
