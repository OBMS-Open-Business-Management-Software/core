<?php

declare(strict_types=1);

namespace App\Providers;

use App\Helpers\CustomPdfWrapper;
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

        collect(scandir(resource_path('themes')))->reject(function (string $path) {
            return $path == '.' ||
                $path == '..' ||
                str_contains($path, '.php') ||
                file_exists(public_path('themes/' . $path)) ||
                is_link(public_path('themes/' . $path)) ||
                !file_exists(resource_path('themes/' . $path . '/src/public'));
        })->each(function (string $theme) {
            symlink(resource_path('themes/' . $theme . '/src/public'), public_path('themes/' . $theme));
        });

        $theme     = config('app.theme', 'aurora');
        $themePath = resource_path('themes/' . $theme . '/src');

        if (File::isDirectory($themePath)) {
            View::addLocation($themePath);
        }
    }
}
