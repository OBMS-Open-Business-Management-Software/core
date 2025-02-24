<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
     * @param Request $request
     * @param Closure $next
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::user()->role == 'api') {
            $request->session()->flush();

            return redirect()->route('login')->with('warning', __('interface.messages.login_prohibited'));
        }

        return $next($request);
    }
}
