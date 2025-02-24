<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Content\Page;
use Closure;
use Illuminate\Http\Request;

/**
 * Class InjectNavigateables.
 *
 * This class is the middleware for injecting navigateable pages.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class InjectNavigateables
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
        $request->attributes->add(['navigateables' => Page::navigateable()->get()]);

        return $next($request);
    }
}
