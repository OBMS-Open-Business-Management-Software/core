<?php

declare(strict_types=1);

namespace App\Helpers;

use Illuminate\Support\Collection;

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
            return $path == '.' || $path == '..' || $path == '.gitignore' || str_contains($path, '.php');
        })->transform(function (string $folder) use (&$list) {
            $classPath = 'OBMS\\Products\\' . $folder . '\\Handler';
            $service   = new $classPath();

            $list->put($service->technicalName(), $service);
        });

        return $list;
    }
}
