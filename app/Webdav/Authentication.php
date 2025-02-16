<?php

namespace App\Webdav;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Sabre\DAV\Auth\Backend\AbstractBasic;

class Authentication extends AbstractBasic
{
    /**
     * Validate username and password for webdav authentication.
     *
     * @param string $username
     * @param string $password
     *
     * @return bool
     */
    public function validateUserPass($username, $password)
    {
        try {
            Validator::make([
                'username' => $username,
                'password' => $password,
            ], [
                'username' => ['email', 'required'],
                'password' => ['required', 'string', 'min:8'],
            ])->validate();
        } catch (ValidationException $exception) {
            return false;
        }

        return Auth::attempt([
            'email' => $username,
            'password' => $password,
        ]);
    }
}
