<?php

declare(strict_types=1);

namespace App\Providers;

use App\Helpers\CustomPdfWrapper;
use App\Helpers\Themes;
use App\Models\Setting;
use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register()
    {
        $this->app->extend('dompdf.wrapper', function ($pdf, $app) {
            return new CustomPdfWrapper($pdf->getDomPDF(), $app['config'], $app['files'], $app['view']);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        try {
            if (Schema::hasTable('settings')) {
                $settings = Setting::all();

                foreach ($settings as $setting) {
                    Config::set($setting->setting, $setting->value);
                }
            }
        } catch (Exception $e) {
        }

        Themes::link();

        $theme     = config('app.theme', 'aurora');
        $themePath = resource_path('themes/' . $theme . '/src');

        if (File::isDirectory($themePath)) {
            View::addLocation($themePath);
        }
    }
}
