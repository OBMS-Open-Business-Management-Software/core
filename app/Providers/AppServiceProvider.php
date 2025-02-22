<?php

namespace App\Providers;

use Exception;
use Illuminate\Support\ServiceProvider;
use App\Helpers\CustomPdfWrapper;
use App\Models\Setting;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->extend('dompdf.wrapper', function ($pdf, $app) {
            return new CustomPdfWrapper($pdf->getDomPDF(), $app['config'], $app['files'], $app['view']);
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
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

        collect(scandir(resource_path('themes')))->reject(function (string $path) {
            return $path == '.' || $path == '..' || str_contains($path, '.php') || file_exists(public_path('themes/' . $path));
        })->each(function (string $theme) {
            symlink(resource_path('themes/' . $theme . '/public'), public_path('themes/' . $theme));
        });

        $theme = config('app.theme', 'aurora');
        $themePath = resource_path('themes/' . $theme);

        if (File::isDirectory($themePath)) {
            View::addLocation($themePath);
        }
    }
}
