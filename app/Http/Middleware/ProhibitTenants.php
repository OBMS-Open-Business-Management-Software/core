<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Class ProhibitTenants.
 *
 * This class is the middleware for checking for tenants.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class ProhibitTenants
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
        if (! empty($request->tenant)) {
            return redirect()->back()->with('warning', __('interface.misc.no_permission_hint'));
        }

        return $next($request);
    }
}
