<?php

declare(strict_types=1);

namespace App\Helpers;

use Illuminate\Support\Collection;

/**
 * Class Themes.
 *
 * This class is the helper for handling themes.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class Themes
{
    /**
     * Get a list of available themes.
     *
     * @return Collection
     */
    public static function list(): Collection
    {
        $list = collect();

        collect(scandir(resource_path('themes')))->reject(function (string $path) {
            return $path == '.' ||
                $path == '..' ||
                $path == '.gitignore' ||
                str_contains($path, '.php');
        })->each(function (string $path) use (&$list) {
            $list->put($path, (object) [
                'symlink'       => public_path('themes/' . $path),
                'base_path'     => resource_path('themes/' . $path),
                'node_modules'  => resource_path('themes/' . $path . '/node_modules'),
                'resource_path' => resource_path('themes/' . $path . '/src'),
            ]);
        });

        return $list;
    }

    /**
     * Create public path symlinks for all available themes.
     */
    public static function link(): void
    {
        self::list()
            ->reject(function ($paths) {
                return file_exists($paths->symlink) ||
                    is_link($paths->symlink) ||
                    !file_exists($paths->resource_path . '/public');
            })
            ->each(function ($paths) {
                symlink($paths->resource_path . '/public', $paths->symlink);
            });
    }
}
