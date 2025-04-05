<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

/**
 * Middleware to set the application locale based on session or default configuration.
 *
 * This middleware ensures that the application uses the correct locale for translations
 * and date/time formatting. It checks the session for a user's preferred locale,
 * falls back to the application's default locale if none is set, and validates
 * that the locale is supported by the application.
 */
class SetLocale
{
    /**
     * List of supported locales in the application.
     *
     * @var array<string>
     */
    private const SUPPORTED_LOCALES = ['en', 'de'];

    /**
     * Handle an incoming request.
     *
     * @param Request $request The incoming request
     * @param Closure $next    The next middleware in the stack
     *
     * @return mixed The response from the next middleware
     */
    public function handle(Request $request, Closure $next)
    {
        $locale = Session::get('locale', config('app.locale'));

        // Validate and sanitize the locale
        if (!in_array($locale, self::SUPPORTED_LOCALES)) {
            $locale = config('app.fallback_locale', 'en');
        }

        App::setLocale($locale);

        return $next($request);
    }
}
