<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Helpers\Products;
use Closure;
use Illuminate\Http\Request;

/**
 * Class IdentifyCustomerProductLists.
 *
 * This class is the middleware for identifying the admin customer lists.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class IdentifyCustomerProductLists
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
            return !$handler->ui()->customer;
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
