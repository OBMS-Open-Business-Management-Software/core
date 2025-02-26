<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class APIAuthController extends APIBaseController
{
    /**
     * Login api.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function login(Request $request)
    {
        if (
            Auth::attempt([
                'email'    => $request->email,
                'password' => $request->password,
            ])
        ) {
            $user = Auth::user();

            if (
                $user->role === 'api' &&
                ! $user->locked
            ) {
                try {
                    $success['token'] = $user->createToken(config('app.name'))-> accessToken;
                    $success['name']  = $user->name;

                    return $this->sendResponse($success, 'User login successful.');
                } catch (Exception $exception) {
                    return $this->sendError('Unauthorized.', [
                        'error' => 'Unauthorized',
                    ]);
                }
            } else {
                return $this->sendError('Unauthorized.', [
                    'error' => 'Unauthorized',
                ]);
            }
        } else {
            return $this->sendError('Unauthorized.', [
                'error' => 'Unauthorized',
            ]);
        }
    }
}
