<?php

declare(strict_types=1);

namespace App\Emails;

use App\Models\Profile\ProfileEmail;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest as Request;

class ProfileEmailVerificationRequest extends Request
{
    private ?ProfileEmail $object = null;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $this->user()
            ->profile
            ->emailAddresses
            ->whereNull('email_verified_at')
            ->each(function (ProfileEmail $email) {
                if (hash_equals((string) $this->route('id'), (string) $email->getKey())) {
                    $this->object = $email;
                }
            });

        if (empty($this->object)) {
            return false;
        }

        if (! hash_equals((string) $this->route('hash'), sha1($this->object->email))) {
            return false;
        }

        return true;
    }

    /**
     * Fulfill the email verification request.
     */
    public function fulfill()
    {
        if (! $this->user()->profile->emailAddresses->first()->hasVerifiedEmail()) {
            $this->user()->profile->emailAddresses->first()->markEmailAsVerified();

            event(new Verified($this->user()->profile->emailAddresses->first()));
        }
    }
}
