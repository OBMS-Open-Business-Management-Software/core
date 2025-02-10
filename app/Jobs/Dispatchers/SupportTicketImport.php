<?php

namespace App\Jobs\Dispatchers;

use App\Jobs\Structure\Job;
use App\Jobs\TenantJobs\SupportTicketImport as SupportTicketImportJob;
use App\Models\Tenant;

/**
 * Class SupportTicketImport
 *
 * This class is the dispatcher job for importing ticket metadata via. IMAP inboxes.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class SupportTicketImport extends Job
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
        $this->dispatch((new SupportTicketImportJob([
            'tenant_id' => 0,
        ]))->onQueue('support_ticket_import'));

        // Dispatch job for tenant instances
        Tenant::query()->each(function (Tenant $tenant) {
            $this->dispatch((new SupportTicketImportJob([
                'tenant_id' => $tenant->id,
            ]))->onQueue('support_ticket_import'));
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
            'job:dispatcher:SupportTicketImport',
        ];
    }
}
