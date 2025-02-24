<?php

declare(strict_types=1);

namespace App\Jobs\TenantJobs;

use App\Jobs\Structure\TenantJob;
use App\Jobs\Structure\UniquelyQueueable;
use App\Models\Tenant;
use Illuminate\Support\Facades\Artisan;

/**
 * Class AutoMigrations.
 *
 * This class is the tenant job for running migrations.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class AutoMigrations extends TenantJob
{
    use UniquelyQueueable;

    public $tries = 1;

    public $timeout = 3600;

    public static $onQueue = 'auto_migrations';

    /**
     * SupportTicketImport constructor.
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        parent::__construct($data);
    }

    /**
     * Execute job algorithm.
     */
    public function handle()
    {
        Artisan::call('migrate');
    }

    /**
     * Define tags which the job can be identified by.
     *
     * @return array
     */
    public function tags(): array
    {
        return $this->injectTenantTags([
            'job',
            'job:tenant',
            'job:tenant:AutoMigrations',
        ]);
    }

    /**
     * Set a unique identifier to avoid duplicate queuing of the same task.
     *
     * @return string
     */
    public function getUniqueIdentifier(): string
    {
        return 'auto-migrations-' . $this->tenant_id;
    }
}
