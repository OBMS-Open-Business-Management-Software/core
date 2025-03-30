<?php

declare(strict_types=1);

namespace App\Providers;

use App\Helpers\CustomTranslationLoader;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;
use Illuminate\Translation\Translator;

/**
 * Class TranslationServiceProvider.
 *
 * This service provider extends Laravel's translation system with custom functionality
 * for loading translations from various sources including themes, payment gateways,
 * and products.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class TranslationServiceProvider extends ServiceProvider
{
    /**
     * Register the translation service.
     *
     * Extends the default translator with a custom loader that supports
     * loading translations from multiple sources.
     */
    public function register(): void
    {
        $this->app->extend('translator', function (Translator $translator, $app) {
            return new Translator(
                new CustomTranslationLoader(new Filesystem(), $app['path.lang']),
                $app->getLocale()
            );
        });
    }
}
