<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use PDO;

/**
 * Class RequireEmployeeRole
 *
 * This class is the middleware for checking for employee user role.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class RequireEmployeeRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (
            ! in_array(Auth::user()->role, [
                'admin',
                'employee',
            ])
        ) {
            return redirect()->route('customer.home')->with('warning', __('You don\'t have the permission to view this page.'));
        }

        return $next($request);
    }
}
