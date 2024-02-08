<?php

namespace App\Console;

use App\Jobs\SaveFinalizeAppointmentColletionMobileApi;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Log;
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
        // $schedule->command('inspire')
        //          ->hourly();
        $schedule->command("UpdateStockLadger")->dailyAt('01:00');
        // Log::info("**************************** CALLING CRON ******************************");
        // $schedule->call(function () {
        //     SaveFinalizeAppointmentColletionMobileApi::dispatch()->everyMinute();
        // })->daily();
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
