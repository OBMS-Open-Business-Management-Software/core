<?php

namespace App\Models;

use App\Emails\AccountEmailVerification;
use App\Models\Accounting\Invoice\Invoice;
use App\Models\Accounting\Invoice\InvoiceReminder;
use App\Models\Accounting\Prepaid\PrepaidHistory;
use App\Models\Address\Address;
use App\Models\Content\Page;
use App\Models\Content\PageAcceptance;
use App\Models\Profile\BankAccount;
use App\Models\Profile\Profile;
use App\Models\Profile\ProfileEmail;
use App\Models\Profile\ProfilePhone;
use App\Models\Support\SupportTicket;
use Carbon\Carbon;
use Exception;
use Error;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Fortify\TwoFactorAuthenticationProvider;
use Laravel\Passport\HasApiTokens;

/**
 * Class User
 *
 * This class is the model for basic user metadata.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property Carbon $email_verified_at
 * @property string $password
 * @property string $two_factor_secret
 * @property string $two_factor_recovery_codes
 * @property bool $must_change_password
 * @property string $remember_token
 * @property string $role
 * @property bool $locked
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 *
 * @property Collection<Tenant> $tenants
 * @property Collection<PrepaidHistory> $prepaidHistory
 * @property Collection<Invoice> $invoices
 * @property Collection<PageAcceptance> $acceptance
 * @property Profile|null $profile
 * @property ProfileEmail|null $contactEmailAddress
 * @property ProfileEmail|null $billingEmailAddress
 * @property ProfilePhone|null $contactPhoneNumber
 * @property ProfilePhone|null $billingPhoneNumber
 * @property Address|null $contactPostalAddress
 * @property Address|null $billingPostalAddress
 * @property BankAccount|null $primaryBankAccount
 * @property bool $validProfile
 * @property bool $validBankAccount
 * @property bool $validBankAccountSEPA
 * @property string $number
 * @property string $realName
 * @property double $prepaidAccountBalance
 * @property int $creditScore
 * @property Collection<Page> $acceptable
 * @property bool $accepted
 * @property bool $reverseCharge
 */
