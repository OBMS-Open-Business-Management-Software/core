<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use PDO;

/**
 * Class RequireUnlockedProfile
 *
 * This class is the middleware for checking for an unlocked customer user.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class RequireUnlockedProfile
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
        if (Auth::user()->locked) {
            Auth::logout();
            Session::flush();
            ;

            return redirect()->route('login')->with('warning', __('Your customer account is currently locked. Please contact us for more information.'));
        }

        return $next($request);
    }
}
