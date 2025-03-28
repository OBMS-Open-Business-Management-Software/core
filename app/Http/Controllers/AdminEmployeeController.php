<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Accounting\Contract\ContractType;
use App\Models\Accounting\Invoice\InvoiceType;
use App\Models\Accounting\PositionDiscount;
use App\Models\Address\Country;
use App\Models\Profile\Profile;
use App\Models\User;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AdminEmployeeController extends Controller
{
    /**
     * Show list of employees.
     *
     * @return Renderable
     */
    public function employee_index(): Renderable
    {
        return view('admin.employee.home');
    }

    /**
     * Get list of profile email addresses.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function employee_list(Request $request): JsonResponse
    {
        session_write_close();

        $query = User::where(function (Builder $query) {
            return $query->where('role', '=', 'employee')
                ->orWhere('role', '=', 'admin');
        });

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

        return response()->json([
            'draw'            => (int) $request->draw,
            'recordsTotal'    => $totalCount,
            'recordsFiltered' => $filteredCount,
            'data'            => $query
                ->get()
                ->transform(function (User $user) {
                    return (object) [
                        'id'     => $user->number,
                        'name'   => $user->realName,
                        'type'   => $user->validProfile ? '<span class="badge badge-success">' . __('interface.data.full') . '</span>' : '<span class="badge badge-warning">' . __('interface.misc.prepaid') . '</span>',
                        'status' => $user->locked ? '<span class="badge badge-warning">' . __('interface.status.locked') . '</span>' : '<span class="badge badge-success">' . __('interface.status.unlocked') . '</span>',
                        'view'   => '<a href="' . route('admin.employees.profile', $user->id) . '" class="btn btn-primary btn-sm"><i class="bi bi-eye"></i></a>',
                    ];
                }),
        ]);
    }

    /**
     * Show detailed overview of a supplier.
     *
     * @param int $user_id
     *
     * @return RedirectResponse|Renderable
     */
    public function employee_profile_index(int $user_id)
    {
        if (
            ! empty(
                $user = User::where('id', '=', $user_id)
                    ->where(function (Builder $query) {
                        return $query->where('role', '=', 'employee')
                            ->orWhere('role', '=', 'admin');
                    })
                    ->first()
            )
        ) {
            return view('admin.employee.details', [
                'user'          => $user,
                'countries'     => Country::all(),
                'contractTypes' => ContractType::all(),
                'invoiceTypes'  => InvoiceType::all(),
                'discounts'     => PositionDiscount::all(),
            ]);
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }

    /**
     * Create a new employee account.
     *
     * @param Request $request
     *
     * @throws ValidationException
     *
     * @return RedirectResponse
     */
    public function employee_create(Request $request): RedirectResponse
    {
        Validator::make($request->toArray(), [
            'name'  => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
            'role' => ['required', 'string'],
        ])->validate();

        if (
            ! empty(
                $user = User::create([
                    'name'     => $request->name,
                    'email'    => $request->email,
                    'password' => Hash::make(Str::random()),
                    'role'     => $request->role,
                ])
            )
        ) {
            return redirect()->route('admin.employees.profile', $user->id)->with('success', __('interface.messages.employee_created'));
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }

    /**
     * Change the account details.
     *
     * @param Request $request
     *
     * @throws ValidationException
     *
     * @return RedirectResponse
     */
    public function employee_profile_update(Request $request): RedirectResponse
    {
        if (
            ! empty(
                $user = User::where('id', '=', $request->user_id)
                    ->where(function (Builder $query) {
                        return $query->where('role', '=', 'employee')
                            ->orWhere('role', '=', 'admin');
                    })
                    ->first()
            )
        ) {
            if ($user->email !== $request->email) {
                Validator::make($request->toArray(), [
                    'name'  => ['required', 'string', 'max:255'],
                    'email' => ['required', 'email', 'confirmed'],
                    'role'  => ['required', 'string'],
                ])->validate();

                $user->update([
                    'name'              => $request->name,
                    'email'             => $request->email,
                    'email_verified_at' => null,
                    'role'              => $request->role,
                ]);
            } else {
                Validator::make($request->toArray(), [
                    'name' => ['required', 'string', 'max:255'],
                    'role' => ['required', 'string'],
                ])->validate();

                $user->update([
                    'name' => $request->name,
                    'role' => $request->role,
                ]);
            }

            return redirect()->back()->with('success', __('interface.messages.profile_updated'));
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }

    /**
     * Lock employee account.
     *
     * @param int $user_id
     *
     * @return RedirectResponse
     */
    public function employee_lock(int $user_id): RedirectResponse
    {
        if (
            ! empty(
                $user = User::where('id', '=', $user_id)
                    ->where(function (Builder $query) {
                        return $query->where('role', '=', 'employee')
                            ->orWhere('role', '=', 'admin');
                    })
                    ->first()
            ) &&
            Auth::id() !== $user->id
        ) {
            $status = ! $user->locked;

            $user->update([
                'locked' => $status,
            ]);

            if ($status) {
                return redirect()->back()->with('success', __('interface.messages.employee_locked'));
            } else {
                return redirect()->back()->with('success', __('interface.messages.employee_unlocked'));
            }
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }
}
