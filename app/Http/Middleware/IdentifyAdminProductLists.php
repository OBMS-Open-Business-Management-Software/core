<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Helpers\Products;
use Closure;
use Illuminate\Http\Request;

/**
 * Class IdentifyAdminProductLists.
 *
 * This class is the middleware for identifying the admin product lists.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class IdentifyAdminProductLists
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
        $results = [];

        $products = Products::list()->reject(function ($handler) {
            return !$handler->ui()->admin;
        })->each(function ($handler) use (&$results) {
            $results[] = (object) [
                'name' => $handler->name(),
                'icon' => $handler->icon(),
                'slug' => $handler->technicalName(),
            ];
        });

        $request->attributes->add(['products' => $results]);

        return $next($request);
    }
}
