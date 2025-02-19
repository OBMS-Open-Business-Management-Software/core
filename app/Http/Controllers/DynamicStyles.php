<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Cache;
use ScssPhp\ScssPhp\Compiler;

class DynamicStyles extends Controller
{
    public function __invoke()
    {
        $cacheKey = 'stylesheet-' . config('app.theme', 'aurora') . '-' . str_replace(['/', ':'], '_', str_replace(['http://', 'https://'], '', config('app.url')));

        if ($stylesheet = Cache::get($cacheKey)) {
            return response($stylesheet, 200, ['Content-Type' => 'text/css']);
        }

        $scss = view('styles')->render();
        $compiler = new Compiler();
        $compiler->setImportPaths([
            resource_path('themes/' . config('app.theme', 'aurora') . '/scss/'),
            base_path('node_modules/'),
        ]);
        $compiledCss = $compiler->compileString($scss)->getCss();

        Cache::forever($cacheKey, $compiledCss);

        return response($compiledCss, 200, ['Content-Type' => 'text/css']);
    }
}
