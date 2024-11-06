<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use DB;
use Log;
class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // 스케줄 내역을 불러온다.
        $result = DB::TABLE("conf_batch")->SELECT("*")
                ->WHERE('status', 'Y')
                ->WHERE('use_yn', 'Y')
                ->orderBy('no')->get();

        foreach($result as $v)
        {
            $cron = trim($v->sch_minute).' '.trim($v->sch_hour).' '.trim($v->sch_day).' '.trim($v->sch_month).' '.trim($v->sch_week);
            
            $schedule->command($v->sch_command.' '.$v->no)->cron($cron)->withoutOverlapping();
        }
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
