<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('watchlist:check-prices')->everyFifteenMinutes();
        $schedule->command('dmarket:sync-orders')->everyFifteenMinutes();
        $schedule->command('app:update-target-orders')->everyMinute();

    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
    }
}
