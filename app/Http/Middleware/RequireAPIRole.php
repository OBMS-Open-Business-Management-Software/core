<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Class RequireAPIRole
 *
 * This class is the middleware for checking for api user role.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class RequireAPIRole
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
        if (Auth::user()->role !== 'api') {
            return redirect()->route('login')->with('warning', __('interface.misc.no_permission_hint'));
        }

        return $next($request);
    }
}
