<?php

declare(strict_types=1);

namespace App\Jobs\Structure;

use App\Models\Tenant;
use Illuminate\Support\Facades\Config;
use PDO;

/**
 * Class TenantJob
 *
 * Parent class for jobs that have to be executed as per tenant.
 * It is used to set the correct database connection for Eloquent
 * models up front (if change is needed).
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class TenantJob extends Job
{
    /**
     * Hold the tenant id.
     *
     * @var string|null
     */
    protected ?string $tenant_id;

    /**
     * Hold the tenant domain.
     *
     * @var string|null
     */
    protected ?string $tenant_domain;

    /**
     * TenantJob constructor.
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        /* @var Tenant $tenant */
        if (
            ($tenantId = $data['tenant_id'] ?? 0) > 0 &&
            ! empty($tenant = Tenant::find($tenantId))
        ) {
            Config::set('database.mysql', [
                'driver'         => $tenant->database_driver ?? Config::get('database.mysql.driver'),
                'url'            => $tenant->database_url ?? Config::get('database.mysql.url'),
                'host'           => $tenant->database_host ?? Config::get('database.mysql.host'),
                'port'           => $tenant->database_port ?? Config::get('database.mysql.port'),
                'database'       => $tenant->database_database ?? Config::get('database.mysql.database'),
                'username'       => $tenant->database_username ?? Config::get('database.mysql.username'),
                'password'       => $tenant->database_password ?? Config::get('database.mysql.password'),
                'unix_socket'    => $tenant->database_unix_socket ?? Config::get('database.mysql.unix_socket'),
                'charset'        => $tenant->database_charset ?? Config::get('database.mysql.charset'),
                'collation'      => $tenant->database_collation ?? Config::get('database.mysql.collation'),
                'prefix'         => $tenant->database_prefix ?? Config::get('database.mysql.prefix'),
                'prefix_indexes' => $tenant->database_prefix_indexes ?? Config::get('database.mysql.prefix_indexes'),
                'strict'         => $tenant->database_strict ?? Config::get('database.mysql.strict'),
                'engine'         => $tenant->database_engine ?? Config::get('database.mysql.engine'),
                'options'        => extension_loaded('pdo_mysql') ? array_filter([
                    PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
                    PDO::ATTR_PERSISTENT   => true,
                ]) : [],
            ]);

            Config::set('cache.stores.redis.session.prefix', $tenant->redis_prefix ?? Config::get('cache.stores.redis.session.prefix'));

            $this->tenant_id     = (string) $tenant->id;
            $this->tenant_domain = $tenant->domain;
        } else {
            $this->tenant_id     = 'main';
            $this->tenant_domain = 'main';
        }
    }

    /**
     * Inject additional tenant reference tags into job object.
     *
     * @param array|null $tags
     *
     * @return array
     */
    protected function injectTenantTags(?array $tags = null): array
    {
        return array_merge($tags ?? [], [
            'tenant:jobs',
            'tenant:id:' . ($this->tenant_id ?? 'unknown'),
            'tenant:domain:' . ($this->tenant_domain ?? 'unknown'),
        ]);
    }
}