class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;
    use SoftDeletes;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'email_verified_at',
        'must_change_password',
        'role',
        'locked',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'must_change_password' => 'boolean',
    ];

    /**
     * Determine if the user has verified their email address.
     *
     * @return bool
     */
    public function hasVerifiedEmail(): bool
    {
        return isset($this->email_verified_at);
    }

    /**
     * Mark the given user's email as verified.
     *
     * @return bool
     */
    public function markEmailAsVerified(): bool
    {
        return $this->update([
            'email_verified_at' => Carbon::now(),
        ]);
    }

    /**
     * Send the email verification notification.
     *
     * @return void
     */
    public function sendEmailVerificationNotification(): void
    {
        try {
            $this->notify(new AccountEmailVerification());
        } catch (Exception|Error $exception) {
        }
    }

    /**
     * Get the email address that should be used for verification.
     *
     * @return string
     */
    public function getEmailForVerification(): string
    {
        return $this->email;
    }

    /**
     * Relation to tenants.
     *
     * @return HasMany
     */
    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class, 'user_id', 'id');
    }

    /**
     * Relation to tickets.
     *
     * @return HasMany
     */
    public function tickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class, 'user_id', 'id');
    }

    /**
     * Relation to prepaid history.
     *
     * @return HasMany
     */
    public function prepaidHistory(): HasMany
    {
        return $this->hasMany(PrepaidHistory::class, 'user_id', 'id');
    }

    /**
     * Relation to invoices.
     *
     * @return HasMany
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'user_id', 'id');
    }

    /**
     * Relation to page acceptances.
     *
     * @return HasMany
     */
    public function acceptance(): HasMany
    {
        return $this->hasMany(PageAcceptance::class, 'user_id', 'id');
    }

    /**
     * Relation to profile.
     *
     * @return HasOne
     */
    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class, 'user_id', 'id')
            ->orderByDesc('id');
    }

    /**
     * Get contact email address.
     *
     * @return ProfileEmail|null
     */
    public function getcontactEmailAddressAttribute(): ?ProfileEmail
    {
        if (! empty($profile = $this->profile)) {
            return $profile->contactEmailAddress;
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
        if (! empty($profile = $this->profile)) {
            return $profile->billingEmailAddress;
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
        if (! empty($profile = $this->profile)) {
            return $profile->contactPhoneNumber;
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
        if (! empty($profile = $this->profile)) {
            return $profile->billingPhoneNumber;
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
        if (! empty($profile = $this->profile)) {
            return $profile->contactPostalAddress;
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
        if (! empty($profile = $this->profile)) {
            return $profile->billingPostalAddress;
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
        if (! empty($profile = $this->profile)) {
            return $profile->primaryBankAccount;
        }

        return null;
    }

    /**
     * Check if user has a valid profile with all the required data.
     *
     * @return bool
     */
    public function getvalidProfileAttribute(): bool
    {
        return isset(
            $this->contactEmailAddress,
            $this->billingEmailAddress,
            $this->contactPhoneNumber,
            $this->billingPhoneNumber,
            $this->contactPostalAddress,
            $this->billingPostalAddress,
        );
    }

    /**
     * Check if user has a valid bank account with all the required data.
     *
     * @return bool
     */
    public function getvalidBankAccountAttribute(): bool
    {
        return isset($this->primaryBankAccount);
    }

    /**
     * Check if user has a valid bank account with all the required data and a SEPA mandate.
     *
     * @return bool
     */
    public function getvalidBankAccountSEPAAttribute(): bool
    {
        return isset($this->primaryBankAccount, $this->primaryBankAccount->sepa_mandate_signed_at);
    }

    /**
     * Confirm 2FA code.
     *
     * @param $code
     *
     * @return bool
     */
    public function confirmTwoFactorAuth($code)
    {
        $codeIsValid = app(TwoFactorAuthenticationProvider::class)
            ->verify(decrypt($this->two_factor_secret), $code);

        if ($codeIsValid) {
            $this->two_factor_confirmed = true;
            $this->save();

            return true;
        }

        return false;
    }

    /**
     * Get formatted customer or employee number.
     *
     * @return string
     */
    public function getnumberAttribute(): string
    {
        switch ($this->role) {
            case 'admin':
                return 'A' . $this->id;
            case 'employee':
                return 'E' . $this->id;
            case 'supplier':
                return 'S' . $this->id;
            case 'customer':
                return 'KD' . $this->id;
            case 'api':
                return 'I' . $this->id;
        }

        return (string) $this->id;
    }

    /**
     * Get formatted customer or employee name.
     *
     * @return string
     */
    public function getrealNameAttribute(): string
    {
        if (! empty($profile = $this->profile)) {
            if (! empty($profile->company)) {
                return $profile->company;
            }

            return $profile->firstname . ' ' . $profile->lastname;
        }

        return $this->name;
    }

    /**
     * Get the prepaid account balance.
     *
     * @return float
     */
    public function getPrepaidAccountBalanceAttribute(): float
    {
        $balance = 0;

        $this->prepaidHistory()
            ->orderBy('created_at')
            ->each(function (PrepaidHistory $history) use (&$balance) {
                $balance += $history->amount;
            });

        if ($this->role == 'supplier') {
            $this->invoices()
                ->whereIn('status', [
                    'unpaid',
                    'paid',
                ])
                ->each(function (Invoice $invoice) use (&$balance) {
                    $balance -= $invoice->grossSumDiscounted;
                });
        }

        return $balance;
    }

    /**
     * Calculate an internal credit score showing customer reliability
     * depending on the relation between invoices and reminders.
     *
     * @return int
     */
    public function getCreditScoreAttribute(): int
    {
        $score = 0;

        Invoice::where('user_id', '=', $this->id)
            ->whereIn('status', [
                'paid',
                'unpaid',
                'refunded',
                'revoked',
            ])
            ->whereDoesntHave('type', function (Builder $builder) {
                return $builder->where('type', '=', 'prepaid');
            })
            ->whereNotNull('archived_at')
            ->each(function (Invoice $invoice) use (&$score) {
                if (
                    $invoice->archived_at
                        ->addDays($invoice->type->period)
                        ->endOfDay()
                        ->lt(Carbon::now())
                ) {
                    $score += 10;

                    $invoice->reminders->each(function (InvoiceReminder $reminder) use (&$score) {
                        $score -= 10;
                    });
                }
            });

        return $score;
    }

    /**
     * Check if the user has to accept page content in the newest version available.
     *
     * @return Collection
     */
    public function getAcceptableAttribute(): Collection
    {
        return Page::acceptable()
            ->get()
            ->filter(function (Page $page) {
                if (empty($latest = $page->latest)) {
                    return false;
                }

                return ! $this->acceptance()
                    ->where('page_version_id', '=', $latest->id)
                    ->whereHas('user', function (Builder $builder) {
                        return $builder->where('user_id', '=', $this->id);
                    })
                    ->exists();
            });
    }

    /**
     * Check if the contents of all required pages have been accepted.
     *
     * @return bool
     */
    public function getAcceptedAttribute(): bool
    {
        return $this->acceptable->isEmpty();
    }

    /**
     * Check if the user is applicable to be using reverse-charge.
     *
     * @return bool
     */
    public function getReverseChargeAttribute(): bool
    {
        return ! empty($userProfile = $this->profile) &&
            ! empty($userProfile->company) &&
            ! empty($userProfile->vat_id) &&
            ! empty($billingAddress = $this->billingPostalAddress) &&
            ! empty($userCountry = $billingAddress->country) &&
            $userCountry->eu &&
            $userCountry->reverse_charge;
    }
}
