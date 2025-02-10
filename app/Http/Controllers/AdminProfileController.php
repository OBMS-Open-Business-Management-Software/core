<?php

namespace App\Http\Controllers;

use App\Models\Address\Country;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AdminProfileController extends Controller
{
    /**
     * Show list of customers.
     *
     * @return Renderable
     */
    public function profile_index(): Renderable
    {
        return view('admin.profile.home', [
            'countries' => Country::all(),
        ]);
    }

    /**
     * Change the account details.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     *
     * @throws ValidationException
     */
    public function profile_update(Request $request): RedirectResponse
    {
        if (Auth::user()->email !== $request->email) {
            Validator::make($request->toArray(), [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'confirmed'],
            ])->validate();

            Auth::user()->update([
                'name' => $request->name,
                'email' => $request->email,
                'email_verified_at' => null,
            ]);
        } else {
            Validator::make($request->toArray(), [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email'],
            ])->validate();

            Auth::user()->update([
                'name' => $request->name,
            ]);
        }

        return redirect()->back()->with('success', __('interface.messages.profile_updated'));
    }

    /**
     * Change the account password.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     *
     * @throws ValidationException
     */
    public function profile_password(Request $request): RedirectResponse
    {
        Validator::make($request->toArray(), [
            'password_current' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ])->validate();

        Auth::user()->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->back()->with('success', __('interface.messages.password_changed'));
    }

    /**
     * Confirm a twofactor code.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function profile_2fa_confirm(Request $request)
    {
        $confirmed = $request->user()->confirmTwoFactorAuth($request->code);

        if (! $confirmed) {
            return redirect()->back()->with('warning', __('interface.messages.2fa_invalid'));
        }

        return redirect()->back();
    }
}
