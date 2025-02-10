<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class APIAuthController extends APIBaseController
{
    /**
     * Login api
     *
     * @return Response
     */
    public function login(Request $request)
    {
        if (
            Auth::attempt([
                'email' => $request->email,
                'password' => $request->password,
            ])
        ) {
            $user = Auth::user();

            if (
                $user->role === 'api' &&
                ! $user->locked
            ) {
                $success['token'] =  $user->createToken('MyApp')-> accessToken;
                $success['name'] =  $user->name;

                return $this->sendResponse($success, 'User login successful.');
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
