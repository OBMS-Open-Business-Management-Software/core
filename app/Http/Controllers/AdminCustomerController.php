<?php

namespace App\Http\Controllers;

use App\Models\Accounting\Contract\ContractType;
use App\Models\Accounting\Invoice\InvoiceType;
use App\Models\Accounting\PositionDiscount;
use App\Models\Accounting\Prepaid\PrepaidHistory;
use App\Models\Address\Address;
use App\Models\Address\Country;
use App\Models\Profile\BankAccount;
use App\Models\Profile\Profile;
use App\Models\Profile\ProfileAddress;
use App\Models\Profile\ProfileEmail;
use App\Models\Profile\ProfilePhone;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AdminCustomerController extends Controller
{
    /**
     * Show list of customers.
     *
     * @return Renderable
     */
    public function customer_index(): Renderable
    {
        return view('admin.customer.home');
    }

    /**
     * Get list of profile email addresses.
     *
     * @param Request $request
     */
    public function customer_list(Request $request): void
    {
        session_write_close();

        $query = User::where('role', '=', 'customer');

        $totalCount = (clone $query)->count();

        if (! empty($request->search['value'])) {
            $query = $query->where(function (Builder $query) use ($request) {
                return $query->where('id', 'LIKE', '%' . $request->search['value'] . '%')
                    ->orWhere('email', 'LIKE', '%' . $request->search['value'] . '%')
                    ->orWhere('name', 'LIKE', '%' . $request->search['value'] . '%')
                    ->orWhereHas('profile', function (Builder $builder) use ($request) {
                        return $builder->where('firstname', 'LIKE', '%' . $request->search['value'] . '%')
                            ->orWhere('lastname', 'LIKE', '%' . $request->search['value'] . '%')
                            ->orWhere('company', 'LIKE', '%' . $request->search['value'] . '%');
                    });
            });
        }

        if (! empty($request->order)) {
            foreach ($request->order as $order) {
                switch ($request->columns[$order['column']]) {
                    case 'name':
                        $orderBy = 'name';
                        break;
                    case 'status':
                        $orderBy = 'locked';
                        break;
                    case 'id':
                    default:
                        $orderBy = 'id';
                        break;
                }

                $query = $query->orderBy($orderBy, $order['dir']);
            }
        }

        $filteredCount = (clone $query)->count();

        $query = $query->offset($request->start)
            ->limit($request->length);

        header('Content-type: application/json');
        echo json_encode([
            'draw' => (int) $request->draw,
            'recordsTotal' => $totalCount,
            'recordsFiltered' => $filteredCount,
            'data' => $query
                ->get()
                ->transform(function (User $user) use ($totalCount) {
                    return (object) [
                        'id' => $user->number,
                        'name' => $user->realName,
                        'type' => $user->validProfile ? '<span class="badge badge-success">' . __('interface.data.full') . '</span>' : '<span class="badge badge-warning">' . __('interface.misc.prepaid') . '</span>',
                        'status' => $user->locked ? '<span class="badge badge-warning">' . __('interface.status.locked') . '</span>' : '<span class="badge badge-success">' . __('interface.status.unlocked') . '</span>',
                        'view' => '<a href="' . route('admin.customers.profile', $user->id) . '" class="btn btn-primary btn-sm"><i class="bi bi-eye"></i></a>',
                    ];
                })
        ]);
    }

    /**
     * Show detailed overview of a customer.
     *
     * @param int $user_id
     *
     * @return Renderable|RedirectResponse
     */
    public function customer_profile_index(int $user_id)
    {
        if (
            ! empty(
                $user = User::where('id', '=', $user_id)
                    ->where('role', '=', 'customer')
                    ->first()
            )
        ) {
            return view('admin.customer.details', [
                'user' => $user,
                'countries' => Country::all(),
                'contractTypes' => ContractType::all(),
                'invoiceTypes' => InvoiceType::all(),
                'discounts' => PositionDiscount::all(),
            ]);
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }

    /**
     * Remove two factor authentication from customer profile.
     *
     * @param int $user_id
     *
     * @return RedirectResponse
     */
    public function customer_profile_2fa_disable(int $user_id): RedirectResponse
    {
        if (
            ! empty(
                $user = User::where('id', '=', $user_id)
                    ->where('role', '=', 'customer')
                    ->first()
            )
        ) {
            $user->forceUpdate([
                'two_factor_secret' => null,
                'two_factor_recovery_codes' => null,
                'two_factor_confirmed' => false,
            ]);

            return redirect()->back()->with('success', __('interface.messages.2fa_removed'));
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }

    /**
     * Lock customer account.
     *
     * @param int $user_id
     *
     * @return RedirectResponse
     */
    public function customer_lock(int $user_id): RedirectResponse
    {
        if (
            ! empty(
                $user = User::where('id', '=', $user_id)
                    ->where('role', '=', 'customer')
                    ->first()
            )
        ) {
            $status = ! $user->locked;

            $user->update([
                'locked' => $status,
            ]);

            if ($status) {
                return redirect()->back()->with('success', __('interface.messages.customer_locked'));
            } else {
                return redirect()->back()->with('success', __('interface.messages.customer_unlocked'));
            }
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }

    /**
     * Create a new customer account.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     *
     * @throws ValidationException
     */
    public function customer_create(Request $request): RedirectResponse
    {
        Validator::make($request->toArray(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'suppress_welcome_email' => ['string', 'nullable'],
        ])->validate();

        if (
            ! empty(
                $user = User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                ])
            )
        ) {
            if (! (bool) $request->suppress_welcome_email) {
                $user->sendEmailVerificationNotification();
            }

            return redirect()->route('admin.customers.profile', $user->id)->with('success', __('interface.messages.customer_created'));
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
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
    public function customer_profile_update(Request $request): RedirectResponse
    {
        if (
            ! empty(
                $user = User::where('id', '=', $request->user_id)
                    ->where('role', '=', 'customer')
                    ->first()
            )
        ) {
            if ($user->email !== $request->email) {
                Validator::make($request->toArray(), [
                    'name' => ['required', 'string', 'max:255'],
                    'email' => ['required', 'email', 'confirmed'],
                ])->validate();

                $user->update([
                    'name' => $request->name,
                    'email' => $request->email,
                    'email_verified_at' => null,
                ]);
            } else {
                Validator::make($request->toArray(), [
                    'name' => ['required', 'string', 'max:255'],
                    'email' => ['required', 'email'],
                ])->validate();

                $user->update([
                    'name' => $request->name,
                ]);
            }

            return redirect()->back()->with('success', __('interface.messages.profile_updated'));
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }

    /**
     * Change the profile details.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     *
     * @throws ValidationException
     */
    public function customer_profile_details_update(Request $request): RedirectResponse
    {
        Validator::make($request->toArray(), [
            'firstname' => ['required', 'string', 'max:255'],
            'lastname' => ['required', 'string', 'max:255'],
            'company' => ['string', 'max:255', 'nullable'],
            'tax_id' => ['string', 'max:255', 'nullable'],
            'vat_id' => ['string', 'max:255', 'nullable'],
        ])->validate();

        if (
            ! empty(
                $user = User::where('id', '=', $request->user_id)
                    ->where('role', '=', 'customer')
                    ->first()
            )
        ) {
            if (! empty($profile = $user->profile)) {
                $profile->update([
                    'firstname' => $request->firstname,
                    'lastname' => $request->lastname,
                    'company' => $request->company ?? null,
                    'tax_id' => $request->tax_id ?? null,
                    'vat_id' => $request->vat_id ?? null,
                ]);

                return redirect()->back()->with('success', __('interface.messages.profile_details_completed'));
            }
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
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
    public function customer_profile_password(Request $request): RedirectResponse
    {
        Validator::make($request->toArray(), [
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ])->validate();

        if (
            ! empty(
                $user = User::where('id', '=', $request->user_id)
                    ->where('role', '=', 'customer')
                    ->first()
            )
        ) {
            $user->update([
                'password' => Hash::make($request->password),
            ]);

            return redirect()->back()->with('success', __('interface.messages.password_changed'));
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }

    /**
     * Complete the profile details.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     *
     * @throws ValidationException
     */
    public function customer_profile_complete(Request $request): RedirectResponse
    {
        Validator::make($request->toArray(), [
            'firstname' => ['required', 'string', 'max:255'],
            'lastname' => ['required', 'string', 'max:255'],
            'company' => ['string', 'max:255', 'nullable'],
            'tax_id' => ['string', 'max:255', 'nullable'],
            'vat_id' => ['string', 'max:255', 'nullable'],
            'street' => ['required', 'string', 'max:255'],
            'housenumber' => ['required', 'string', 'max:255'],
            'addition' => ['string', 'max:255', 'nullable'],
            'postalcode' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'state' => ['required', 'string', 'max:255'],
            'country' => ['required', 'integer', 'min:1'],
            'email' => ['required', 'email'],
            'phone' => ['required', 'regex:/^(?:(?:\(?(?:00|\+)([1-4]\d\d|[1-9]\d?)\)?)?[\-\.\ \\\\\/]?)?((?:\(?\d{1,}\)?[\-\.\ \\\\\/]?){0,})(?:[\-\.\ \\\\\/]?(?:#|ext\.?|extension|x)[\-\.\ \\\\\/]?(\d+))?$/i'],
        ])->validate();

        if (
            ! empty(
                $user = User::where('id', '=', $request->user_id)
                    ->where('role', '=', 'customer')
                    ->first()
            )
        ) {
            $profile = Profile::create([
                'user_id' => $user->id,
                'firstname' => $request->firstname,
                'lastname' => $request->lastname,
                'company' => $request->company ?? null,
                'tax_id' => $request->tax_id ?? null,
                'vat_id' => $request->vat_id ?? null,
                'verified' => false,
                'primary' => true,
            ]);

            $address = Address::create([
                'country_id' => $request->country,
                'street' => $request->street,
                'housenumber' => $request->housenumber,
                'addition' => $request->addition ?? null,
                'postalcode' => $request->postalcode,
                'city' => $request->city,
                'state' => $request->state,
            ]);

            ProfileAddress::create([
                'profile_id' => $profile->id,
                'address_id' => $address->id,
                'type' => 'all',
            ]);

            ProfilePhone::create([
                'profile_id' => $profile->id,
                'phone' => $request->phone,
                'type' => 'all',
            ]);

            if (
                ! empty(
                    $email = ProfileEmail::create([
                        'profile_id' => $profile->id,
                        'email' => $request->email,
                        'type' => 'all',
                    ])
                )
            ) {
                $email->sendEmailVerificationNotification();
            }

            return redirect()->back()->with('success', __('interface.messages.profile_completed'));
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }

    /**
     * Get list of profile email addresses.
     *
     * @param Request $request
     */
    public function customer_profile_email_list(Request $request): void
    {
        session_write_close();

        if (
            ! empty(
                $user = User::where('id', '=', $request->user_id)
                    ->where('role', '=', 'customer')
                    ->first()
            )
        ) {
            $query = ProfileEmail::whereHas('profile', function (Builder $builder) use ($user) {
                return $builder->where('user_id', '=', $user->id);
            });

            $totalCount = (clone $query)->count();

            if (! empty($request->search['value'])) {
                $query = $query->where(function (Builder $query) use ($request) {
                    return $query->where('id', 'LIKE', '%' . $request->search['value'] . '%')
                        ->orWhere('email', 'LIKE', '%' . $request->search['value'] . '%')
                        ->orWhere('type', 'LIKE', '%' . $request->search['value'] . '%');
                });
            }

            if (! empty($request->order)) {
                foreach ($request->order as $order) {
                    switch ($request->columns[$order['column']]) {
                        case 'email':
                            $orderBy = 'email';
                            break;
                        case 'type':
                            $orderBy = 'type';
                            break;
                        case 'status':
                            $orderBy = 'email_verified_at';
                            break;
                        case 'id':
                        default:
                            $orderBy = 'id';
                            break;
                    }

                    $query = $query->orderBy($orderBy, $order['dir']);
                }
            }

            $filteredCount = (clone $query)->count();

            $query = $query->offset($request->start)
                ->limit($request->length);

            header('Content-type: application/json');
            echo json_encode([
                'draw' => (int) $request->draw,
                'recordsTotal' => $totalCount,
                'recordsFiltered' => $filteredCount,
                'data' => $query
                    ->get()
                    ->transform(function (ProfileEmail $email) use ($user, $totalCount) {
                        switch ($email->email_verified_at) {
                            case ! null:
                                $status = '<span class="badge badge-success">' . __('interface.status.verified') . '</span>';
                                break;
                            default:
                                $status = '<span class="badge badge-danger">' . __('interface.status.unverified') . '</span>';
                                break;
                        }

                        $edit = '
<a class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editEmail' . $email->id . '"><i class="bi bi-pencil-square"></i></a>
<div class="modal fade" id="editEmail' . $email->id . '" tabindex="-1" aria-labelledby="editEmail' . $email->id . 'Label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title" id="editEmail' . $email->id . 'Label">' . __('interface.actions.edit') . ' (' . $email->email . ')</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="' . route('admin.customers.profile.email.update', $user->id) . '" method="post">
                <div class="modal-body">
                    <input type="hidden" name="_token" value="' . csrf_token() . '" />
                    <input type="hidden" name="email_id" value="' . $email->id . '" />
                    <input type="hidden" name="user_id" value="' . $user->id . '" />
                    <div class="form-group row">
                        <label for="all" class="col-md-4 col-form-label text-md-right">' . __('interface.misc.all') . '</label>

                        <div class="col-md-8">
                            <input id="all" type="radio" class="form-control" name="email_type_' . $email->id . '" value="all" ' . (($email->type ?? null) == 'all' ? 'checked' : '') . '>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="billing" class="col-md-4 col-form-label text-md-right">' . __('interface.data.billing') . '</label>

                        <div class="col-md-8">
                            <input id="billing" type="radio" class="form-control" name="email_type_' . $email->id . '" value="billing" ' . (($email->type ?? null) == 'billing' ? 'checked' : '') . ' ' . ($totalCount <= 1 ? 'disabled' : '') . '>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="contact" class="col-md-4 col-form-label text-md-right">' . __('interface.data.contact') . '</label>

                        <div class="col-md-8">
                            <input id="contact" type="radio" class="form-control" name="email_type_' . $email->id . '" value="contact" ' . (($email->type ?? null) == 'contact' ? 'checked' : '') . ' ' . ($totalCount <= 1 ? 'disabled' : '') . '>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="none" class="col-md-4 col-form-label text-md-right">' . __('interface.misc.none') . '</label>

                        <div class="col-md-8">
                            <input id="none" type="radio" class="form-control" name="email_type_' . $email->id . '" value="none" ' . (($email->type ?? '') == '' ? 'checked' : '') . ' ' . ($totalCount <= 1 ? 'disabled' : '') . '>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-warning"><i class="bi bi-pencil-square"></i> ' . __('interface.actions.edit') . '</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">' . __('interface.actions.close') . '</button>
                </div>
            </form>
        </div>
    </div>
</div>
';

                        return (object) [
                            'email' => $email->email,
                            'type' => ! empty($email->type) ? __(ucfirst($email->type)) : __('interface.misc.none'),
                            'status' => $status,
                            'resend' => empty($email->email_verified_at) ? '<a href="' . route('admin.customers.profile.email.resend', ['user_id' => $user->id, 'id' => $email->id]) . '" class="btn btn-primary btn-sm"><i class="bi bi-envelope"></i></a>' : '<button type="button" class="btn btn-primary btn-sm" disabled><i class="bi bi-envelope"></i></button>',
                            'edit' => $edit,
                            'delete' => $totalCount > 1 && empty($email->type) ? '<a href="' . route('admin.customers.profile.email.delete', ['user_id' => $user->id, 'id' => $email->id]) . '" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i></a>' : '<button type="button" class="btn btn-danger btn-sm" disabled><i class="bi bi-trash"></i></button>',
                        ];
                    })
            ]);
        }
    }

    /**
     * Create a profile email address.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     *
     * @throws ValidationException
     */
    public function customer_profile_email_create(Request $request): RedirectResponse
    {
        Validator::make($request->toArray(), [
            'email' => ['required', 'email'],
            'type' => ['required', 'string'],
        ])->validate();

        if (
            ! empty(
                $user = User::where('id', '=', $request->user_id)
                    ->where('role', '=', 'customer')
                    ->first()
            )
        ) {
            if (! empty($profile = $user->profile)) {
                if ($request->type == 'none') {
                    $request->type = null;
                } else {
                    $profile->emailAddresses()
                        ->where('type', '=', $request->type)
                        ->update([
                            'type' => null,
                        ]);
                }

                ProfileEmail::create([
                    'profile_id' => $profile->id,
                    'email' => $request->email,
                    'type' => $request->type,
                ]);

                return redirect()->back()->with('success', __('interface.messages.profile_email_added'));
            }
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }

    /**
     * Update a profile email address.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     *
     * @throws ValidationException
     */
    public function customer_profile_email_update(Request $request): RedirectResponse
    {
        Validator::make($request->toArray(), [
            'email_id' => ['required', 'integer'],
        ])->validate();

        if (
            ! empty(
                $user = User::where('id', '=', $request->user_id)
                    ->where('role', '=', 'customer')
                    ->first()
            )
        ) {
            if (
                ! empty($email = ProfileEmail::find($request->email_id)) &&
                $email->profile->user_id == $user->id
            ) {
                Validator::make($request->toArray(), [
                    'email_type_' . $email->id => ['required', 'string'],
                ])->validate();

                $allQuery = ProfileEmail::whereHas('profile', function (Builder $builder) use ($user) {
                    return $builder->where('user_id', $user->id);
                })
                    ->where('type', '=', 'all');

                if (
                    (
                        $request->{'email_type_' . $email->id} !== 'all' &&
                        (clone $allQuery)->count() == 1 &&
                        $email->type !== 'all'
                    ) ||
                    (
                        $request->{'email_type_' . $email->id} !== 'all' &&
                        (clone $allQuery)->count() > 1 &&
                        $email->type == 'all'
                    ) ||
                    $request->{'email_type_' . $email->id} == 'all'
                ) {
                    if ($request->{'email_type_' . $email->id} == 'all') {
                        $allQuery->update([
                            'type' => null,
                        ]);
                    } elseif ($request->{'email_type_' . $email->id} !== 'none') {
                        ProfileEmail::whereHas('profile', function (Builder $builder) use ($user) {
                            return $builder->where('user_id', $user->id);
                        })
                            ->where('type', '=', $request->{'email_type_' . $email->id})
                            ->where('id', '!=', $email->id)
                            ->update([
                                'type' => null,
                            ]);
                    }

                    if ($request->{'email_type_' . $email->id} == 'none') {
                        $request->{'email_type_' . $email->id} = null;
                    }

                    $email->update([
                        'type' => $request->{'email_type_' . $email->id},
                    ]);

                    return redirect()->back()->with('success', __('interface.messages.profile_email_updated'));
                }
            }
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }

    /**
     * Delete a profile email address.
     *
     * @param int $id
     *
     * @return RedirectResponse
     *
     * @throws ValidationException
     */
    public function customer_profile_email_delete(int $user_id, int $id): RedirectResponse
    {
        Validator::make([
            'email_id' => $id,
        ], [
            'email_id' => ['required', 'integer'],
        ])->validate();

        if (
            ! empty(
                $user = User::where('id', '=', $user_id)
                    ->where('role', '=', 'customer')
                    ->first()
            )
        ) {
            if (
                ! empty($email = ProfileEmail::find($id)) &&
                $email->profile->user_id == $user->id &&
                empty($email->type)
            ) {
                $email->delete();

                return redirect()->back()->with('success', __('interface.messages.profile_email_deleted'));
            }
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }

    /**
     * Resend the verification email for a profile email address.
     *
     * @param int $id
     *
     * @return RedirectResponse
     *
     * @throws ValidationException
     */
    public function customer_profile_email_resend(int $user_id, int $id): RedirectResponse
    {
        Validator::make([
            'email_id' => $id,
        ], [
            'email_id' => ['required', 'integer'],
        ])->validate();

        if (
            ! empty(
                $user = User::where('id', '=', $user_id)
                    ->where('role', '=', 'customer')
                    ->first()
            )
        ) {
            if (
                !empty($email = ProfileEmail::find($id)) &&
                $email->profile->user_id == $user->id
            ) {
                $email->sendEmailVerificationNotification();

                return redirect()->back()->with('success', __('interface.messages.email_verification_resent'));
            }
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }

    /**
     * Get list of profile phone numbers.
     *
     * @param Request $request
     */
    public function customer_profile_phone_list(Request $request): void
    {
        session_write_close();

        if (
            ! empty(
                $user = User::where('id', '=', $request->user_id)
                    ->where('role', '=', 'customer')
                    ->first()
            )
        ) {
            $query = ProfilePhone::whereHas('profile', function (Builder $builder) use ($user) {
                return $builder->where('user_id', '=', $user->id);
            });

            $totalCount = (clone $query)->count();

            if (! empty($request->search['value'])) {
                $query = $query->where(function (Builder $query) use ($request) {
                    return $query->where('id', 'LIKE', '%' . $request->search['value'] . '%')
                        ->orWhere('phone', 'LIKE', '%' . $request->search['value'] . '%')
                        ->orWhere('type', 'LIKE', '%' . $request->search['value'] . '%');
                });
            }

            if (! empty($request->order)) {
                foreach ($request->order as $order) {
                    switch ($request->columns[$order['column']]) {
                        case 'phone':
                            $orderBy = 'phone';
                            break;
                        case 'type':
                            $orderBy = 'type';
                            break;
                        case 'id':
                        default:
                            $orderBy = 'id';
                            break;
                    }

                    $query = $query->orderBy($orderBy, $order['dir']);
                }
            }

            $filteredCount = (clone $query)->count();

            $query = $query->offset($request->start)
                ->limit($request->length);

            header('Content-type: application/json');
            echo json_encode([
                'draw' => (int) $request->draw,
                'recordsTotal' => $totalCount,
                'recordsFiltered' => $filteredCount,
                'data' => $query
                    ->get()
                    ->transform(function (ProfilePhone $phone) use ($user, $totalCount) {
                        $edit = '
<a class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editPhone' . $phone->id . '"><i class="bi bi-pencil-square"></i></a>
<div class="modal fade" id="editPhone' . $phone->id . '" tabindex="-1" aria-labelledby="editPhone' . $phone->id . 'Label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title" id="editPhone' . $phone->id . 'Label">' . __('interface.actions.edit') . ' (' . $phone->phone . ')</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="' . route('admin.customers.profile.phone.update', $user->id) . '" method="post">
                <div class="modal-body">
                    <input type="hidden" name="_token" value="' . csrf_token() . '" />
                    <input type="hidden" name="phone_id" value="' . $phone->id . '" />
                    <input type="hidden" name="user_id" value="' . $user->id . '" />
                    <div class="form-group row">
                        <label for="all" class="col-md-4 col-form-label text-md-right">' . __('interface.misc.all') . '</label>

                        <div class="col-md-8">
                            <input id="all" type="radio" class="form-control" name="phone_type_' . $phone->id . '" value="all" ' . (($phone->type ?? null) == 'all' ? 'checked' : '') . '>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="billing" class="col-md-4 col-form-label text-md-right">' . __('interface.data.billing') . '</label>

                        <div class="col-md-8">
                            <input id="billing" type="radio" class="form-control" name="phone_type_' . $phone->id . '" value="billing" ' . (($phone->type ?? null) == 'billing' ? 'checked' : '') . ' ' . ($totalCount <= 1 ? 'disabled' : '') . '>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="contact" class="col-md-4 col-form-label text-md-right">' . __('interface.data.contact') . '</label>

                        <div class="col-md-8">
                            <input id="contact" type="radio" class="form-control" name="phone_type_' . $phone->id . '" value="contact" ' . (($phone->type ?? null) == 'contact' ? 'checked' : '') . ' ' . ($totalCount <= 1 ? 'disabled' : '') . '>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="none" class="col-md-4 col-form-label text-md-right">' . __('interface.misc.none') . '</label>

                        <div class="col-md-8">
                            <input id="none" type="radio" class="form-control" name="phone_type_' . $phone->id . '" value="none" ' . (($phone->type ?? '') == '' ? 'checked' : '') . ' ' . ($totalCount <= 1 ? 'disabled' : '') . '>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-warning"><i class="bi bi-pencil-square"></i> ' . __('interface.actions.edit') . '</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">' . __('interface.actions.close') . '</button>
                </div>
            </form>
        </div>
    </div>
</div>
';

                        return (object) [
                            'phone' => $phone->phone,
                            'type' => ! empty($phone->type) ? __(ucfirst($phone->type)) : __('interface.misc.none'),
                            'edit' => $edit,
                            'delete' => $totalCount > 1 && empty($phone->type) ? '<a href="' . route('admin.customers.profile.phone.delete', ['user_id' => $user->id, 'id' => $phone->id]) . '" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i></a>' : '<button type="button" class="btn btn-danger btn-sm" disabled><i class="bi bi-trash"></i></button>',
                        ];
                    })
            ]);
        }
    }

    /**
     * Create a profile phone number.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     *
     * @throws ValidationException
     */
    public function customer_profile_phone_create(Request $request): RedirectResponse
    {
        Validator::make($request->toArray(), [
            'phone' => ['required', 'regex:/^(?:(?:\(?(?:00|\+)([1-4]\d\d|[1-9]\d?)\)?)?[\-\.\ \\\\\/]?)?((?:\(?\d{1,}\)?[\-\.\ \\\\\/]?){0,})(?:[\-\.\ \\\\\/]?(?:#|ext\.?|extension|x)[\-\.\ \\\\\/]?(\d+))?$/i'],
            'type' => ['required', 'string'],
        ])->validate();

        if (
            ! empty(
                $user = User::where('id', '=', $request->user_id)
                    ->where('role', '=', 'customer')
                    ->first()
            )
        ) {
            if (! empty($profile = $user->profile)) {
                if ($request->type == 'none') {
                    $request->type = null;
                } else {
                    $profile->phoneNumbers()
                        ->where('type', '=', $request->type)
                        ->update([
                            'type' => null,
                        ]);
                }

                ProfilePhone::create([
                    'profile_id' => $profile->id,
                    'phone' => $request->phone,
                    'type' => $request->type,
                ]);

                return redirect()->back()->with('success', __('interface.messages.phone_number_added'));
            }
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }

    /**
     * Update a profile phone number.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     *
     * @throws ValidationException
     */
    public function customer_profile_phone_update(Request $request): RedirectResponse
    {
        Validator::make($request->toArray(), [
            'phone_id' => ['required', 'integer'],
        ])->validate();

        if (
            ! empty(
                $user = User::where('id', '=', $request->user_id)
                    ->where('role', '=', 'customer')
                    ->first()
            )
        ) {
            if (
                ! empty($phone = ProfilePhone::find($request->phone_id)) &&
                $phone->profile->user_id == $user->id
            ) {
                Validator::make($request->toArray(), [
                    'phone_type_' . $phone->id => ['required', 'string'],
                ])->validate();

                $allQuery = ProfilePhone::whereHas('profile', function (Builder $builder) use ($user) {
                    return $builder->where('user_id', $user->id);
                })
                    ->where('type', '=', 'all');

                if (
                    (
                        $request->{'phone_type_' . $phone->id} !== 'all' &&
                        (clone $allQuery)->count() == 1 &&
                        $phone->type !== 'all'
                    ) ||
                    (
                        $request->{'phone_type_' . $phone->id} !== 'all' &&
                        (clone $allQuery)->count() > 1 &&
                        $phone->type == 'all'
                    ) ||
                    $request->{'phone_type_' . $phone->id} == 'all'
                ) {
                    if ($request->{'phone_type_' . $phone->id} == 'all') {
                        $allQuery->update([
                            'type' => null,
                        ]);
                    } elseif ($request->{'phone_type_' . $phone->id} !== 'none') {
                        ProfilePhone::whereHas('profile', function (Builder $builder) use ($user) {
                            return $builder->where('user_id', $user->id);
                        })
                            ->where('type', '=', $request->{'phone_type_' . $phone->id})
                            ->where('id', '!=', $phone->id)
                            ->update([
                                'type' => null,
                            ]);
                    }

                    if ($request->{'phone_type_' . $phone->id} == 'none') {
                        $request->{'phone_type_' . $phone->id} = null;
                    }

                    $phone->update([
                        'type' => $request->{'phone_type_' . $phone->id},
                    ]);

                    return redirect()->back()->with('success', __('interface.messages.phone_number_updated'));
                }
            }
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }

    /**
     * Delete a profile phone number.
     *
     * @param int $id
     *
     * @return RedirectResponse
     *
     * @throws ValidationException
     */
    public function customer_profile_phone_delete(int $user_id, int $id): RedirectResponse
    {
        Validator::make([
            'phone_id' => $id,
        ], [
            'phone_id' => ['required', 'integer'],
        ])->validate();

        if (
            ! empty(
                $user = User::where('id', '=', $user_id)
                    ->where('role', '=', 'customer')
                    ->first()
            )
        ) {
            if (
                ! empty($phone = ProfilePhone::find($id)) &&
                $phone->profile->user_id == $user->id &&
                empty($phone->type)
            ) {
                $phone->delete();

                return redirect()->back()->with('success', __('interface.messages.phone_number_deleted'));
            }
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }

    /**
     * Get list of profile phone numbers.
     *
     * @param Request $request
     */
    public function customer_profile_address_list(Request $request): void
    {
        session_write_close();

        if (
            ! empty(
                $user = User::where('id', '=', $request->user_id)
                    ->where('role', '=', 'customer')
                    ->first()
            )
        ) {
            $query = ProfileAddress::whereHas('profile', function (Builder $builder) use ($user) {
                return $builder->where('user_id', '=', $user->id);
            });

            $totalCount = (clone $query)->count();

            if (! empty($request->search['value'])) {
                $query = $query->where(function (Builder $query) use ($request) {
                    return $query->where('id', 'LIKE', '%' . $request->search['value'] . '%')
                        ->orWhere('type', 'LIKE', '%' . $request->search['value'] . '%')
                        ->orWhereHas('address', function (Builder $builder) use ($request) {
                            return $builder->where('street', 'LIKE', '%' . $request->search['value'] . '%')
                                ->orWhere('housenumber', 'LIKE', '%' . $request->search['value'] . '%')
                                ->orWhere('addition', 'LIKE', '%' . $request->search['value'] . '%')
                                ->orWhere('postalcode', 'LIKE', '%' . $request->search['value'] . '%')
                                ->orWhere('city', 'LIKE', '%' . $request->search['value'] . '%')
                                ->orWhere('state', 'LIKE', '%' . $request->search['value'] . '%')
                                ->orWhereHas('country', function (Builder $builder) use ($request) {
                                    return $builder->where('name', 'LIKE', '%' . $request->search['value'] . '%');
                                });
                        });
                });
            }

            if (! empty($request->order)) {
                foreach ($request->order as $order) {
                    switch ($request->columns[$order['column']]) {
                        case 'address':
                            $orderBy = 'address_id';
                            break;
                        case 'type':
                            $orderBy = 'type';
                            break;
                        case 'id':
                        default:
                            $orderBy = 'id';
                            break;
                    }

                    $query = $query->orderBy($orderBy, $order['dir']);
                }
            }

            $filteredCount = (clone $query)->count();

            $query = $query->offset($request->start)
                ->limit($request->length);

            header('Content-type: application/json');
            echo json_encode([
                'draw' => (int) $request->draw,
                'recordsTotal' => $totalCount,
                'recordsFiltered' => $filteredCount,
                'data' => $query
                    ->get()
                    ->transform(function (ProfileAddress $address) use ($user, $totalCount) {
                        $addition = ! empty($address->address->addition) ? $address->address->addition . '<br>' : '';
                        $addressString = $address->address->street . ' ' . $address->address->housenumber . '<br>' . $addition . $address->address->postalcode . ' ' . $address->address->city . '<br>' . $addition . $address->address->state . ' ' . ($address->address->country->name ?? __('interface.status.unknown')) . '<br>';

                        $edit = '
<a class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editAddress' . $address->id . '"><i class="bi bi-pencil-square"></i></a>
<div class="modal fade" id="editAddress' . $address->id . '" tabindex="-1" aria-labelledby="editAddress' . $address->id . 'Label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title" id="editAddress' . $address->id . 'Label">' . __('interface.actions.edit') . ' (#' . $address->id . ')</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="' . route('admin.customers.profile.address.update', $user->id) . '" method="post">
                <div class="modal-body">
                    <input type="hidden" name="_token" value="' . csrf_token() . '" />
                    <input type="hidden" name="address_link_id" value="' . $address->id . '" />
                    <input type="hidden" name="user_id" value="' . $user->id . '" />
                    <div class="form-group row">
                        <label for="all" class="col-md-4 col-form-label text-md-right">' . __('interface.misc.all') . '</label>

                        <div class="col-md-8">
                            <input id="all" type="radio" class="form-control" name="address_type_' . $address->id . '" value="all" ' . (($address->type ?? null) == 'all' ? 'checked' : '') . '>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="billing" class="col-md-4 col-form-label text-md-right">' . __('interface.data.billing') . '</label>

                        <div class="col-md-8">
                            <input id="billing" type="radio" class="form-control" name="address_type_' . $address->id . '" value="billing" ' . (($address->type ?? null) == 'billing' ? 'checked' : '') . ' ' . ($totalCount <= 1 ? 'disabled' : '') . '>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="contact" class="col-md-4 col-form-label text-md-right">' . __('interface.data.contact') . '</label>

                        <div class="col-md-8">
                            <input id="contact" type="radio" class="form-control" name="address_type_' . $address->id . '" value="contact" ' . (($address->type ?? null) == 'contact' ? 'checked' : '') . ' ' . ($totalCount <= 1 ? 'disabled' : '') . '>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="none" class="col-md-4 col-form-label text-md-right">' . __('interface.misc.none') . '</label>

                        <div class="col-md-8">
                            <input id="none" type="radio" class="form-control" name="address_type_' . $address->id . '" value="none" ' . (($address->type ?? '') == '' ? 'checked' : '') . ' ' . ($totalCount <= 1 ? 'disabled' : '') . '>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-warning"><i class="bi bi-pencil-square"></i> ' . __('interface.actions.edit') . '</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">' . __('interface.actions.close') . '</button>
                </div>
            </form>
        </div>
    </div>
</div>
';

                        return (object) [
                            'address' => $addressString,
                            'type' => ! empty($address->type) ? __(ucfirst($address->type)) : __('interface.misc.none'),
                            'edit' => $edit,
                            'delete' => $totalCount > 1 && empty($address->type) ? '<a href="' . route('admin.customers.profile.address.delete', ['user_id' => $user->id, 'id' => $address->id]) . '" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i></a>' : '<button type="button" class="btn btn-danger btn-sm" disabled><i class="bi bi-trash"></i></button>',
                        ];
                    })
            ]);
        }
    }

    /**
     * Create a profile postal address.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     *
     * @throws ValidationException
     */
    public function customer_profile_address_create(Request $request): RedirectResponse
    {
        Validator::make($request->toArray(), [
            'street' => ['required', 'string', 'max:255'],
            'housenumber' => ['required', 'string', 'max:255'],
            'addition' => ['string', 'max:255', 'nullable'],
            'postalcode' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'state' => ['required', 'string', 'max:255'],
            'country' => ['required', 'integer', 'min:1'],
            'type' => ['required', 'string'],
        ])->validate();

        if (
            ! empty(
                $user = User::where('id', '=', $request->user_id)
                    ->where('role', '=', 'customer')
                    ->first()
            )
        ) {
            if (! empty($profile = $user->profile)) {
                if ($request->type == 'none') {
                    $request->type = null;
                } else {
                    $profile->addressLinks()
                        ->where('type', '=', $request->type)
                        ->update([
                            'type' => null,
                        ]);
                }

                $address = Address::create([
                    'country_id' => $request->country,
                    'street' => $request->street,
                    'housenumber' => $request->housenumber,
                    'addition' => $request->addition ?? '',
                    'postalcode' => $request->postalcode,
                    'city' => $request->city,
                    'state' => $request->state,
                ]);

                ProfileAddress::create([
                    'profile_id' => $profile->id,
                    'address_id' => $address->id,
                    'type' => $request->type,
                ]);

                return redirect()->back()->with('success', __('interface.messages.postal_address_added'));
            }
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }

    /**
     * Update a profile postal address.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     *
     * @throws ValidationException
     */
    public function customer_profile_address_update(Request $request): RedirectResponse
    {
        Validator::make($request->toArray(), [
            'address_link_id' => ['required', 'integer'],
        ])->validate();

        if (
            ! empty(
                $user = User::where('id', '=', $request->user_id)
                    ->where('role', '=', 'customer')
                    ->first()
            )
        ) {
            if (
                ! empty($address = ProfileAddress::find($request->address_link_id)) &&
                $address->profile->user_id == $user->id
            ) {
                Validator::make($request->toArray(), [
                    'address_type_' . $address->id => ['required', 'string'],
                ])->validate();

                $allQuery = ProfileAddress::whereHas('profile', function (Builder $builder) use ($user) {
                    return $builder->where('user_id', $user->id);
                })
                    ->where('type', '=', 'all');

                if (
                    (
                        $request->{'address_type_' . $address->id} !== 'all' &&
                        (clone $allQuery)->count() == 1 &&
                        $address->type !== 'all'
                    ) ||
                    (
                        $request->{'address_type_' . $address->id} !== 'all' &&
                        (clone $allQuery)->count() > 1 &&
                        $address->type == 'all'
                    ) ||
                    $request->{'address_type_' . $address->id} == 'all'
                ) {
                    if ($request->{'address_type_' . $address->id} == 'all') {
                        $allQuery->update([
                            'type' => null,
                        ]);
                    } elseif ($request->{'address_type_' . $address->id} !== 'none') {
                        ProfileAddress::whereHas('profile', function (Builder $builder) use ($user) {
                            return $builder->where('user_id', $user->id);
                        })
                            ->where('type', '=', $request->{'address_type_' . $address->id})
                            ->where('id', '!=', $address->id)
                            ->update([
                                'type' => null,
                            ]);
                    }

                    if ($request->{'address_type_' . $address->id} == 'none') {
                        $request->{'address_type_' . $address->id} = null;
                    }

                    $address->update([
                        'type' => $request->{'address_type_' . $address->id},
                    ]);

                    return redirect()->back()->with('success', __('interface.messages.postal_address_updated'));
                }
            }
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }

    /**
     * Delete a profile postal address.
     *
     * @param int $id
     *
     * @return RedirectResponse
     *
     * @throws ValidationException
     */
    public function customer_profile_address_delete(int $user_id, int $id): RedirectResponse
    {
        Validator::make([
            'address_link_id' => $id,
        ], [
            'address_link_id' => ['required', 'integer'],
        ])->validate();

        if (
            ! empty(
                $user = User::where('id', '=', $user_id)
                    ->where('role', '=', 'customer')
                    ->first()
            )
        ) {
            if (
                ! empty($address = ProfileAddress::find($id)) &&
                $address->profile->user_id == $user->id &&
                empty($address->type)
            ) {
                $address->address()->delete();
                $address->delete();

                return redirect()->back()->with('success', __('interface.messages.postal_address_deleted'));
            }
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }

    /**
     * Get list of profile phone numbers.
     *
     * @param Request $request
     */
    public function customer_profile_bank_list(Request $request): void
    {
        session_write_close();

        if (
            ! empty(
                $user = User::where('id', '=', $request->user_id)
                    ->where('role', '=', 'customer')
                    ->first()
            )
        ) {
            $query = BankAccount::whereHas('profile', function (Builder $builder) use ($user) {
                return $builder->where('user_id', '=', $user->id);
            });

            $totalCount = (clone $query)->count();

            if (! empty($request->search['value'])) {
                $query = $query->where(function (Builder $query) use ($request) {
                    return $query->where('id', 'LIKE', '%' . $request->search['value'] . '%')
                        ->orWhere('bank', 'LIKE', '%' . $request->search['value'] . '%')
                        ->orWhere('iban', 'LIKE', '%' . $request->search['value'] . '%')
                        ->orWhere('bic', 'LIKE', '%' . $request->search['value'] . '%')
                        ->orWhere('owner', 'LIKE', '%' . $request->search['value'] . '%');
                });
            }

            if (! empty($request->order)) {
                foreach ($request->order as $order) {
                    switch ($request->columns[$order['column']]) {
                        case 'address':
                            $orderBy = 'address_id';
                            break;
                        case 'type':
                            $orderBy = 'type';
                            break;
                        case 'id':
                        default:
                            $orderBy = 'id';
                            break;
                    }

                    $query = $query->orderBy($orderBy, $order['dir']);
                }
            }

            $filteredCount = (clone $query)->count();

            $query = $query->offset($request->start)
                ->limit($request->length);

            header('Content-type: application/json');
            echo json_encode([
                'draw' => (int) $request->draw,
                'recordsTotal' => $totalCount,
                'recordsFiltered' => $filteredCount,
                'data' => $query
                    ->get()
                    ->transform(function (BankAccount $account) use ($user, $totalCount) {
                        $sepa = '
<a class="btn btn-primary btn-sm" data-toggle="modal" data-target="#sepaMandate' . $account->id . '"><i class="bi bi-pencil-square"></i></a>
<div class="modal fade" id="sepaMandate' . $account->id . '" tabindex="-1" aria-labelledby="sepaMandate' . $account->id . 'Label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title text-white" id="sepaMandate' . $account->id . 'Label">' . __('interface.actions.accept_sepa_mandate') . ' (' . $account->iban . ')</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="' . route('admin.customers.profile.bank.sepa', ['user_id' => $user->id, 'id' => $account->id]) . '" method="post">
                <div class="modal-body">
                    <input type="hidden" name="_token" value="' . csrf_token() . '" />
                    <input type="hidden" name="account_id" value="' . $account->id . '" />
                    <input type="hidden" name="user_id" value="' . $user->id . '" />
                    <div class="form-group row">
                        <div class="col-md-2 text-md-right">
                            <input id="accept" type="checkbox" class="form-control" name="accept" value="true">
                        </div>
                        <label for="accept" class="col-md-10 col-form-label">' . __('interface.misc.sepa_confirmation') . '</label>
                    </div>
                    <div class="form-group row">
                        <label for="name" class="col-md-3 col-form-label text-md-right">' . __('interface.data.signature') . '*</label>

                        <div class="col-md-9">
                            <input id="name" type="text" class="form-control" name="name">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> ' . __('interface.actions.accept') . '</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">' . __('interface.actions.close') . '</button>
                </div>
            </form>
        </div>
    </div>
</div>
';

                        return (object) [
                            'iban' => $account->iban,
                            'bic' => $account->bic,
                            'bank' => $account->bank,
                            'owner' => $account->owner,
                            'sepa' => empty($account->sepa_mandate_signed_at) ? $sepa : '<button type="button" class="btn btn-primary btn-sm" disabled><i class="bi bi-check-circle"></i></button>',
                            'primary' => ! $account->primary ? '<a href="' . route('admin.customers.profile.bank.primary', ['user_id' => $user->id, 'id' => $account->id]) . '" class="btn btn-success btn-sm"><i class="bi bi-check-circle"></i></a>' : '<button type="button" class="btn btn-success btn-sm" disabled><i class="bi bi-check-circle"></i></button>',
                            'delete' => '<a href="' . route('admin.customers.profile.bank.delete', ['user_id' => $user->id, 'id' => $account->id]) . '" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i></a>',
                        ];
                    })
            ]);
        }
    }

    /**
     * Create a profile bank account.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     *
     * @throws ValidationException
     */
    public function customer_profile_bank_create(Request $request): RedirectResponse
    {
        Validator::make($request->toArray(), [
            'iban' => ['required', 'string', 'max:255'],
            'bic' => ['required', 'string', 'max:255'],
            'bank' => ['string', 'max:255', 'nullable'],
            'owner' => ['required', 'string', 'max:255'],
            'primary' => ['nullable', 'string'],
        ])->validate();

        if (
            ! empty(
                $user = User::where('id', '=', $request->user_id)
                    ->where('role', '=', 'customer')
                    ->first()
            )
        ) {
            if (! empty($profile = $user->profile)) {
                $primary = ! empty($request->primary) && $request->primary == 'true';

                if ($primary) {
                    $profile->bankAccounts()
                        ->update([
                            'primary' => false,
                        ]);
                }

                BankAccount::create([
                    'profile_id' => $profile->id,
                    'iban' => $request->iban,
                    'bic' => $request->bic,
                    'bank' => $request->bank,
                    'owner' => $request->owner,
                    'primary' => $primary
                ]);

                return redirect()->back()->with('success', __('interface.messages.bank_added'));
            }
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }

    /**
     * Get list of profile phone numbers.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     *
     * @throws ValidationException
     */
    public function customer_profile_bank_sepa(Request $request): RedirectResponse
    {
        Validator::make($request->toArray(), [
            'account_id' => ['required', 'integer'],
            'name' => ['required', 'string'],
            'accept' => ['required'],
        ])->validate();

        if (
            ! empty(
                $user = User::where('id', '=', $request->user_id)
                    ->where('role', '=', 'customer')
                    ->first()
            )
        ) {
            if (
                ! empty($account = BankAccount::find($request->account_id)) &&
                $account->profile->user_id == $user->id
            ) {
                $account->update([
                    'sepa_mandate' => 'SEPA' . $user->id . $account->id . Carbon::now()->format('YmdHis'),
                    'sepa_mandate_signed_at' => Carbon::now(),
                    'sepa_mandate_signature' => Hash::make($request->name),
                ]);

                return redirect()->back()->with('success', __('interface.messages.bank_sepa_signed'));
            }
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }

    /**
     * Make a profile bank account primary.
     *
     * @param int $id
     *
     * @return RedirectResponse
     *
     * @throws ValidationException
     */
    public function customer_profile_bank_primary(int $user_id, int $id): RedirectResponse
    {
        Validator::make([
            'account_id' => $id,
        ], [
            'account_id' => ['required', 'integer'],
        ])->validate();

        if (
            ! empty(
                $user = User::where('id', '=', $user_id)
                    ->where('role', '=', 'customer')
                    ->first()
            )
        ) {
            if (
                ! empty($account = BankAccount::find($id)) &&
                $account->profile->user_id == $user->id &&
                ! $account->primary
            ) {
                $user->profile
                    ->bankAccounts()
                    ->where('id', '!=', $account->id)
                    ->update([
                        'primary' => false,
                    ]);

                $account->update([
                    'primary' => true,
                ]);

                return redirect()->back()->with('success', __('interface.messages.bank_primary'));
            }
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }

    /**
     * Delete a profile bank account.
     *
     * @param int $id
     *
     * @return RedirectResponse
     *
     * @throws ValidationException
     */
    public function customer_profile_bank_delete(int $user_id, int $id): RedirectResponse
    {
        Validator::make([
            'account_id' => $id,
        ], [
            'account_id' => ['required', 'integer'],
        ])->validate();

        if (
            ! empty(
                $user = User::where('id', '=', $user_id)
                    ->where('role', '=', 'customer')
                    ->first()
            )
        ) {
            if (
                ! empty($account = BankAccount::find($id)) &&
                $account->profile->user_id == $user->id
            ) {
                $account->delete();

                return redirect()->back()->with('success', __('interface.messages.bank_deleted'));
            }
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }

    /**
     * Get list of profile phone numbers.
     *
     * @param Request $request
     */
    public function customer_transaction_list(Request $request): void
    {
        session_write_close();

        if (
            ! empty(
                $user = User::where('id', '=', $request->user_id)
                    ->where('role', '=', 'customer')
                    ->first()
            )
        ) {
            $query = PrepaidHistory::where('user_id', '=', $user->id);

            $totalCount = (clone $query)->count();

            if (! empty($request->search['value'])) {
                $query = $query->where(function (Builder $query) use ($request) {
                    return $query->where('id', 'LIKE', '%' . $request->search['value'] . '%')
                        ->orWhere('created_at', 'LIKE', '%' . $request->search['value'] . '%')
                        ->orWhere('contract_id', 'LIKE', '%' . $request->search['value'] . '%')
                        ->orWhere('invoice_id', 'LIKE', '%' . $request->search['value'] . '%')
                        ->orWhere('amount', 'LIKE', '%' . $request->search['value'] . '%')
                        ->orWhere('transaction_method', 'LIKE', '%' . $request->search['value'] . '%')
                        ->orWhere('transaction_id', 'LIKE', '%' . $request->search['value'] . '%');
                });
            }

            if (! empty($request->order)) {
                foreach ($request->order as $order) {
                    switch ($request->columns[$order['column']]) {
                        case 'created_at':
                            $orderBy = 'created_at';
                            break;
                        case 'contract_id':
                            $orderBy = 'contract_id';
                            break;
                        case 'invoice_id':
                            $orderBy = 'invoice_id';
                            break;
                        case 'amount':
                            $orderBy = 'amount';
                            break;
                        case 'transaction_method':
                            $orderBy = 'transaction_method';
                            break;
                        case 'transaction_id':
                            $orderBy = 'transaction_id';
                            break;
                        case 'id':
                        default:
                            $orderBy = 'id';
                            break;
                    }

                    $query = $query->orderBy($orderBy, $order['dir']);
                }
            }

            $filteredCount = (clone $query)->count();

            $query = $query->offset($request->start)
                ->limit($request->length);

            header('Content-type: application/json');
            echo json_encode([
                'draw' => (int) $request->draw,
                'recordsTotal' => $totalCount,
                'recordsFiltered' => $filteredCount,
                'data' => $query
                    ->get()
                    ->transform(function (PrepaidHistory $entry) use ($user, $totalCount) {
                        $edit = '
<a class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editTransaction' . $entry->id . '"><i class="bi bi-pencil-square"></i></a>
<div class="modal fade" id="editTransaction' . $entry->id . '" tabindex="-1" aria-labelledby="editTransaction' . $entry->id . 'Label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title" id="editTransaction' . $entry->id . 'Label">' . __('interface.actions.edit') . ' (#' . $entry->id . ')</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="' . route('admin.customers.transactions.update', $entry->user_id) . '" method="post">
                <input type="hidden" name="_token" value="' . csrf_token() . '" />
                <input type="hidden" name="booking_id" value="' . $entry->id . '" />
                <div class="modal-body">
                    <div class="form-group row">
                        <label for="contract_id" class="col-md-4 col-form-label text-md-right">' . __('interface.data.contract_id') . '</label>

                        <div class="col-md-8">
                            <input id="contract_id" type="number" class="form-control" name="contract_id" value="' . $entry->contract_id . '">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="invoice_id" class="col-md-4 col-form-label text-md-right">' . __('interface.data.invoice_id') . '</label>

                        <div class="col-md-8">
                            <input id="invoice_id" type="number" class="form-control" name="invoice_id" value="' . $entry->invoice_id . '">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="amount" class="col-md-4 col-form-label text-md-right">' . __('interface.data.amount') . '</label>

                        <div class="col-md-8">
                            <div class="input-group">
                                <input id="amount" type="number" step="0.01" class="form-control" name="amount" value="' . $entry->amount . '">
                                <div class="input-group-append">
                                    <span class="input-group-text" id="basic-addon2">€</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-warning"><i class="bi bi-pencil-square"></i> ' . __('interface.actions.edit') . '</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">' . __('interface.actions.close') . '</button>
                </div>
            </form>
        </div>
    </div>
</div>
';

                        return (object) [
                            'date' => $entry->created_at->format('d.m.Y, H:i'),
                            'contract_id' => ! empty($contract = $entry->contract) ? $contract->number : __('interface.misc.not_available'),
                            'invoice_id' => ! empty($invoice = $entry->invoice) ? $invoice->number : __('interface.misc.not_available'),
                            'amount' => number_format($entry->amount, 2) . ' €',
                            'transaction_method' => empty($transactionMethod) ? (empty($entry->creator) ? '<i class="bi bi-robot mr-2"></i> ' . __('interface.data.system') : '<i class="bi bi-pencil-square mr-2"></i> ' . __('interface.data.manual') . ' (' . $entry->creator->realName . ')') : $entry->transaction_method,
                            'transaction_id' => (empty($transactionId = $entry->transaction_id) ? __('interface.misc.not_available') : $transactionId) . ' (#' . $entry->id . ')',
                            'edit' => $edit,
                            'delete' => '<a href="' . route('admin.customers.transactions.delete', ['user_id' => $entry->user_id, 'id' => $entry->id]) . '" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i></a>',
                        ];
                    })
            ]);
        }
    }

    /**
     * Create a new contract.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     *
     * @throws ValidationException
     */
    public function customer_transaction_create(Request $request): RedirectResponse
    {
        Validator::make($request->toArray(), [
            'amount' => ['required', 'numeric'],
            'contract_id' => ['nullable', 'integer'],
            'invoice_id' => ['nullable', 'integer'],
        ])->validate();

        PrepaidHistory::create([
            'user_id' => $request->user_id,
            'creator_user_id' => Auth::id(),
            'contract_id' => ! empty($contractId = $request->contract_id) ? $contractId : null,
            'invoice_id' => ! empty($invoiceId = $request->invoice_id) ? $invoiceId : null,
            'amount' => $request->amount,
        ]);

        return redirect()->back()->with('success', __('interface.messages.transaction_added'));
    }

    /**
     * Update an existing contract.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     *
     * @throws ValidationException
     */
    public function customer_transaction_update(Request $request): RedirectResponse
    {
        Validator::make($request->toArray(), [
            'booking_id' => ['required', 'integer'],
            'amount' => ['required', 'numeric'],
            'contract_id' => ['nullable', 'integer'],
            'invoice_id' => ['nullable', 'integer'],
        ])->validate();

        /* @var PrepaidHistory $entry */
        if (! empty($entry = PrepaidHistory::find($request->booking_id))) {
            $entry->update([
                'creator_user_id' => Auth::id(),
                'contract_id' => ! empty($contractId = $request->contract_id) ? $contractId : null,
                'invoice_id' => ! empty($invoiceId = $request->invoice_id) ? $invoiceId : null,
                'amount' => $request->amount,
            ]);

            return redirect()->back()->with('success', __('interface.messages.transaction_updated'));
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }

    /**
     * Delete an existing contract.
     *
     * @param int $id
     *
     * @return RedirectResponse
     *
     * @throws ValidationException
     */
    public function customer_transaction_delete(int $id): RedirectResponse
    {
        Validator::make([
            'booking_id' => $id,
        ], [
            'booking_id' => ['required', 'integer'],
        ])->validate();

        /* @var PrepaidHistory $entry */
        if (! empty($entry = PrepaidHistory::find($id))) {
            $entry->delete();

            return redirect()->back()->with('success', __('interface.messages.transaction_deleted'));
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }
}
