<?php

namespace App\Http\Middleware;

use App\Models\Shop\Configurator\ShopConfiguratorCategory;
use App\Models\Shop\Configurator\ShopConfiguratorForm;
use Closure;
use Illuminate\Http\Request;

/**
 * Class InjectShopCategoryOrProduct
 *
 * This class is the middleware for injecting shop category or product data.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class InjectShopCategoryOrProduct
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param Closure $next
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        ShopConfiguratorCategory::query()->each(function (ShopConfiguratorCategory $category) use (&$request) {
            if ($request->getRequestUri() === $category->fullRoute) {
                $request->attributes->add(['category' => $category]);

                return false;
            }

            return true;
        });

        ShopConfiguratorForm::query()->each(function (ShopConfiguratorForm $form) use (&$request) {
            if ($request->getRequestUri() === $form->fullRoute) {
                $request->attributes->add(['form' => $form]);

                return false;
            }

            return true;
        });

        return $next($request);
    }
}
