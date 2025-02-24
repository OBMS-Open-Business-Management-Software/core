<?php

declare(strict_types=1);

namespace App\Console;

use App\Jobs\Dispatchers\AutoMigrations;
use App\Jobs\Dispatchers\ContractInvoicing;
use App\Jobs\Dispatchers\InvoiceReminders;
use App\Jobs\Dispatchers\ShopOrderQueue;
use App\Jobs\Dispatchers\SupportTicketImport;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Laravel\Passport\Console\ClientCommand;

/**
 * Class Kernel.
 *
 * This class holds the definition for job and command scheduling (automated tasks).
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        ClientCommand::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param Schedule $schedule
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('horizon:snapshot')->everyFiveMinutes();

        $schedule->job(new SupportTicketImport(), 'dispatchers')->everyTenMinutes();
        $schedule->job(new InvoiceReminders(), 'dispatchers')->hourly();
        $schedule->job(new ContractInvoicing(), 'dispatchers')->hourly();
        $schedule->job(new ShopOrderQueue(), 'dispatchers')->everyTenMinutes();
        $schedule->job(new AutoMigrations(), 'dispatchers')->everyTenMinutes();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
