<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class SetLocale
{
    public function handle($request, Closure $next)
    {
        $locale = Session::get('locale', config('app.locale'));

        App::setLocale($locale);

        return $next($request);
    }
}
