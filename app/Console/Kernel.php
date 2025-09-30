<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{

    protected $commands = [
        Commands\BookingStart::class,
    ];

    protected function schedule(Schedule $schedule)
    {
        $schedule->command('booking:start')->everyTenMinutes()->evenInMaintenanceMode();
        $schedule->command('booking:end')->everyMinute()->evenInMaintenanceMode();
        $schedule->command('pending:deposit')->daily()->evenInMaintenanceMode();
        $schedule->command('coupon:expire')->daily()->evenInMaintenanceMode();
    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
        
    }
}
