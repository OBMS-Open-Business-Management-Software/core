<?php

declare(strict_types=1);

namespace App\Providers;

use App\Helpers\CustomTranslationLoader;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;
use Illuminate\Translation\Translator;

class TranslationServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->extend('translator', function ($translator, $app) {
            return new Translator(new CustomTranslationLoader(new Filesystem(), $app['path.lang']), $app->getLocale());
        });
    }
}
