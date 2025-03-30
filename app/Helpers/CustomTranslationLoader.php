<?php

declare(strict_types=1);

namespace App\Helpers;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Translation\FileLoader;

/**
 * Class CustomTranslationLoader.
 *
 * This class is the helper for custom translation loading.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class CustomTranslationLoader extends FileLoader
{
    public function __construct(Filesystem $files, $path)
    {
        parent::__construct($files, $path);
    }

    public function load($locale, $group, $namespace = null)
    {
        $cacheKey     = "translation:{$locale}:{$group}";
        $translations = Cache::get($cacheKey);

        if ($translations) {
            return $translations;
        }

        $customTranslations = [];

        if (File::isDirectory(__DIR__ . '/../../lang/' . $locale)) {
            collect(scandir(__DIR__ . '/../../lang/' . $locale))->reject(function (string $group) {
                return !str_contains($group, '.php');
            })->transform(function (string $group) {
                return str_replace('.php', '', $group);
            })->each(function (string $group) use ($locale, &$customTranslations) {
                $customTranslations = [
                    ...$customTranslations,
                    ...collect(Arr::dot(require __DIR__ . '/../../lang/' . $locale . '/' . $group . '.php'))->mapWithKeys(function ($value, string $key) use ($group) {
                        return [
                            $group . '.' . $key => $value,
                        ];
                    })->toArray(),
                ];
            });
        }

        $themeTranslationsPath = theme_base(config('app.theme'), '/lang/' . $locale);

        if (File::isDirectory($themeTranslationsPath)) {
            collect(scandir($themeTranslationsPath))->reject(function (string $group) {
                return !str_contains($group, '.php');
            })->transform(function (string $group) {
                return str_replace('.php', '', $group);
            })->each(function (string $group) use ($themeTranslationsPath, &$customTranslations) {
                $customTranslations = [
                    ...$customTranslations,
                    ...collect(Arr::dot(require $themeTranslationsPath . '/' . $group . '.php'))->mapWithKeys(function ($value, string $key) use ($group) {
                        return [
                            $group . '.' . $key => $value,
                        ];
                    })->toArray(),
                ];
            });
        }

        PaymentGateways::list()->each(function ($gateway) use ($locale, &$customTranslations) {
            $this->loadTranslationsFromFolder($gateway->folderName() . '/Languages', $locale, $customTranslations);
        });

        Products::list()->each(function ($product) use ($locale, &$customTranslations) {
            $this->loadTranslationsFromFolder($product->folderName() . '/Languages', $locale, $customTranslations);
        });

        $translations = collect($customTranslations)->map(function ($value, $key) {
            if (is_array($value)) {
                return $key;
            }

            return $value;
        });

        Cache::forever($cacheKey, $translations);

        return $translations;
    }

    /**
     * Load translations from a specific folder structure.
     *
     * @param string $folder              The base folder path
     * @param string $locale              The locale to load
     * @param array  &$customTranslations Reference to the translations array
     */
    private function loadTranslationsFromFolder(string $folder, string $locale, array &$customTranslations): void
    {
        collect(scandir($folder))->reject(function (string $path) use ($locale) {
            return $path == '.' || $path == '..' || str_contains($path, '.php') || $path !== $locale;
        })->each(function (string $lang) use ($folder, &$customTranslations) {
            collect(scandir($folder . '/' . $lang))->reject(function (string $group) {
                return !str_contains($group, '.php');
            })->transform(function (string $group) {
                return str_replace('.php', '', $group);
            })->each(function (string $group) use ($folder, $lang, &$customTranslations) {
                $customTranslations = [
                    ...$customTranslations,
                    ...collect(Arr::dot(require $folder . '/' . $lang . '/' . $group . '.php'))->mapWithKeys(function (string $value, string $key) use ($group) {
                        return [
                            $group . '.' . $key => empty($value) ? $group . '.' . $key : $value,
                        ];
                    })->toArray(),
                ];
            });
        });
    }
}
