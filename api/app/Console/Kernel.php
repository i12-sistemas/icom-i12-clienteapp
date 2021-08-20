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
        '\App\Console\Commands\StorageLimpezaCommand',
        '\App\Console\Commands\DownloadNFeBSoftCommand',
        '\App\Console\Commands\ColetaNotaIndexacaoXmlCommanda',
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // todo minuto 1 e 31 entre as horas 6 e 20
        // $schedule->command('bsoft:processanfe')->cron('1,30 6-20 * * *');
        // $schedule->command('coletanota:indexanfe')->cron('10,40 6-20 * * *');
        // $schedule->command('coletanota:avulsaadd')->cron('20,50 * * * *');

        // substitui as 3 linhas acimas
        //At minute 30 past every hour from 0 through 7.
        // $schedule->command('coletanota:processocompleto')->withoutOverlapping()->cron('30 0-7 * * *');
        //At minute 5 past every hour from 7 through 19.
        // $schedule->command('coletanota:processocompleto')->withoutOverlapping()->cron('5 * * * *');
        $schedule->command('coletanota:processocompleto')->withoutOverlapping()->everyFiveMinutes();
        // At minute 30 past every hour from 19 through 23.
        // $schedule->command('coletanota:processocompleto')->withoutOverlapping()->cron('30 19-23 * * *');

        $schedule->command('storage:limpezaarquivos')->everyThreeHours();
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
