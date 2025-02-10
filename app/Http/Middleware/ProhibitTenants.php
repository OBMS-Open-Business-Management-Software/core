<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

/**
 * Class ProhibitTenants
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
     * @param  Request  $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (! empty($request->tenant)) {
            return redirect()->back()->with('warning', __('You don\'t have the permission to view this page.'));
        }

        return $next($request);
    }
}
