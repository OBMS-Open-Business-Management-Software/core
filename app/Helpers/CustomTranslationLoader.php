<?php

namespace App\Helpers;

use Illuminate\Translation\FileLoader;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;

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
        $customTranslations = [];

        if (File::isDirectory(__DIR__ . '/../../resources/themes/' . config('app.theme') . '/lang')) {
            collect(scandir(__DIR__ . '/../../resources/themes/' . config('app.theme') . '/lang'))->reject(function (string $path) {
                return $path == '.' || $path == '..' || str_contains($path, '.php');
            })->each(function (string $lang) use ($locale, &$customTranslations) {
                collect(scandir(__DIR__ . '/../../resources/themes/' . config('app.theme') . '/lang/' . $lang))->reject(function (string $group) use ($locale) {
                    return !str_contains($group, '.php');
                })->transform(function (string $group) {
                    return str_replace('.php', '', $group);
                })->each(function (string $group) use ($lang, &$customTranslations) {
                    $customTranslations = [
                        ...$customTranslations,
                        ...collect(Arr::dot(require __DIR__ . '/../../resources/themes/' . config('app.theme') . '/lang/' . $lang . '/' . $group . '.php'))->mapWithKeys(function ($value, string $key) use ($group) {
                            return [
                                $group . '.' . $key => $value,
                            ];
                        })->toArray()
                    ];
                });
            });
        }

        collect(scandir(__DIR__ . '/../PaymentGateways'))->reject(function (string $path) {
            return $path == '.' || $path == '..' || str_contains($path, '.php');
        })->each(function (string $folder) use ($locale, &$customTranslations) {
            collect(scandir(__DIR__ . '/../PaymentGateways/' . $folder . '/Languages'))->reject(function (string $path) use ($locale) {
                return $path == '.' || $path == '..' || str_contains($path, '.php') || $path !== $locale;
            })->each(function (string $lang) use ($folder, &$customTranslations) {
                collect(scandir(__DIR__ . '/../PaymentGateways/' . $folder . '/Languages/' . $lang))->reject(function (string $group) {
                    return !str_contains($group, '.php');
                })->transform(function (string $group) {
                    return str_replace('.php', '', $group);
                })->each(function (string $group) use ($folder, $lang, &$customTranslations) {
                    $customTranslations = [
                        ...$customTranslations,
                        ...collect(Arr::dot(require __DIR__ . '/../PaymentGateways/' . $folder . '/Languages/' . $lang . '/' . $group . '.php'))->mapWithKeys(function (string $value, string $key) use ($group) {
                            return [
                                $group . '.' . $key => empty($value) ? $group . '.' . $key : $value,
                            ];
                        })->toArray()
                    ];
                });
            });
        });

        collect(scandir(__DIR__ . '/../Products'))->reject(function (string $path) {
            return $path == '.' || $path == '..' || str_contains($path, '.php');
        })->each(function (string $folder) use ($locale, &$customTranslations) {
            collect(scandir(__DIR__ . '/../Products/' . $folder . '/Languages'))->reject(function (string $path) use ($locale) {
                return $path == '.' || $path == '..' || str_contains($path, '.php') || $path !== $locale;
            })->each(function (string $lang) use ($folder, &$customTranslations) {
                collect(scandir(__DIR__ . '/../Products/' . $folder . '/Languages/' . $lang))->reject(function (string $group) {
                    return !str_contains($group, '.php');
                })->transform(function (string $group) {
                    return str_replace('.php', '', $group);
                })->each(function (string $group) use ($folder, $lang, &$customTranslations) {
                    $customTranslations = [
                        ...$customTranslations,
                        ...collect(Arr::dot(require __DIR__ . '/../Products/' . $folder . '/Languages/' . $lang . '/' . $group . '.php'))->mapWithKeys(function (string $value, string $key) use ($group) {
                            return [
                                $group . '.' . $key => empty($value) ? $group . '.' . $key : $value,
                            ];
                        })->toArray()
                    ];
                });
            });
        });

        return collect($customTranslations)->map(function ($value, $key) {
            if (is_array($value)) {
                return $key;
            }

            return $value;
        });
    }
}
