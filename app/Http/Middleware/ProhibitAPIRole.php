<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

/**
 * Class ProhibitAPIRole
 *
 * This class is the middleware for checking for customer user role.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class ProhibitAPIRole
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
        if (Auth::user()->role == 'api') {
            $request->session()->flush();

            return redirect()->route('login')->with('warning', __('You don\'t have the permission to log in with this user account.'));
        }

        return $next($request);
    }
}
