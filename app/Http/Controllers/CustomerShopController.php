<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Accounting\Prepaid\PrepaidHistory;
use App\Models\Content\Page;
use App\Models\Content\PageAcceptance;
use App\Models\Shop\Configurator\ShopConfiguratorCategory;
use App\Models\Shop\Configurator\ShopConfiguratorField;
use App\Models\Shop\Configurator\ShopConfiguratorFieldOption;
use App\Models\Shop\Configurator\ShopConfiguratorForm;
use App\Models\Shop\OrderQueue\ShopOrderQueue;
use App\Models\Shop\OrderQueue\ShopOrderQueueField;
use App\Models\Shop\OrderQueue\ShopOrderQueueHistory;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class CustomerShopController extends Controller
{
    /**
     * Show the contents of a shop category.
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface
     *
     * @return Renderable
     */
    public function render_category(): Renderable
    {
        /* @var ShopConfiguratorCategory $category */
        if (! empty($category = request()->get('category'))) {
            $categories = $category->categories()
                ->where('public', '=', true)
                ->get();
            $forms = $category->forms()
                ->where('public', '=', true)
                ->whereHas('fields')
                ->get()
                ->filter(function (ShopConfiguratorForm $form) {
                    return empty(Auth::user()) ||
                        in_array($form->contractType->type, [
                            'prepaid_auto',
                            'prepaid_manual',
                        ]) || (
                            Auth::user()->validProfile &&
                            ! in_array($form->contractType->type, [
                                'prepaid_auto',
                                'prepaid_manual',
                            ])
                        );
                });
        } else {
            $categories = ShopConfiguratorCategory::whereNull('category_id')
                ->where('public', '=', true)
                ->get();
            $forms = ShopConfiguratorForm::whereNull('category_id')
                ->where('public', '=', true)
                ->whereHas('fields')
                ->get()
                ->filter(function (ShopConfiguratorForm $form) {
                    return empty(Auth::user()) ||
                        in_array($form->contractType->type, [
                            'prepaid_auto',
                            'prepaid_manual',
                        ]) || (
                            Auth::user()->validProfile &&
                            ! in_array($form->contractType->type, [
                                'prepaid_auto',
                                'prepaid_manual',
                            ])
                        );
                });
        }

        return view('customer.shop.category', [
            'category'   => $category,
            'categories' => $categories,
            'forms'      => $forms,
        ]);
    }

    /**
     * Show the contents of a shop form.
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface
     *
     * @return RedirectResponse|Renderable
     */
    public function render_form()
    {
        /* @var ShopConfiguratorForm $form */
        if (! empty($form = request()->get('form'))) {
            if (
                empty(Auth::user()) ||
                in_array($form->contractType->type, [
                    'prepaid_auto',
                    'prepaid_manual',
                ]) || (
                    Auth::user()->validProfile &&
                    ! in_array($form->contractType->type, [
                        'prepaid_auto',
                        'prepaid_manual',
                    ])
                )
            ) {
                return view('customer.shop.form', [
                    'form' => $form,
                ]);
            } else {
                return redirect()->route('public.shop')->with('warning', __('interface.messages.order_denied_incomplete_profile'));
            }
        } else {
            return redirect()->route('public.shop')->with('warning', __('interface.misc.something_wrong_notice'));
        }
    }

    /**
     * Process a new order.
     *
     * @param Request $request
     *
     * @throws ValidationException
     *
     * @return RedirectResponse
     */
    public function process(Request $request): RedirectResponse
    {
        Validator::make($request->toArray(), [
            'form_id' => ['required', 'integer'],
        ])->validate();

        /* @var ShopConfiguratorForm $form */
        if (
            ! empty(
                $form = ShopConfiguratorForm::where('public', '=', true)
                    ->whereHas('fields')
                    ->where('id', '=', $request->form_id)
                    ->first()
            )
        ) {
            $user = Auth::user();

            if (
                ! $user->validProfile &&
                ! in_array($form->contractType->type, [
                    'prepaid_auto',
                    'prepaid_manual',
                ])
            ) {
                return redirect()->back()->with('warning', __('interface.messages.order_denied_incomplete_profile'));
            }

            $validationRules = [];

            if (! empty($acceptable = Page::acceptable()->get())) {
                $acceptable->each(function (Page $page) use (&$validationRules) {
                    $validationRules['accept_' . $page->id] = ['required', 'string'];
                });
            }

            $form->fields->each(function (ShopConfiguratorField $field) use (&$validationRules) {
                $rules = [];

                if ($field->required) {
                    $rules[] = 'required';
                } else {
                    $rules[] = 'nullable';
                }

                switch ($field->type) {
                    case 'input_number':
                    case 'input_range':
                        $rules[] = 'numeric';

                        if (! empty($field->min)) {
                            $rules[] = 'min:' . $field->min;
                        }

                        if (! empty($field->max)) {
                            $rules[] = 'max:' . $field->max;
                        }

                        if (! empty($field->step)) {
                            $rules[] = 'multiple_of:' . $field->step;
                        }

                        break;
                    case 'input_radio':
                    case 'input_radio_image':
                    case 'select':
                        $availableOptions = $field->options
                            ->pluck('value')
                            ->toArray();

                        if (! empty($field->value)) {
                            $availableOptions[] = $field->value;
                        }

                        $rules[] = Rule::in($availableOptions);

                        break;
                    case 'input_text':
                    case 'textarea':
                    default:
                        $rules[] = 'string';

                        break;
                }

                $validationRules[$field->key] = $rules;
            });

            Validator::make($request->toArray(), $validationRules)->validate();

            $signedAt = Carbon::now();

            $acceptable->each(function (Page $page) use ($request, $signedAt) {
                PageAcceptance::updateOrCreate([
                    'page_id'         => $page->id,
                    'page_version_id' => $page->latest->id,
                    'user_id'         => Auth::id(),
                    'user_agent'      => $request->server('HTTP_USER_AGENT'),
                    'ip'              => $request->ip(),
                    'signature'       => md5($page->id . $page->latest->id . Auth::id() . $request->server('HTTP_USER_AGENT') . $request->ip() . $signedAt),
                    'signed_at'       => $signedAt,
                ]);
            });

            $amount = 0;

            $form->fields->each(function (ShopConfiguratorField $field) use ($request, &$amount) {
                switch ($field->type) {
                    case 'input_number':
                    case 'input_range':
                        $amount += $field->amount * ($request->{$field->key} / $field->step);

                        break;
                    case 'input_radio':
                    case 'input_radio_image':
                    case 'select':
                        $amount += $field->amount;

                        /* @var ShopConfiguratorFieldOption $option */
                        $option = $field->options
                            ->where('value', '=', $request->{$field->key})
                            ->first();

                        if (! empty($option)) {
                            $amount += $option->amount;
                        }

                        break;
                    case 'input_checkbox':
                        if (! empty($request->{$field->key})) {
                            $amount += $field->amount;
                        }

                        break;
                    case 'input_text':
                    case 'textarea':
                    case 'hidden':
                    default:
                        $amount += $field->amount;

                        break;
                }
            });

            if ($form->contractType->invoiceType->type == 'prepaid') {
                if (Auth::user()->prepaidAccountBalance >= $amount || $amount === 0) {
                    PrepaidHistory::create([
                        'user_id'            => Auth::id(),
                        'amount'             => $amount * ((100 + $form->vatRate) / 100) * (-1),
                        'transaction_method' => 'account',
                    ]);
                } else {
                    return redirect()->back()->with('warning', __('interface.messages.order_denied_credits'));
                }
            }

            /* @var ShopOrderQueue $queueItem */
            if (
                ! empty(
                    $queueItem = ShopOrderQueue::create([
                        'user_id'        => Auth::id(),
                        'form_id'        => $form->id,
                        'tracker_id'     => $form->tracker_id ?? null,
                        'product_type'   => $form->product_type,
                        'verified'       => true,
                        'approved'       => ! $form->approval,
                        'amount'         => $amount,
                        'vat_percentage' => $form->vatRate,
                        'reverse_charge' => $form->reverseCharge,
                    ])
                )
            ) {
                ShopOrderQueueHistory::create([
                    'order_id' => $queueItem->id,
                    'type'     => 'success',
                    'message'  => 'Field validation succeeded.',
                ]);

                if ($queueItem->approved) {
                    ShopOrderQueueHistory::create([
                        'order_id' => $queueItem->id,
                        'type'     => 'success',
                        'message'  => 'Order approval succeeded.',
                    ]);
                }

                $form->fields->each(function (ShopConfiguratorField $field) use ($request, $queueItem) {
                    $option = $field->options
                        ->where('value', '=', $request->{$field->key})
                        ->first();

                    ShopOrderQueueField::create([
                        'order_id'  => $queueItem->id,
                        'field_id'  => $field->id,
                        'option_id' => ! empty($option) ? $option->id : null,
                        'key'       => $field->key,
                        'value'     => $request->{$field->key},
                    ]);
                });

                if ($form->approval) {
                    $text = __('interface.messages.order_successful_approval_required');
                    $queueItem->sendEmailCreationPendingApprovalNotification();
                } else {
                    $text = __('interface.messages.order_successful_setup');
                    $queueItem->sendEmailCreationSuccessfulApprovalNotification();
                }

                return redirect()->route('customer.shop.success', $queueItem->id)
                    ->with('success', $text);
            }
        }

        return redirect()->back()->with('warning', __('interface.messages.something_wrong_input'));
    }

    /**
     * Show order success page.
     *
     * @param int $order_id
     *
     * @return RedirectResponse|Renderable
     */
    public function render_success(int $order_id)
    {
        if (
            ! empty(
                $order = ShopOrderQueue::where('user_id', '=', Auth::id())
                    ->where('id', '=', $order_id)
                    ->first()
            )
        ) {
            return view('customer.shop.success', [
                'order' => $order,
            ]);
        }

        return redirect()->route('customer.home')->with('warning', __('interface.misc.no_permission_hint'));
    }

    public function shop_orders_index(): Renderable
    {
        return view('customer.shop.orders');
    }

    /**
     * Get list of shop fields.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function shop_orders_list(Request $request): JsonResponse
    {
        session_write_close();

        $query = ShopOrderQueue::query();

        $totalCount = (clone $query)->count();

        if (! empty($request->search['value'])) {
            $query = $query->where(function (Builder $query) use ($request) {
                return $query->where('id', 'LIKE', '%' . $request->search['value'] . '%')
                    ->orWhere('form_id', 'LIKE', '%' . $request->search['value'] . '%')
                    ->orWhereHas('form', function (Builder $builder) use ($request) {
                        return $builder->where('name', 'LIKE', '%' . $request->search['value'] . '%');
                    })
                    ->orWhere('user_id', 'LIKE', '%' . $request->search['value'] . '%')
                    ->orWhere('product_type', 'LIKE', '%' . $request->search['value'] . '%')
                    ->orWhere('approved', 'LIKE', '%' . $request->search['value'] . '%')
                    ->orWhere('disapproved', 'LIKE', '%' . $request->search['value'] . '%');
            });
        }

        if (! empty($request->order)) {
            foreach ($request->order as $order) {
                switch ($request->columns[$order['column']]) {
                    case 'status':
                        $orderBy       = 'approved';
                        $orderBySecond = 'disapproved';
                        $orderByThird  = 'setup';
                        $orderByFourth = 'fails';

                        break;
                    case 'amount':
                        $orderBy = 'amount';

                        break;
                    case 'product_type':
                        $orderBy = 'product_type';

                        break;
                    case 'user':
                        $orderBy = 'user_id';

                        break;
                    case 'form':
                        $orderBy = 'form_id';

                        break;
                    case 'id':
                    default:
                        $orderBy = 'id';

                        break;
                }

                $query = $query->orderBy($orderBy, $order['dir']);

                if (isset($orderBySecond)) {
                    $query = $query->orderBy($orderBySecond, $order['dir']);
                }

                if (isset($orderByThird)) {
                    $query = $query->orderBy($orderByThird, $order['dir']);
                }

                if (isset($orderByFourth)) {
                    $query = $query->orderBy($orderByFourth, $order['dir']);
                }
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
                ->transform(function (ShopOrderQueue $queue) {
                    if ($queue->history->isNotEmpty()) {
                        $historyRows = '';

                        $queue->history->sortByDesc('id')->each(function (ShopOrderQueueHistory $history) use (&$historyRows) {
                            $class = 'table-' . $history->type;

                            switch ($history->type) {
                                case 'success':
                                    $icon = 'bi bi-check-circle';

                                    break;
                                case 'warning':
                                    $icon = 'bi bi-exclamation-triangle';

                                    break;
                                case 'danger':
                                    $icon = 'bi bi-exclamation-circle';

                                    break;
                                case 'info':
                                    $icon = 'bi bi-info-circle';

                                    break;
                                default:
                                    $icon = null;

                                    break;
                            }

                            $historyRows .= '
<tr class="' . $class . '">
    <td>' . (! empty($icon) ? '<i class="' . $icon . '"></i>' : '') . '</td>
    <td>' . $history->created_at->format('d.m.Y, H:i') . '</td>
    <td>' . __($history->message) . '</td>
</tr>
';
                        });

                        $historyItems = '
<table class="table w-100 options_table">
    <thead>
        <tr>
            <td width="1%"></td>
            <td>' . __('interface.data.date') . '</td>
            <td>' . __('interface.data.message') . '</td>
        </tr>
    </thead>
    <tbody class="options_tbody">
        ' . $historyRows . '
    </tbody>
</table>
';
                    } else {
                        $historyItems = '<div class="alert alert-warning mb-0"><i class="bi bi-exclamation-triangle"></i> ' . __('interface.messages.no_history_unapproved') . '</div>';
                    }

                    $history = '
<a class="btn btn-primary btn-sm" data-toggle="modal" data-target="#historyQueueItem' . $queue->id . '"><i class="bi bi-list"></i></a>
<div class="modal fade" id="historyQueueItem' . $queue->id . '" tabindex="-1" aria-labelledby="historyQueueItem' . $queue->id . 'Label" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="historyQueueItem' . $queue->id . 'Label">' . __('interface.data.history') . ' (' . $queue->number . ')</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                ' . $historyItems . '
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">' . __('interface.actions.close') . '</button>
            </div>
        </div>
    </div>
</div>
';

                    $status = '';

                    if ($queue->approved) {
                        $status .= '<span class="badge badge-success"><i class="bi bi-check-circle"></i> ' . __('interface.status.approved') . '</span>';

                        if ($queue->verified) {
                            $status .= '<br><span class="badge badge-success"><i class="bi bi-check-circle"></i> ' . __('interface.status.verified') . '</span>';

                            if ($queue->setup) {
                                $status .= '<br><span class="badge badge-success"><i class="bi bi-check-circle"></i> ' . __('interface.status.completed') . '</span>';
                            } else {
                                if ($queue->fails < 3) {
                                    $status .= '<br><span class="badge badge-success"><i class="bi bi-play-circle"></i> ' . __('interface.status.running') . '</span>';
                                } else {
                                    $status .= '<br><span class="badge badge-danger"><i class="bi bi-stop-circle"></i> ' . __('interface.status.failed') . '</span>';
                                }
                            }

                            if ($queue->fails > 0) {
                                $status .= '<span class="badge badge-warning ml-1"><i class="bi bi-exclamation-triangle"></i> ' . __('interface.data.fails_num', [
                                    'num' => $queue->fails,
                                ]) . '</span>';
                            }
                        } else {
                            if ($queue->invalid) {
                                $status .= '<br><span class="badge badge-warning"><i class="bi bi-exclamation-triangle"></i> ' . __('interface.status.invalid') . '</span>';
                            } else {
                                $status .= '<br><span class="badge badge-success"><i class="bi bi-play-circle"></i> ' . __('interface.status.verifying') . '</span>';
                            }
                        }
                    } elseif ($queue->disapproved) {
                        $status .= '<span class="badge badge-danger"><i class="bi bi-x-circle"></i> ' . __('interface.status.disapproved') . '</span>';
                    } else {
                        $status .= '<span class="badge badge-warning"><i class="bi bi-play-circle"></i> ' . __('interface.data.approval') . '</span>';
                    }

                    return (object) [
                        'id'           => $queue->number,
                        'user'         => $queue->user->realName ?? __('interface.misc.not_available'),
                        'form'         => __($queue->form->name),
                        'product_type' => ! empty($handler = $queue->handler) ? $handler->name() : '&lt;' . $queue->product_type . '&gt;',
                        'amount'       => number_format($queue->amount, 2) . ' €<span class="d-block small">' . number_format($queue->amount * (100 + $queue->vat_percentage) / 100, 2) . ' €</span>',
                        'status'       => $status,
                        'history'      => $history,
                    ];
                }),
        ]);
    }
}
