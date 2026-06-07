<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Schedule daily backups
        $schedule->exec('docker compose exec -T db mariadb-dump -u cms -pcms_secret cms_app > ' . storage_path('backups/daily_backup_' . date('YmdHis') . '.sql'))
            ->daily()
            ->at('02:00'); // 2 AM daily
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }

    /**
     * Get the bootstrap classes for the application.
     *
     * @return array
     */
    protected function bootstrappers(): array
    {
        return array_merge(
            parent::bootstrappers(),
            [
                \App\Bootstrap\ProtectProductionDatabase::class,
            ]
        );
    }
}
