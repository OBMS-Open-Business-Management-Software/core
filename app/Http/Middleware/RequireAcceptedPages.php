<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Class RequireAcceptedPages
 *
 * This class is the middleware for checking for page content acceptance.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class RequireAcceptedPages
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
        if (! Auth::user()->accepted) {
            return redirect()->route('customer.accept')->with('warning', __('Please accept our terms and conditions before continuing.'));
        }

        return $next($request);
    }
}
