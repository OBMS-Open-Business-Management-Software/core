<?php

declare(strict_types=1);

if (!function_exists('theme_asset')) {
    function theme_asset(string $theme, ?string $path = null): string
    {
        return str_replace(
            public_path(),
            '',
            theme_symlink($theme, $path)
        );
    }
}

if (!function_exists('theme_base')) {
    function theme_base(string $theme, ?string $path = null): string
    {
        return theme_paths($theme)->base . (
            $path ? (
                str_starts_with($path, '/') ?
                    $path :
                    '/' . $path
            ) : ''
        );
    }
}

if (!function_exists('theme_paths')) {
    function theme_paths(string $theme): object
    {
        return (object) [
            'base'     => resource_path('themes/' . $theme),
            'resource' => resource_path('themes/' . $theme . '/public'),
            'symlink'  => public_path('themes/' . $theme),
        ];
    }
}

if (!function_exists('theme_resource')) {
    function theme_resource(string $theme, ?string $path = null): string
    {
        return theme_paths($theme)->resource . (
            $path ? (
                str_starts_with($path, '/') ?
                    $path :
                    '/' . $path
            ) : ''
        );
    }
}

if (!function_exists('theme_symlink')) {
    function theme_symlink(string $theme, ?string $path = null): string
    {
        return theme_paths($theme)->symlink . (
            $path ? (
                str_starts_with($path, '/') ?
                    $path :
                    '/' . $path
            ) : ''
        );
    }
}
