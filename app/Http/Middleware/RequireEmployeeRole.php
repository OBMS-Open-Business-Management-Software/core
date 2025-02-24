<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
     * @param Request $request
     * @param Closure $next
     *
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
            return redirect()->route('customer.home')->with('warning', __('interface.misc.no_permission_hint'));
        }

        return $next($request);
    }
}
