<?php

namespace App\Providers;

use Illuminate\Translation\Translator;
use Illuminate\Support\ServiceProvider;
use App\Helpers\CustomTranslationLoader;
use Illuminate\Filesystem\Filesystem;

class TranslationServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->extend('translator', function ($translator, $app) {
            return new Translator(new CustomTranslationLoader(new Filesystem(), $app['path.lang']), $app->getLocale());
        });
    }
}
