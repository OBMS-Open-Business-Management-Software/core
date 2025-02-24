<?php

declare(strict_types=1);

namespace App\Helpers;

use Illuminate\Support\Collection;

/**
 * Class ClassFinder.
 *
 * This class is the helper for handling namespace scanning for
 * classes.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class ClassFinder
{
    public const appRoot = __DIR__ . '/../../';

    /**
     * Get a list of classes within a certain namespace.
     *
     * @param $namespace
     *
     * @return Collection
     */
    public static function getClassesInNamespace($namespace)
    {
        $files = scandir(self::getNamespaceDirectory($namespace));

        $classes = array_map(function ($file) use ($namespace) {
            return $namespace . '\\' . str_replace('.php', '', $file);
        }, $files);

        $list = array_filter($classes, function ($possibleClass) {
            return class_exists($possibleClass);
        });

        return collect($list);
    }

    /**
     * Get a list of defined namespaces from composer autoload
     * file.
     *
     * @return array
     */
    private static function getDefinedNamespaces()
    {
        $composerJsonPath = self::appRoot . 'composer.json';
        $composerConfig   = json_decode(file_get_contents($composerJsonPath));

        return (array) $composerConfig->autoload->{'psr-4'};
    }

    /**
     * Get the directory reference for a certain namespace.
     *
     * @param $namespace
     *
     * @return false|string
     */
    private static function getNamespaceDirectory($namespace)
    {
        $composerNamespaces = self::getDefinedNamespaces();

        $namespaceFragments          = explode('\\', $namespace);
        $undefinedNamespaceFragments = [];

        while ($namespaceFragments) {
            $possibleNamespace = implode('\\', $namespaceFragments) . '\\';

            if (array_key_exists($possibleNamespace, $composerNamespaces)) {
                return realpath(self::appRoot . $composerNamespaces[$possibleNamespace] . implode('/', $undefinedNamespaceFragments));
            }

            array_unshift($undefinedNamespaceFragments, array_pop($namespaceFragments));
        }

        return false;
    }
}
