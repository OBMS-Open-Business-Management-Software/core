<?php

declare(strict_types=1);

namespace App\Models\Profile;

use App\Models\Address\Address;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Profile
 *
 * This class is the model for basic profile metadata.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @property int                        $id
 * @property int                        $user_id
 * @property string                     $firstname
 * @property string                     $lastname
 * @property string|null                $company
 * @property string|null                $tax_id
 * @property string|null                $vat_id
 * @property bool                       $verified
 * @property bool                       $primary
 * @property Carbon                     $created_at
 * @property Carbon                     $updated_at
 * @property Carbon                     $deleted_at
 * @property User|null                  $user
 * @property Collection<ProfileAddress> $addressLinks
 * @property Collection<ProfileHistory> $history
 * @property Collection<BankAccount>    $bankAccounts
 * @property Collection<ProfilePhone>   $phoneNumbers
 * @property Collection<ProfileEmail>   $emailAddresses
 * @property ProfileEmail|null          $contactEmailAddress
 * @property ProfileEmail|null          $billingEmailAddress
 * @property ProfilePhone|null          $contactPhoneNumber
 * @property ProfilePhone|null          $billingPhoneNumber
 * @property Address|null               $contactPostalAddress
 * @property Address|null               $billingPostalAddress
 * @property BankAccount|null           $primaryBankAccount
 */
class Profile extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_profiles';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var bool|string[]
     */
    protected $guarded = [
        'id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'verified' => 'boolean',
        'primary'  => 'boolean',
    ];

    /**
     * Relation to user.
     *
     * @return HasOne
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    /**
     * Relation to address links.
     *
     * @return HasMany
     */
    public function addressLinks(): HasMany
    {
        return $this->hasMany(ProfileAddress::class, 'profile_id', 'id');
    }

    /**
     * Relation to history.
     *
     * @return HasMany
     */
    public function history(): HasMany
    {
        return $this->hasMany(ProfileHistory::class, 'profile_id', 'id');
    }

    /**
     * Relation to bank accounts.
     *
     * @return HasMany
     */
    public function bankAccounts(): HasMany
    {
        return $this->hasMany(BankAccount::class, 'profile_id', 'id');
    }

    /**
     * Relation to phone numbers.
     *
     * @return HasMany
     */
    public function phoneNumbers(): HasMany
    {
        return $this->hasMany(ProfilePhone::class, 'profile_id', 'id');
    }

    /**
     * Relation to email addresses.
     *
     * @return HasMany
     */
    public function emailAddresses(): HasMany
    {
        return $this->hasMany(ProfileEmail::class, 'profile_id', 'id');
    }

    /**
     * Get contact email address.
     *
     * @return ProfileEmail|null
     */
    public function getcontactEmailAddressAttribute(): ?ProfileEmail
    {
        if (
            ! empty(
                $email = $this->emailAddresses
                    ->where('type', '=', 'contact')
                    ->first()
            )
        ) {
            return $email;
        } elseif (
            ! empty(
                $email = $this->emailAddresses
                    ->where('type', '=', 'all')
                    ->first()
            )
        ) {
            return $email;
        }

        return null;
    }

    /**
     * Get billing email address.
     *
     * @return ProfileEmail|null
     */
    public function getbillingEmailAddressAttribute(): ?ProfileEmail
    {
        if (
            ! empty(
                $email = $this->emailAddresses
                    ->where('type', '=', 'billing')
                    ->first()
            )
        ) {
            return $email;
        } elseif (
            ! empty(
                $email = $this->emailAddresses
                    ->where('type', '=', 'all')
                    ->first()
            )
        ) {
            return $email;
        }

        return null;
    }

    /**
     * Get contact phone number.
     *
     * @return ProfilePhone|null
     */
    public function getcontactPhoneNumberAttribute(): ?ProfilePhone
    {
        if (
            ! empty(
                $phone = $this->phoneNumbers
                    ->where('type', '=', 'contact')
                    ->first()
            )
        ) {
            return $phone;
        } elseif (
            ! empty(
                $phone = $this->phoneNumbers
                    ->where('type', '=', 'all')
                    ->first()
            )
        ) {
            return $phone;
        }

        return null;
    }

    /**
     * Get billing phone number.
     *
     * @return ProfilePhone|null
     */
    public function getbillingPhoneNumberAttribute(): ?ProfilePhone
    {
        if (
            ! empty(
                $phone = $this->phoneNumbers
                    ->where('type', '=', 'billing')
                    ->first()
            )
        ) {
            return $phone;
        } elseif (
            ! empty(
                $phone = $this->phoneNumbers
                    ->where('type', '=', 'all')
                    ->first()
            )
        ) {
            return $phone;
        }

        return null;
    }

    /**
     * Get contact postal address.
     *
     * @return Address|null
     */
    public function getcontactPostalAddressAttribute(): ?Address
    {
        if (
            ! empty(
                $address = $this->addressLinks
                    ->where('type', '=', 'contact')
                    ->first()
            )
        ) {
            return $address->address ?? null;
        } elseif (
            ! empty(
                $address = $this->addressLinks
                    ->where('type', '=', 'all')
                    ->first()
            )
        ) {
            return $address->address ?? null;
        }

        return null;
    }

    /**
     * Get billing postal address.
     *
     * @return Address|null
     */
    public function getbillingPostalAddressAttribute(): ?Address
    {
        if (
            ! empty(
                $address = $this->addressLinks
                    ->where('type', '=', 'billing')
                    ->first()
            )
        ) {
            return $address->address ?? null;
        } elseif (
            ! empty(
                $address = $this->addressLinks
                    ->where('type', '=', 'all')
                    ->first()
            )
        ) {
            return $address->address ?? null;
        }

        return null;
    }

    /**
     * Get primary bank account.
     *
     * @return BankAccount|null
     */
    public function getprimaryBankAccountAttribute(): ?BankAccount
    {
        if (
            ! empty(
                $account = $this->bankAccounts
                    ->where('primary', '=', true)
                    ->first()
            )
        ) {
            return $account;
        }

        return null;
    }
}
