<?php

namespace App\Helpers;

use App\Models\Accounting\Invoice\Invoice;
use App\Models\Accounting\Invoice\InvoiceHistory;
use App\Models\Accounting\Prepaid\PrepaidHistory;
use App\Models\Payment;
use App\Models\PaymentGatewaySetting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

/**
 * Class Products.
 *
 * This class is the helper for handling products.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class Products
{
    /**
     * Get a list of available products as class instances
     * of the handlers.
     *
     * @return Collection
     */
    public static function list(): Collection
    {
        $list = collect();

        collect(scandir(__DIR__ . '/../Products'))->reject(function (string $path) {
            return $path == '.' || $path == '..' || str_contains($path, '.php');
        })->transform(function (string $folder) use (&$list) {
            ClassFinder::getClassesInNamespace('App\Products\\' . $folder)->transform(function (string $classPath) {
                return new $classPath();
            })->each(function ($method) use (&$list) {
                $list->put($method->technicalName(), $method);
            });
        });

        return $list;
    }
}
