<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Setting;
use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use PDO;

/**
 * Class IdentifyTenant
 *
 * This class is the middleware for identifying the tenant.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class IdentifyTenant
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        /* @var Tenant|null $tenant */
        $tenant = Tenant::where('domain', '=', $request->getHttpHost())
            ->first();

        if (! empty($tenant)) {
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

            $request->attributes->add(['tenant' => $tenant]);

            Setting::whereNotNull('value')
                ->each(function (Setting $setting) {
                    Config::set($setting->setting, $setting->value);
                });
        }

        return $next($request);
    }
}
