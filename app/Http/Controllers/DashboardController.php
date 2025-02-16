<?php

namespace App\Http\Controllers;

use App\Actions\Fortify\PasswordValidationRules;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;

class DashboardController extends Controller
{
    use PasswordValidationRules;

    /**
     * Show the application dashboard.
     *
     * @return Renderable
     */
    public function index(): Renderable
    {
        return view('home');
    }

    /**
     * Show the password reset.
     *
     * @return Renderable
     */
    public function setPassword(): Renderable
    {
        return view('auth.password-set');
    }

    public function submitPassword(Request $request)
    {
        Validator::make($request->toArray(), [
            'password' => $this->passwordRules(),
        ])->validate();

        Auth::user()->update([
            'password' => Hash::make($request->password),
            'must_change_password' => false,
        ]);

        return Redirect::route('redirect')->with('success', __('interface.messages.password_set'));
    }

    /**
     * Role-based redirection to protected customer or admin area.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function redirect(Request $request)
    {
        switch (Auth::user()->role) {
            case 'admin':
            case 'employee':
                return Redirect::route('admin.home');
            case 'customer':
            default:
                return Redirect::route('customer.home');
        }
    }
}
