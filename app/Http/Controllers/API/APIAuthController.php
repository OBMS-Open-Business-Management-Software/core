<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Traits\API\SendsResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class APIAuthController
{
    use SendsResponse;

    /**
     * Login api.
     *
     * @OA\Post(
     *     path="/api/login",
     *     summary="Authenticate user",
     *     description="Authenticate user and return a token",
     *     tags={"Authentication"},
     *
     *     @OA\RequestBody(
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="email", type="string", example="user@example.com"),
     *             @OA\Property(property="password", type="string", example="password")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="User login successful.",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiI3IiwianRpIjoiZjYzY2NlMjMyMGI0NjQ4ZTQzYzZlNDY3YzhmZWVkNDI3ODJjMDhjZGUzNWZlNWZkZWE5MmYzNDdmNWY2ZWMxYmMxM2NjNzVhMDVlMGJhNzMiLCJpYXQiOjE3NDA3Nzk0MDAuMjM1NzY1LCJuYmYiOjE3NDA3Nzk0MDAuMjM1NzY4LCJleHAiOjE3NzIzMTU0MDAuMjA5NDU5LCJzdWIiOiIzIiwic2NvcGVzIjpbXX0.cZfYgJ22uOjLsRLrbXr1eN0kXys3PPllVaQd27LyvLT_eoFTKZMEeBgnzI9DEar73HOc61t2FnokC2Y5X1fvUluN2N-K5M4BH-10cwK0I7Ch6NXHKrLww0B7lsPKoIu7nSvDWiydCY0HkzRWq-UpdODTafp6HIisF_T60sVfwKa1PxwkH4eTZ96HDo17wbIWnkHs5WX4e6z0GzxJ8QCavoZL3JzR2MUWUCGM52BICppe5EZorxW9iWHyWKs0sufUZgBLLFLqz5_Tz4RTnMAMkcimX41o-XBQeMmz1juJlRZ3LtlpOwFhy1Ixj9rkNtNz1UxMPq8fgqbMhYSgUHL1aTFT1qzrn6s0HP38bnnAsWj2V1OW4vftZF9jasvzn6yWl59Hfym4H1nUSf8iyFF9MxYZOJVFjqEwpNPZvL1zEYGETtAKeHyVzpL6ytj9xUE71LAqbTchwBLVUgCvLob1hhSkxg3-CBjHXKX_Az8nvOXiF62iw3Y1U7hjZLpYzU1HioqOkTsdwtbTyssqHYdU3OM_xr4lQkdwpHcFliwthUBdxzMWybtVxTRQUwQ-EjuJt3GRN4xKlWfkuDEHPWGxXr8Huz3lEtFc1q5fMex9i1fD0egS6wHw6BEBxAqJxDCfpbo7swRtrnvdDKBrsMId6dn6wxEQJNBqOvp_HnKqmEI"),
     *                 @OA\Property(property="name", type="string", example="API")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(response=403, ref="#/components/responses/ForbiddenResponse")
     * )
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

                    return $this->sendResponse($success, 'User login successful');
                } catch (Exception $exception) {
                }
            }
        }

        return $this->sendError(403, 'Forbidden', []);
    }
}
