<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class SocialiteController extends Controller
{
    public function redirect()
    {
        return Socialite::driver(config('sso.provider'))
            ->redirectUrl(route('auth.sso.callback'))
            ->with([
                'client_id'     => config('sso.client.id'),
                'client_secret' => config('sso.client.secret'),
                ...(config('sso.provider') === 'microsoft' ? [
                    'tenant' => config('sso.tenant'),
                ] : []),
            ])
            ->redirect();
    }

    public function callback()
    {
        try {
            $user = Socialite::driver(config('sso.provider'))
                ->redirectUrl(route('auth.sso.callback'))
                ->with([
                    'client_id'     => config('sso.client.id'),
                    'client_secret' => config('sso.client.secret'),
                    ...(config('sso.provider') === 'microsoft' ? [
                        'tenant' => config('sso.tenant'),
                    ] : []),
                ])
                ->user();
        } catch (Exception $e) {
            return redirect()->route('login');
        }

        $existingUser = User::where('email', $user->getEmail())
            ->whereIn('role', [
                'admin',
                'employee',
            ])
            ->first();

        if ($existingUser) {
            if (!$existingUser->email_verified_at) {
                $existingUser->update([
                    'email_verified_at' => Carbon::now(),
                ]);
            }

            Auth::login($existingUser);
        }

        return redirect()->route('login');
    }
}
