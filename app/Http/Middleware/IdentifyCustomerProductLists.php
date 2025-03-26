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
                'name'    => $handler->name(),
                'icon'    => $handler->icon(),
                'slug'    => $handler->technicalName(),
                'service' => $handler->capabilities()->contains('service'),
            ];
        });

        $request->attributes->add(['products' => $results]);
        $request->attributes->add(['service_products' => collect($results)->reject(function ($handler) {
            return !$handler->service;
        })]);

        return $next($request);
    }
}
