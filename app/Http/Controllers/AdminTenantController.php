<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use PDO;

class AdminTenantController extends Controller
{
    /**
     * Show list of tenants.
     *
     * @return Renderable
     */
    public function tenant_index(): Renderable
    {
        return view('admin.tenants.home');
    }

    /**
     * Get list of tenants.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function tenant_list(Request $request): JsonResponse
    {
        session_write_close();

        $query = Tenant::query();

        $totalCount = (clone $query)->count();

        if (! empty($request->search['value'])) {
            $query = $query->where(function (Builder $query) use ($request) {
                return $query->where('id', 'LIKE', '%' . $request->search['value'] . '%')
                    ->orWhere('domain', 'LIKE', '%' . $request->search['value'] . '%')
                    ->orWhere('user_id', 'LIKE', '%' . $request->search['value'] . '%')
                    ->orWhere('contract_id', 'LIKE', '%' . $request->search['value'] . '%');
            });
        }

        if (! empty($request->order)) {
            foreach ($request->order as $order) {
                switch ($request->columns[$order['column']]) {
                    case 'user':
                        $orderBy = 'user_id';

                        break;
                    case 'contract':
                        $orderBy = 'contract_id';

                        break;
                    case 'domain':
                        $orderBy = 'domain';

                        break;
                    case 'id':
                    default:
                        $orderBy = 'id';

                        break;
                }

                $query = $query->orderBy($orderBy, $order['dir']);
            }
        }

        $filteredCount = (clone $query)->count();

        $query = $query->offset($request->start)
            ->limit($request->length);

        return response()->json([
            'draw'            => (int) $request->draw,
            'recordsTotal'    => $totalCount,
            'recordsFiltered' => $filteredCount,
            'data'            => $query
                ->get()
                ->transform(function (Tenant $tenant) {
                    $info = '
<a class="btn btn-primary btn-sm" data-toggle="modal" data-target="#infoTenant' . $tenant->id . '"><i class="bi bi-eye"></i></a>
<div class="modal fade" id="infoTenant' . $tenant->id . '" tabindex="-1" aria-labelledby="infoTenant' . $tenant->id . 'Label" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="infoTenant' . $tenant->id . 'Label">' . __('interface.data.information') . ' (' . $tenant->domain . ')</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <i class="bi bi-x"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group row">
                    <label for="domain" class="col-md-4 col-form-label text-md-right">' . __('interface.data.database_size') . '</label>

                    <div class="col-md-8 col-form-label">
                        <span class="badge badge-primary">' . number_format($tenant->size, 2) . ' MB</span>
                    </div>
                </div>
                <hr>
                <div class="form-group row">
                    <label for="domain" class="col-md-4 col-form-label text-md-right">' . __('interface.data.database_driver') . '</label>

                    <div class="col-md-8 col-form-label">
                        ' . $tenant->database_driver . '
                    </div>
                </div>
                ' . (! empty($tenant->database_url) ? ' <div class="form-group row">
                    <label for="domain" class="col-md-4 col-form-label text-md-right">' . __('interface.data.database_url') . '</label>

                    <div class="col-md-8 col-form-label">
                        ' . $tenant->database_url . '
                    </div>
                </div>' : '') . '
                ' . (! empty($tenant->database_unix_socket) ? '<div class="form-group row">
                    <label for="domain" class="col-md-4 col-form-label text-md-right">' . __('interface.data.database_unix_socket') . '</label>

                    <div class="col-md-8 col-form-label">
                        ' . $tenant->database_unix_socket . '
                    </div>
                </div>' : '') . '

                <div class="form-group row">
                    <label for="domain" class="col-md-4 col-form-label text-md-right">' . __('interface.data.database_host') . '</label>

                    <div class="col-md-8 col-form-label">
                        ' . $tenant->database_host . ':' . $tenant->database_port . '
                    </div>
                </div>
                <hr>
                <div class="form-group row">
                    <label for="domain" class="col-md-4 col-form-label text-md-right">' . __('interface.data.database_username') . '</label>

                    <div class="col-md-8 col-form-label">
                        ' . $tenant->database_username . '
                    </div>
                </div>
                <div class="form-group row">
                    <label for="domain" class="col-md-4 col-form-label text-md-right">' . __('interface.data.database_password') . '</label>

                    <div class="col-md-8 col-form-label">
                        ' . $tenant->database_password . '
                    </div>
                </div>
                <hr>
                <div class="form-group row">
                    <label for="domain" class="col-md-4 col-form-label text-md-right">' . __('interface.data.database_name') . '</label>

                    <div class="col-md-8 col-form-label">
                        ' . $tenant->database_database . '
                    </div>
                </div>
                ' . (! empty($tenant->database_prefix) ? '<div class="form-group row">
                    <label for="domain" class="col-md-4 col-form-label text-md-right">' . __('interface.data.database_prefix') . '</label>

                    <div class="col-md-8 col-form-label">
                        ' . $tenant->database_prefix . '
                    </div>
                </div>' : '') . '
                <div class="form-group row">
                    <label for="domain" class="col-md-4 col-form-label text-md-right">' . __('interface.data.database_charset') . '</label>

                    <div class="col-md-8 col-form-label">
                        ' . $tenant->database_charset . '
                    </div>
                </div>
                <div class="form-group row">
                    <label for="domain" class="col-md-4 col-form-label text-md-right">' . __('interface.data.database_collation') . '</label>

                    <div class="col-md-8 col-form-label">
                        ' . $tenant->database_collation . '
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">' . __('interface.actions.close') . '</button>
            </div>
        </div>
    </div>
</div>
';

                    $edit = '
<a class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editTenant' . $tenant->id . '"><i class="bi bi-pencil-square"></i></a>
<div class="modal fade" id="editTenant' . $tenant->id . '" tabindex="-1" aria-labelledby="editTenant' . $tenant->id . 'Label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title" id="editTenant' . $tenant->id . 'Label">' . __('interface.actions.edit') . ' (' . $tenant->domain . ')</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <i class="bi bi-x"></i>
                </button>
            </div>
            <form action="' . route('admin.tenants.update', $tenant->id) . '" method="post">
                <div class="modal-body">
                    <input type="hidden" name="_token" value="' . csrf_token() . '" />
                    <input type="hidden" name="tenant_id" value="' . $tenant->id . '" />

                    <div class="form-group row">
                        <label for="domain" class="col-md-4 col-form-label text-md-right">' . __('interface.data.domain') . '</label>

                        <div class="col-md-8">
                            <input id="domain" type="text" class="form-control" name="domain" value="' . $tenant->domain . '">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="user_id" class="col-md-4 col-form-label text-md-right">' . __('interface.data.user_id') . '</label>

                        <div class="col-md-8">
                            <input id="user_id" type="number" class="form-control" name="user_id" value="' . $tenant->user_id . '">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="contract_id" class="col-md-4 col-form-label text-md-right">' . __('interface.data.contract_id') . '</label>

                        <div class="col-md-8">
                            <input id="contract_id" type="number" class="form-control" name="contract_id" value="' . $tenant->contract_id . '">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-warning"><i class="bi bi-pencil-square"></i> ' . __('interface.actions.edit') . '</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">' . __('interface.actions.close') . '</button>
                </div>
            </form>
        </div>
    </div>
</div>
';

                    return (object) [
                        'id'       => $tenant->id,
                        'user'     => ! empty($user = $tenant->user) ? $user->realName : __('interface.misc.not_available'),
                        'contract' => ! empty($contract = $tenant->contract) ? $contract->number : __('interface.misc.not_available'),
                        'domain'   => $tenant->domain,
                        'info'     => $info,
                        'edit'     => $edit,
                        'delete'   => '<a href="' . route('admin.tenants.delete', $tenant->id) . '" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i></a>',
                    ];
                }),
        ]);
    }

    // TODO: Take the webserver port of the main application into account when setting app url
    // TODO: Move extended tenant creation, modification and deletion to model itself

    /**
     * Create a new tenant.
     *
     * @param Request $request
     *
     * @throws ValidationException
     *
     * @return RedirectResponse
     */
    public function tenant_add(Request $request): RedirectResponse
    {
        Validator::make($request->toArray(), [
            'user_id'     => ['integer', 'nullable'],
            'contract_id' => ['integer', 'nullable'],
            'domain'      => ['required', 'string', 'max:255'],
        ])->validate();

        /* @var Tenant|null $tenant */
        if (
            ! Tenant::where('domain', '=', $request->domain)->exists() &&
            ! empty(
                $tenant = Tenant::create([
                    'user_id'     => ! empty($request->user_id) ? $request->user_id : null,
                    'contract_id' => ! empty($request->contract_id) ? $request->contract_id : null,
                    'domain'      => $request->domain,
                ])
            ) &&
            (
                empty($user = User::find($request->user_id)) ||
                $user->role === 'customer'
            )
        ) {
            $name     = config('database.tenants.prefix') . (! empty($request->user_id) ? $request->user_id : '0') . $tenant->id;
            $password = Str::random();

            DB::connection('base')->statement('CREATE DATABASE ' . $name);

            DB::connection('base')->statement('CREATE USER \'' . $name . '\'@\'%\' IDENTIFIED BY \'' . $password . '\'');

            DB::connection('base')->statement('GRANT ALL PRIVILEGES ON ' . $name . '.* TO \'' . $name . '\'@\'%\'');

            DB::connection('base')->statement('FLUSH PRIVILEGES');

            $tenant->update([
                'database_driver'         => config('database.connections.mysql.driver'),
                'database_url'            => config('database.connections.mysql.url'),
                'database_host'           => config('database.connections.mysql.host'),
                'database_port'           => config('database.connections.mysql.port'),
                'database_database'       => $name,
                'database_username'       => $name,
                'database_password'       => $password,
                'database_unix_socket'    => config('database.connections.mysql.unix_socket'),
                'database_charset'        => config('database.connections.mysql.charset'),
                'database_collation'      => config('database.connections.mysql.collation'),
                'database_prefix'         => config('database.connections.mysql.prefix'),
                'database_prefix_indexes' => config('database.connections.mysql.prefix_indexes'),
                'database_strict'         => config('database.connections.mysql.strict'),
                'database_engine'         => config('database.connections.mysql.engine'),
                'redis_prefix'            => $name . '-',
            ]);

            Config::set('database.connections.' . $name, [
                'driver'           => $tenant->database_driver,
                'url'              => $tenant->database_url,
                'host'             => $tenant->database_host,
                'port'             => $tenant->database_port,
                'database'         => $tenant->database_database,
                'username'         => $tenant->database_username,
                'password'         => $tenant->database_password,
                'unix_socket'      => $tenant->database_unix_socket,
                'database_charset' => $tenant->database_charset,
                'collation'        => $tenant->database_collation,
                'prefix'           => $tenant->database_prefix,
                'prefix_indexes'   => $tenant->database_prefix_indexes ? '1' : '0',
                'database_strict'  => $tenant->database_strict ? '1' : '0',
                'database_engine'  => $tenant->database_engine,
                'redis_prefix'     => $tenant->redis_prefix,
                'options'          => extension_loaded('pdo_mysql') ? array_filter([
                    PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
                    PDO::ATTR_PERSISTENT   => true,
                ]) : [],
            ]);

            Artisan::call('migrate', [
                '--database' => $name,
            ]);

            DB::connection($name)->update('UPDATE settings SET value = ? WHERE setting = ?', [
                encrypt('https://' . $tenant->domain),
                'app.url',
            ]);

            return redirect()->route('admin.tenants')->with('success', __('interface.messages.tenant_added'));
        } elseif (! empty($tenant)) {
            $tenant->delete();
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }

    /**
     * Update an existing tenant.
     *
     * @param Request $request
     *
     * @throws ValidationException
     *
     * @return RedirectResponse
     */
    public function tenant_update(Request $request): RedirectResponse
    {
        Validator::make($request->toArray(), [
            'tenant_id'   => ['required', 'integer'],
            'user_id'     => ['integer', 'nullable'],
            'contract_id' => ['integer', 'nullable'],
            'domain'      => ['required', 'string', 'max:255'],
        ])->validate();

        /* @var Tenant $tenant */
        if (! empty($tenant = Tenant::find($request->tenant_id))) {
            $oldDomain = (clone $tenant)->domain;

            $tenant->update([
                'user_id'     => ! empty($request->user_id) ? $request->user_id : null,
                'contract_id' => ! empty($request->contract_id) ? $request->contract_id : null,
                'domain'      => $request->domain,
            ]);

            if ($oldDomain !== $tenant->domain) {
                Config::set('database.connections.' . $tenant->database_database, [
                    'driver'           => $tenant->database_driver,
                    'url'              => $tenant->database_url,
                    'host'             => $tenant->database_host,
                    'port'             => $tenant->database_port,
                    'database'         => $tenant->database_database,
                    'username'         => $tenant->database_username,
                    'password'         => $tenant->database_password,
                    'unix_socket'      => $tenant->database_unix_socket,
                    'database_charset' => $tenant->database_charset,
                    'collation'        => $tenant->database_collation,
                    'prefix'           => $tenant->database_prefix,
                    'prefix_indexes'   => $tenant->database_prefix_indexes ? '1' : '0',
                    'database_strict'  => $tenant->database_strict ? '1' : '0',
                    'database_engine'  => $tenant->database_engine,
                    'redis_prefix'     => $tenant->redis_prefix,
                    'options'          => extension_loaded('pdo_mysql') ? array_filter([
                        PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
                        PDO::ATTR_PERSISTENT   => true,
                    ]) : [],
                ]);

                DB::connection($tenant->database_database)->update('UPDATE settings SET value = ? WHERE setting = ?', [
                    encrypt('https://' . $tenant->domain),
                    'app.url',
                ]);
            }

            return redirect()->back()->with('success', __('interface.messages.tenant_updated'));
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }

    /**
     * Delete an existing tenant.
     *
     * @param int $id
     *
     * @throws ValidationException
     *
     * @return RedirectResponse
     */
    public function tenant_delete(int $id): RedirectResponse
    {
        Validator::make([
            'tenant_id' => $id,
        ], [
            'tenant_id' => ['required', 'integer'],
        ])->validate();

        /* @var Tenant $tenant */
        if (! empty($tenant = Tenant::find($id))) {
            DB::connection('base')->statement('DROP USER \'' . $tenant->database_username . '\'@\'%\'');

            DB::connection('base')->statement('DROP DATABASE ' . $tenant->database_database);

            $tenant->delete();

            return redirect()->back()->with('success', __('interface.messages.tenant_deleted'));
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }
}
