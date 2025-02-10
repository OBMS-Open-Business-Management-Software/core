<?php

namespace App\Jobs\Dispatchers;

use App\Jobs\Structure\Job;
use App\Jobs\TenantJobs\InvoiceReminders as InvoiceRemindersJob;
use App\Models\Tenant;

/**
 * Class InvoiceReminders
 *
 * This class is the dispatcher job for sending out invoice reminders.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class InvoiceReminders extends Job
{
    public $tries = 1;
    public $timeout = 3600;
    public static $onQueue = 'dispatchers';

    /**
     * Execute job algorithm.
     */
    public function handle()
    {
        // Dispatch job for main instance
        $this->dispatch((new InvoiceRemindersJob([
            'tenant_id' => 0,
        ]))->onQueue('invoice_reminders'));

        // Dispatch job for tenant instances
        Tenant::query()->each(function (Tenant $tenant) {
            $this->dispatch((new InvoiceRemindersJob([
                'tenant_id' => $tenant->id,
            ]))->onQueue('invoice_reminders'));
        });
    }

    /**
     * Define tags which the job can be identified by.
     *
     * @return array
     */
    public function tags(): array
    {
        return [
            'job',
            'job:dispatcher',
            'job:dispatcher:InvoiceReminders',
        ];
    }
}
