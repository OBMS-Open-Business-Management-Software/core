<?php

declare(strict_types=1);

namespace App\Jobs\Dispatchers;

use App\Jobs\Structure\Job;
use App\Jobs\TenantJobs\ContractInvoicing as ContractInvoicingJob;
use App\Models\Tenant;

/**
 * Class ContractInvoicing.
 *
 * This class is the dispatcher job for generating contract invoices.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class ContractInvoicing extends Job
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
        $this->dispatch((new ContractInvoicingJob([
            'tenant_id' => 0,
        ]))->onQueue('contract_invoicing'));

        // Dispatch job for tenant instances
        Tenant::query()->each(function (Tenant $tenant) {
            $this->dispatch((new ContractInvoicingJob([
                'tenant_id' => $tenant->id,
            ]))->onQueue('contract_invoicing'));
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
            'job:dispatcher:ContractInvoicing',
        ];
    }
}
