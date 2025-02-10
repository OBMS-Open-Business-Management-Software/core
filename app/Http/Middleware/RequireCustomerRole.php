<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Class RequireCustomerRole
 *
 * This class is the middleware for checking for customer user role.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class RequireCustomerRole
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
        if (Auth::user()->role !== 'customer') {
            return redirect()->route('admin.home')->with('warning', __('You don\'t have the permission to view this page.'));
        }

        return $next($request);
    }
}
