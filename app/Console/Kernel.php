<?php

namespace App\Console;

use App\Console\Commands\SendLateFileEmails;
use App\Console\Commands\SendTestEmails;
use App\Jobs\ReplenishUserCredits;
use App\Jobs\SendOverdueFilesNotifications;
use App\Jobs\SendUpcomingDueFilesNotifications;
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
        SendTestEmails::class,
        SendLateFileEmails::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')
        //          ->hourly();

        // Let recipients know on Monday night that they have late files. Because generally
        // people are more productive on Tues mornings.
        $schedule->call(function () {
            dispatch(new SendOverdueFilesNotifications);
        })->weekly()->mondays()->at('21:00');

        // Every morning, send upcoming file reminders at 06:00
        $schedule->call(function () {
            dispatch(new SendUpcomingDueFilesNotifications);
        })->dailyAt('06:00');

        // Replenish user credits to 5 / month
        $schedule->call(function () {
            dispatch(new ReplenishUserCredits);
        })->monthlyOn(1, '00:00');

        /*
         * TODO ::: Maybe we can move dispatching the jobs into an Artisan command and have
         * the scheduler call the command. But maybe the extra abstraction is unecessary
         * unless we're going to be calling the job manually too.
         */

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
