<?php

namespace App\Http;

use App\Http\Middleware\Authenticate;
use App\Http\Middleware\EncryptCookies;
use App\Http\Middleware\InjectNavigateables;
use App\Http\Middleware\InjectShopCategoryOrProduct;
use App\Http\Middleware\PasswordChangeRequired;
use App\Http\Middleware\PreventRequestsDuringMaintenance;
use App\Http\Middleware\ProhibitTenants;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Http\Middleware\RequireAcceptedPages;
use App\Http\Middleware\RequireAdminRole;
use App\Http\Middleware\RequireAPIRole;
use App\Http\Middleware\RequireCustomerRole;
use App\Http\Middleware\RequireEmployeeRole;
use App\Http\Middleware\RequireUnlockedProfile;
use App\Http\Middleware\TrimStrings;
use App\Http\Middleware\TrustProxies;
use App\Http\Middleware\IdentifyTenant;
use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Auth\Middleware\AuthenticateWithBasicAuth;
use Illuminate\Auth\Middleware\Authorize;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Auth\Middleware\RequirePassword;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull;
use Illuminate\Foundation\Http\Middleware\ValidatePostSize;
use Illuminate\Http\Middleware\SetCacheHeaders;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Routing\Middleware\ValidateSignature;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middlewares are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        // \App\Http\Middleware\TrustHosts::class,
        TrustProxies::class,
        PreventRequestsDuringMaintenance::class,
        ValidatePostSize::class,
        TrimStrings::class,
        ConvertEmptyStringsToNull::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
            // \Illuminate\Session\Middleware\AuthenticateSession::class,
            ShareErrorsFromSession::class,
            VerifyCsrfToken::class,
            SubstituteBindings::class,
            IdentifyTenant::class,
            InjectNavigateables::class,
        ],

        'api' => [
            // \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            'throttle:api',
            SubstituteBindings::class,
            IdentifyTenant::class,
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middlewares may be assigned to group or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth' => Authenticate::class,
        'auth.basic' => AuthenticateWithBasicAuth::class,
        'cache.headers' => SetCacheHeaders::class,
        'can' => Authorize::class,
        'guest' => RedirectIfAuthenticated::class,
        'password.confirm' => RequirePassword::class,
        'signed' => ValidateSignature::class,
        'throttle' => ThrottleRequests::class,
        'verified' => EnsureEmailIsVerified::class,
        'password.check_reset' => PasswordChangeRequired::class,
        'tenant' => IdentifyTenant::class,
        'tenant.prohibit' => ProhibitTenants::class,
        'role.customer' => RequireCustomerRole::class,
        'role.employee' => RequireEmployeeRole::class,
        'role.admin' => RequireAdminRole::class,
        'role.api' => RequireAPIRole::class,
        'unlocked' => RequireUnlockedProfile::class,
        'accepted' => RequireAcceptedPages::class,
        'navigateables' => InjectNavigateables::class,
        'shop.categoryOrProduct' => InjectShopCategoryOrProduct::class,
    ];
}
