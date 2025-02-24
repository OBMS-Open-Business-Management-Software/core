<?php

declare(strict_types=1);

namespace App\Actions\Fortify;

use App\Models\Content\Page;
use App\Models\Content\PageAcceptance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param array $input
     *
     * @throws ValidationException
     *
     * @return User
     */
    public function create(array $input)
    {
        Validator::make($input, [
            'name'  => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
            'password' => $this->passwordRules(),
        ])->validate();

        if (! empty($acceptable = Page::acceptable()->get())) {
            $validationRules = [];

            $acceptable->each(function (Page $page) use (&$validationRules) {
                $validationRules['accept_' . $page->id] = ['required', 'string'];
            });

            Validator::make($input, $validationRules)->validate();
        }

        $user = User::create([
            'name'     => $input['name'],
            'email'    => $input['email'],
            'password' => Hash::make($input['password']),
        ]);

        if (! empty($user)) {
            $request = request();

            $signedAt = Carbon::now();

            $acceptable->each(function (Page $page) use ($user, $request, $signedAt) {
                PageAcceptance::updateOrCreate([
                    'page_id'         => $page->id,
                    'page_version_id' => $page->latest->id,
                    'user_id'         => $user->id,
                    'user_agent'      => $request->server('HTTP_USER_AGENT'),
                    'ip'              => $request->ip(),
                    'signature'       => md5($page->id . $page->latest->id . $user->id . $request->server('HTTP_USER_AGENT') . $request->ip() . $signedAt),
                    'signed_at'       => $signedAt,
                ]);
            });
        }

        return $user;
    }
}
