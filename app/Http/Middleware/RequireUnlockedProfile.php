<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

/**
 * Class RequireUnlockedProfile.
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
     * @param Request $request
     * @param Closure $next
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::user()->locked) {
            Auth::logout();
            Session::flush();

            return redirect()->route('login')->with('warning', __('interface.messages.account_locked'));
        }

        return $next($request);
    }
}
