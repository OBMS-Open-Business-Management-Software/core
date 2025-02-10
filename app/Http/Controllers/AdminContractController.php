<?php

namespace App\Http\Controllers;

use App\Models\Accounting\Contract\Contract;
use App\Models\Accounting\Contract\ContractPosition;
use App\Models\Accounting\Contract\ContractType;
use App\Models\Accounting\Invoice\Invoice;
use App\Models\Accounting\Invoice\InvoiceHistory;
use App\Models\Accounting\Invoice\InvoicePosition;
use App\Models\Accounting\Invoice\InvoiceType;
use App\Models\Accounting\Position;
use App\Models\Accounting\PositionDiscount;
use App\Models\Accounting\Prepaid\PrepaidHistory;
use App\Models\FileManager\File;
use App\Models\UsageTracker\Tracker;
use App\Models\UsageTracker\TrackerInstance;
use App\Models\UsageTracker\TrackerItem;
use App\Models\User;
use Carbon\Carbon;
use Endroid\QrCode\ErrorCorrectionLevel;
use Exception;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use SepaQr\Data;

class AdminContractController extends Controller
{
    /**
     * Show list of contracts.
     *
     * @return Renderable
     */
    public function contract_index(): Renderable
    {
        return view('admin.contract.home', [
            'types' => ContractType::all(),
        ]);
    }

    /**
     * Show list of contracts.
     *
     * @param int $id
     *
     * @return Renderable
     */
    public function contract_details(int $id): Renderable
    {
        return view('admin.contract.details', [
            'contract' => Contract::find($id),
            'types' => ContractType::all(),
            'discounts' => PositionDiscount::all(),
            'trackers' => Tracker::all(),
        ]);
    }

    /**
     * Get list of contracts.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function contract_list(Request $request): JsonResponse
    {
        session_write_close();

        $query = Contract::query();

        if (! empty($request->user_id)) {
            $query = $query->where('user_id', '=', $request->user_id);
        }

        $totalCount = (clone $query)->count();

        if (! empty($request->search['value'])) {
            $query = $query->where(function (Builder $query) use ($request) {
                return $query->where('id', 'LIKE', '%' . $request->search['value'] . '%')
                    ->orWhere('user_id', 'LIKE', '%' . $request->search['value'] . '%');
            });
        }

        if (! empty($request->order)) {
            foreach ($request->order as $order) {
                switch ($request->columns[$order['column']]) {
                    case 'user':
                        $orderBy = 'user_id';
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
            'draw' => (int) $request->draw,
            'recordsTotal' => $totalCount,
            'recordsFiltered' => $filteredCount,
            'data' => $query
                ->get()
                ->transform(function (Contract $contract) {
                    switch ($contract->status) {
                        case 'cancelled':
                            $status = '<span class="badge badge-danger">' . __('interface.status.cancelled') . '</span>';
                            break;
                        case 'expires':
                            $status = '<span class="badge badge-warning">' . __('interface.status.expires') . '</span>';
                            break;
                        case 'started':
                            $status = '<span class="badge badge-success">' . __('interface.status.active') . '</span>';
                            break;
                        case 'template':
                        default:
                            $status = '<span class="badge badge-primary">' . __('interface.status.draft') . '</span>';
                            break;
                    }

                    $types = '';

                    ContractType::all()->each(function (ContractType $type) use ($contract, &$types) {
                        if ($contract->type_id !== $type->id) {
                            $types .= '<option value="' . $type->id . '">' . $type->name . '</option>';
                        } else {
                            $types .= '<option value="' . $type->id . '" selected>' . $type->name . '</option>';
                        }
                    });

                    if (! $contract->started) {
                        $edit = '
<a class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editContract' . $contract->id . '"><i class="bi bi-pencil-square"></i></a>
<div class="modal fade" id="editContract' . $contract->id . '" tabindex="-1" aria-labelledby="editContract' . $contract->id . 'Label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title" id="editContract' . $contract->id . 'Label">' . __('interface.actions.edit') . ' (' . $contract->number . ')</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="' . route('admin.contracts.update', $contract->id) . '" method="post">
                <div class="modal-body">
                    <input type="hidden" name="_token" value="' . csrf_token() . '" />
                    <input type="hidden" name="contract_id" value="' . $contract->id . '" />
                    <div class="form-group row">
                        <label for="user_id" class="col-md-4 col-form-label text-md-right">' . __('interface.data.user_id') . '</label>

                        <div class="col-md-8">
                            <input id="user_id" type="number" class="form-control" name="user_id" value="' . $contract->user_id . '">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="type_id" class="col-md-4 col-form-label text-md-right">' . __('interface.data.type') . '</label>

                        <div class="col-md-8">
                            <select id="type_id" class="form-control" name="type_id">
                                ' . $types . '
                            </select>
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
                    }

                    return (object) [
                        'id' => $contract->number,
                        'user' => $contract->user->realName ?? __('interface.misc.not_available'),
                        'status' => $status,
                        'type' => $contract->type->name ?? __('interface.misc.not_available'),
                        'view' => '<a href="' . route('admin.contracts.details', $contract->id) . '" class="btn btn-primary btn-sm"><i class="bi bi-eye"></i></a>',
                        'edit' => ! $contract->started ? $edit : '<button type="button" class="btn btn-warning btn-sm" disabled><i class="bi bi-pencil-square"></i></button>',
                        'delete' => ! $contract->started ? '<a href="' . route('admin.contracts.delete', $contract->id) . '" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i></a>' : '<button type="button" class="btn btn-danger btn-sm" disabled><i class="bi bi-trash"></i></button>',
                    ];
                })
        ]);
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
    public function contract_add(Request $request): RedirectResponse
    {
        Validator::make($request->toArray(), [
            'user_id' => ['required', 'integer'],
            'type_id' => ['required', 'integer'],
        ])->validate();

        if (
            ! empty($user = User::find($request->user_id)) &&
            $user->role == 'customer'
        ) {
            $contract = Contract::create([
                'user_id' => $request->user_id,
                'type_id' => $request->type_id,
            ]);

            return redirect()->route('admin.contracts.details', $contract->id)->with('success', __('interface.messages.contract_added'));
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
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
    public function contract_update(Request $request): RedirectResponse
    {
        Validator::make($request->toArray(), [
            'contract_id' => ['required', 'integer'],
            'user_id' => ['required', 'integer'],
            'type_id' => ['required', 'integer'],
        ])->validate();

        /* @var Contract $contract */
        if (
            ! empty($contract = Contract::find($request->contract_id)) &&
            ! $contract->started
        ) {
            $contract->update([
                'user_id' => $request->user_id,
                'type_id' => $request->type_id,
            ]);

            return redirect()->back()->with('success', __('interface.messages.contract_updated'));
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
    public function contract_delete(int $id): RedirectResponse
    {
        Validator::make([
            'contract_id' => $id,
        ], [
            'contract_id' => ['required', 'integer'],
        ])->validate();

        /* @var Contract $contract */
        if (
            ! empty($contract = Contract::find($id)) &&
            ! $contract->started
        ) {
            $contract->delete();

            return redirect()->route('admin.invoices.customers')->with('success', __('interface.messages.contract_deleted'));
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }

    /**
     * Start an existing contract.
     *
     * @param int $id
     *
     * @return RedirectResponse
     *
     * @throws ValidationException
     */
    public function contract_start(int $id): RedirectResponse
    {
        Validator::make([
            'contract_id' => $id,
        ], [
            'contract_id' => ['required', 'integer'],
        ])->validate();

        /* @var Contract $contract */
        if (
            ! empty($contract = Contract::find($id)) &&
            $contract->start()
        ) {
            return redirect()->back()->with('success', __('interface.messages.contract_started'));
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }

    /**
     * Start an existing contract.
     *
     * @param int $id
     *
     * @return RedirectResponse
     *
     * @throws ValidationException
     */
    public function contract_extend(int $id): RedirectResponse
    {
        Validator::make([
            'contract_id' => $id,
        ], [
            'contract_id' => ['required', 'integer'],
        ])->validate();

        /* @var Contract $contract */
        if (
            ! empty($contract = Contract::find($id)) &&
            $contract->status == 'expires' &&
            (
                $contract->type->type == 'prepaid_auto' ||
                $contract->type->type == 'prepaid_manual'
            ) &&
            $contract->user->prepaidAccountBalance >= $contract->grossSum
        ) {
            PrepaidHistory::all([
                'user_id' => $contract->user_id,
                'creator_user_id' => Auth::id(),
                'contract_id' => $contract->id,
                'amount' => $contract->grossSum * (-1),
                'transaction_method' => 'account',
                'transaction_id' => null,
            ]);

            /* @var Invoice $invoice */
            $invoice = Invoice::create([
                'user_id' => $contract->user_id,
                'type_id' => $contract->type->invoice_type_id,
                'contract_id' => $contract->id,
                'status' => 'paid',
                'reverse_charge' => $contract->user->reverseCharge,
            ]);

            $contract->positionLinks->each(function (ContractPosition $link) use ($invoice) {
                /* @var Position $position */
                $position = Position::create([
                    'order_id' => $link->position->order_id,
                    'product_id' => $link->position->product_id,
                    'discount_id' => $link->position->discount_id,
                    'name' => $link->position->name,
                    'description' => $link->position->description,
                    'amount' => $link->position->amount,
                    'vat_percentage' => $link->position->vat_percentage,
                    'quantity' => $link->position->quantity,
                ]);

                InvoicePosition::create([
                    'invoice_id' => $invoice->id,
                    'position_id' => $position->id,
                    'started_at' => $link->started_at,
                    'ended_at' => $link->ended_at,
                ]);
            });

            $invoice->update([
                'archived_at' => Carbon::now(),
            ]);

            try {
                $sepaQr = ! empty($amount = $invoice->grossSum) && $amount > 0 ? Data::create()
                    ->setName(config('company.bank.owner'))
                    ->setIban(str_replace(' ', '', config('company.bank.iban')))
                    ->setRemittanceText($invoice->number)
                    ->setAmount($amount) : null;

                if (! empty($sepaQr)) {
                    $sepaQr = \Endroid\QrCode\Builder\Builder::create()
                        ->data($sepaQr)
                        ->errorCorrectionLevel(ErrorCorrectionLevel::Medium)
                        ->build()
                        ->getDataUri();
                }
            } catch (Exception $exception) {
                $sepaQr = null;
            }

            $pdf = App::make('dompdf.wrapper')->loadView('pdf.invoice', [
                'invoice' => $invoice,
                'sepaQr' => $sepaQr,
            ]);

            $content = $pdf->output();

            /* @var File $file */
            $file = File::create([
                'user_id' => null,
                'folder_id' => null,
                'name' => $invoice->number . '.pdf',
                'data' => $content,
                'mime' => 'application/pdf',
                'size' => strlen($content),
            ]);

            $invoice->update([
                'file_id' => $file->id,
            ]);

            $contract->update([
                'last_invoice_at' => $contract->cancelled_to,
                'cancelled_at' => Carbon::now(),
                'cancellation_revoked_at' => null,
                'cancelled_to' => $contract->cancelled_to->addDays($contract->type->invoice_period),
            ]);

            InvoiceHistory::create([
                'user_id' => Auth::id(),
                'invoice_id' => $invoice->id,
                'status' => 'pay',
            ]);

            return redirect()->back()->with('success', __('interface.messages.contract_extended'));
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }

    /**
     * Stop an existing contract ignoring the cancellation period.
     *
     * @param int $id
     *
     * @return RedirectResponse
     *
     * @throws ValidationException
     */
    public function contract_stop(int $id): RedirectResponse
    {
        Validator::make([
            'contract_id' => $id,
        ], [
            'contract_id' => ['required', 'integer'],
        ])->validate();

        /* @var Contract $contract */
        if (
            ! empty($contract = Contract::find($id)) &&
            $contract->status == 'started'
        ) {
            if ($contract->type->type == 'contract_pre_pay') {
                /* @var Invoice|null $invoice */
                if (! empty($invoice = $contract->invoices()->whereNotNull('archived_at')->orderByDesc('archived_at')->first())) {
                    $factor = ($contract->type->invoice_period - $contract->last_invoice_at->diffInDays(Carbon::now())) / $contract->type->invoice_period;

                    /* @var Invoice $revokationInvoice */
                    $revokationInvoice = Invoice::create([
                        'user_id' => $contract->user_id,
                        'type_id' => $contract->type->invoice_type_id,
                        'contract_id' => $contract->id,
                        'original_id' => $invoice->id,
                        'status' => 'refund',
                        'reverse_charge' => $contract->user->reverseCharge,
                    ]);

                    $contract->positionLinks->each(function (ContractPosition $link) use ($revokationInvoice, $factor) {
                        /* @var Position $position */
                        $position = Position::create([
                            'order_id' => $link->position->order_id,
                            'product_id' => $link->position->product_id,
                            'discount_id' => $link->position->discount_id,
                            'name' => $link->position->name,
                            'description' => $link->position->description,
                            'amount' => $link->position->amount * $factor * (-1),
                            'vat_percentage' => $link->position->vat_percentage,
                            'quantity' => $link->position->quantity,
                        ]);

                        InvoicePosition::create([
                            'invoice_id' => $revokationInvoice->id,
                            'position_id' => $position->id,
                            'started_at' => $link->started_at,
                            'ended_at' => $link->ended_at,
                        ]);
                    });

                    $revokationInvoice->update([
                        'archived_at' => Carbon::now(),
                    ]);

                    try {
                        $sepaQr = ! empty($amount = $revokationInvoice->grossSum) && $amount > 0 ? Data::create()
                            ->setName(config('company.bank.owner'))
                            ->setIban(str_replace(' ', '', config('company.bank.iban')))
                            ->setRemittanceText($revokationInvoice->number)
                            ->setAmount($amount) : null;

                        if (! empty($sepaQr)) {
                            $sepaQr = \Endroid\QrCode\Builder\Builder::create()
                                ->data($sepaQr)
                                ->errorCorrectionLevel(ErrorCorrectionLevel::Medium)
                                ->build()
                                ->getDataUri();
                        }
                    } catch (Exception $exception) {
                        $sepaQr = null;
                    }

                    $pdf = App::make('dompdf.wrapper')->loadView('pdf.invoice', [
                        'invoice' => $revokationInvoice,
                        'sepaQr' => $sepaQr,
                    ]);

                    $content = $pdf->output();

                    /* @var File $file */
                    $file = File::create([
                        'user_id' => null,
                        'folder_id' => null,
                        'name' => $revokationInvoice->number . '.pdf',
                        'data' => $content,
                        'mime' => 'application/pdf',
                        'size' => strlen($content),
                    ]);

                    $revokationInvoice->update([
                        'file_id' => $file->id,
                    ]);
                }
            } elseif ($contract->type->type == 'contract_post_pay') {
                $factor = $contract->last_invoice_at->diffInDays(Carbon::now()) / $contract->type->invoice_period;

                /* @var Invoice $invoice */
                $invoice = Invoice::create([
                    'user_id' => $contract->user_id,
                    'type_id' => $contract->type->invoice_type_id,
                    'contract_id' => $contract->id,
                    'status' => 'unpaid',
                    'reverse_charge' => $contract->user->reverseCharge,
                ]);

                $contract->positionLinks->each(function (ContractPosition $link) use ($invoice, $factor) {
                    /* @var Position $position */
                    $position = Position::create([
                        'order_id' => $link->position->order_id,
                        'product_id' => $link->position->product_id,
                        'discount_id' => $link->position->discount_id,
                        'name' => $link->position->name,
                        'description' => $link->position->description,
                        'amount' => $link->position->amount * $factor,
                        'vat_percentage' => $link->position->vat_percentage,
                        'quantity' => $link->position->quantity,
                    ]);

                    InvoicePosition::create([
                        'invoice_id' => $invoice->id,
                        'position_id' => $position->id,
                        'started_at' => $link->started_at,
                        'ended_at' => $link->ended_at,
                    ]);
                });

                $invoice->update([
                    'archived_at' => Carbon::now(),
                ]);

                try {
                    $sepaQr = ! empty($amount = $invoice->grossSum) && $amount > 0 ? Data::create()
                        ->setName(config('company.bank.owner'))
                        ->setIban(str_replace(' ', '', config('company.bank.iban')))
                        ->setRemittanceText($invoice->number)
                        ->setAmount($amount) : null;

                    if (! empty($sepaQr)) {
                        $sepaQr = \Endroid\QrCode\Builder\Builder::create()
                            ->data($sepaQr)
                            ->errorCorrectionLevel(ErrorCorrectionLevel::Medium)
                            ->build()
                            ->getDataUri();
                    }
                } catch (Exception $exception) {
                    $sepaQr = null;
                }

                $pdf = App::make('dompdf.wrapper')->loadView('pdf.invoice', [
                    'invoice' => $invoice,
                    'sepaQr' => $sepaQr,
                ]);

                $content = $pdf->output();

                /* @var File $file */
                $file = File::create([
                    'user_id' => null,
                    'folder_id' => null,
                    'name' => $invoice->number . '.pdf',
                    'data' => $content,
                    'mime' => 'application/pdf',
                    'size' => strlen($content),
                ]);

                $invoice->update([
                    'file_id' => $file->id,
                ]);

                $contract->update([
                    'last_invoice_at' => Carbon::now(),
                ]);
            } elseif (
                $contract->type->type == 'prepaid_auto' ||
                $contract->type->type == 'prepaid_manual'
            ) {
                $factor = ($contract->type->invoice_period - $contract->last_invoice_at->diffInDays(Carbon::now())) / $contract->type->invoice_period;
                $refund = $contract->grossSum * $factor;

                PrepaidHistory::all([
                    'user_id' => $contract->user_id,
                    'creator_user_id' => Auth::id(),
                    'contract_id' => $contract->id,
                    'amount' => $refund,
                    'transaction_method' => 'account',
                    'transaction_id' => null,
                ]);

                /* @var Invoice $revokationInvoice */
                $revokationInvoice = Invoice::create([
                    'user_id' => $contract->user_id,
                    'type_id' => $contract->type->invoice_type_id,
                    'contract_id' => $contract->id,
                    'status' => 'refund',
                    'reverse_charge' => $contract->user->reverseCharge,
                ]);

                $contract->positionLinks->each(function (ContractPosition $link) use ($revokationInvoice, $factor) {
                    /* @var Position $position */
                    $position = Position::create([
                        'order_id' => $link->position->order_id,
                        'product_id' => $link->position->product_id,
                        'discount_id' => $link->position->discount_id,
                        'name' => $link->position->name,
                        'description' => $link->position->description,
                        'amount' => $link->position->amount * $factor * (-1),
                        'vat_percentage' => $link->position->vat_percentage,
                        'quantity' => $link->position->quantity,
                    ]);

                    InvoicePosition::create([
                        'invoice_id' => $revokationInvoice->id,
                        'position_id' => $position->id,
                        'started_at' => $link->started_at,
                        'ended_at' => $link->ended_at,
                    ]);
                });

                $revokationInvoice->update([
                    'archived_at' => Carbon::now(),
                ]);

                try {
                    $sepaQr = ! empty($amount = $revokationInvoice->grossSum) && $amount > 0 ? Data::create()
                        ->setName(config('company.bank.owner'))
                        ->setIban(str_replace(' ', '', config('company.bank.iban')))
                        ->setRemittanceText($revokationInvoice->number)
                        ->setAmount($amount) : null;

                    if (! empty($sepaQr)) {
                        $sepaQr = \Endroid\QrCode\Builder\Builder::create()
                            ->data($sepaQr)
                            ->errorCorrectionLevel(ErrorCorrectionLevel::Medium)
                            ->build()
                            ->getDataUri();
                    }
                } catch (Exception $exception) {
                    $sepaQr = null;
                }

                $pdf = App::make('dompdf.wrapper')->loadView('pdf.invoice', [
                    'invoice' => $revokationInvoice,
                    'sepaQr' => $sepaQr,
                ]);

                $content = $pdf->output();

                /* @var File $file */
                $file = File::create([
                    'user_id' => null,
                    'folder_id' => null,
                    'name' => $revokationInvoice->number . '.pdf',
                    'data' => $content,
                    'mime' => 'application/pdf',
                    'size' => strlen($content),
                ]);

                $revokationInvoice->update([
                    'file_id' => $file->id,
                ]);
            }

            $contract->update([
                'cancelled_at' => Carbon::now(),
                'cancellation_revoked_at' => null,
                'cancelled_to' => Carbon::now(),
            ]);

            return redirect()->back()->with('success', __('interface.messages.contract_stopped'));
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }

    /**
     * Cancel an existing contract taking the cancellation period into account.
     *
     * @param int $id
     *
     * @return RedirectResponse
     *
     * @throws ValidationException
     */
    public function contract_cancel(int $id): RedirectResponse
    {
        Validator::make([
            'contract_id' => $id,
        ], [
            'contract_id' => ['required', 'integer'],
        ])->validate();

        /* @var Contract $contract */
        if (
            ! empty($contract = Contract::find($id)) &&
            $contract->status == 'started'
        ) {
            if (
                $contract->type->type == 'contract_pre_pay' ||
                $contract->type->type == 'contract_post_pay'
            ) {
                if (
                    $contract->last_invoice_at->addDays($contract->type->invoice_period)->subDays($contract->type->cancellation_period)
                        ->gte(Carbon::now())
                ) {
                    $cancelledTo = $contract->last_invoice_at->addDays($contract->type->invoice_period);
                } else {
                    $cancelledTo = $contract->last_invoice_at->addDays($contract->type->invoice_period * 2);
                }
            } elseif (
                $contract->type->type == 'prepaid_auto' ||
                $contract->type->type == 'prepaid_manual'
            ) {
                $cancelledTo = $contract->last_invoice_at->addDays($contract->type->invoice_period);
            }

            if (! empty($cancelledTo)) {
                $contract->update([
                    'cancelled_at' => Carbon::now(),
                    'cancellation_revoked_at' => null,
                    'cancelled_to' => $cancelledTo,
                ]);

                return redirect()->back()->with('success', __('interface.messages.contract_stopped'));
            }
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }

    /**
     * Restart an existing contract which has been cancelled but hasn't reached
     * the final cancel date yet (is in grace period).
     *
     * @param int $id
     *
     * @return RedirectResponse
     *
     * @throws ValidationException
     */
    public function contract_restart(int $id): RedirectResponse
    {
        Validator::make([
            'contract_id' => $id,
        ], [
            'contract_id' => ['required', 'integer'],
        ])->validate();

        /* @var Contract $contract */
        if (
            ! empty($contract = Contract::find($id)) &&
            $contract->status == 'cancelled' &&
            $contract->type->type !== 'prepaid_manual'
        ) {
            $contract->update([
                'started_at' => Carbon::now(),
                'last_invoice_at' => null,
                'cancelled_at' => null,
                'cancellation_revoked_at' => null,
                'cancelled_to' => null,
            ]);

            return redirect()->back()->with('success', __('interface.messages.contract_restarted'));
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }

    /**
     * Revoke a contract cancellation.
     *
     * @param int $id
     *
     * @return RedirectResponse
     *
     * @throws ValidationException
     */
    public function contract_cancellation_revoke(int $id): RedirectResponse
    {
        Validator::make([
            'contract_id' => $id,
        ], [
            'contract_id' => ['required', 'integer'],
        ])->validate();

        /* @var Contract $contract */
        if (
            ! empty($contract = Contract::find($id)) &&
            $contract->status == 'expires' &&
            $contract->type->type !== 'prepaid_manual'
        ) {
            $contract->update([
                'cancelled_at' => null,
                'cancellation_revoked_at' => null,
                'cancelled_to' => null,
            ]);

            return redirect()->back()->with('success', __('interface.messages.contract_cancellation_revoked'));
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }

    /**
     * Create a new contract position.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     *
     * @throws ValidationException
     */
    public function contract_positions_add(Request $request): RedirectResponse
    {
        Validator::make($request->toArray(), [
            'contract_id' => ['required', 'integer'],
            'name' => ['required', 'string'],
            'description' => ['required', 'string'],
            'amount' => ['required', 'numeric'],
            'vat_percentage' => ['nullable', 'numeric'],
            'quantity' => ['required', 'numeric'],
            'discount_id' => ['nullable', 'integer'],
            'tracker' => ['nullable', 'integer'],
        ])->validate();

        if (! empty($request->service_runtime)) {
            Validator::make($request->toArray(), [
                'started_at' => ['required', 'string'],
                'ended_at' => ['required', 'string'],
            ])->validate();
        }

        /* @var Contract $contract */
        if (
            ! empty($contract = Contract::find($request->contract_id)) &&
            ! $contract->started
        ) {
            $position = Position::create([
                'discount_id' => ! empty($request->discount_id) ? $request->discount_id : null,
                'name' => $request->name,
                'description' => $request->description,
                'amount' => $request->amount,
                'vat_percentage' => ! empty($request->vat_percentage) ? $request->vat_percentage : 0,
                'quantity' => $request->quantity,
            ]);

            /* @var ContractPosition $positionLink */
            $positionLink = ContractPosition::create([
                'contract_id' => $request->contract_id,
                'position_id' => $position->id,
                'started_at' => ! empty($request->service_runtime) ? Carbon::parse($request->started_at) : null,
                'ended_at' => ! empty($request->service_runtime) ? Carbon::parse($request->ended_at) : null,
            ]);

            if (
                ! empty($request->tracker) &&
                $positionLink->contract->type->type == 'contract_post_pay'
            ) {
                TrackerInstance::updateOrCreate([
                    'contract_id' => $contract->id,
                    'contract_position_id' => $positionLink->id,
                ], [
                    'tracker_id' => $request->tracker,
                ]);
            } else {
                TrackerInstance::where('contract_id', '=', $contract->id)
                    ->where('contract_position_id', '=', $positionLink->id)
                    ->delete();
            }

            return redirect()->back()->with('success', __('interface.messages.contract_position_added'));
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }

    /**
     * Update an existing contract position.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     *
     * @throws ValidationException
     */
    public function contract_positions_update(Request $request): RedirectResponse
    {
        Validator::make($request->toArray(), [
            'position_id' => ['required', 'integer'],
            'name' => ['required', 'string'],
            'description' => ['required', 'string'],
            'amount' => ['required', 'numeric'],
            'vat_percentage' => ['nullable', 'numeric'],
            'quantity' => ['required', 'numeric'],
            'discount_id' => ['nullable', 'integer'],
            'tracker' => ['nullable', 'integer'],
        ])->validate();

        if (! empty($request->service_runtime)) {
            Validator::make($request->toArray(), [
                'started_at' => ['required', 'string'],
                'ended_at' => ['required', 'string'],
            ])->validate();
        }

        /* @var ContractPosition $position */
        if (
            ! empty($position = ContractPosition::find($request->position_id)) &&
            ! $position->contract->started
        ) {
            $position->position->update([
                'discount_id' => ! empty($request->discount_id) ? $request->discount_id : null,
                'name' => $request->name,
                'description' => $request->description,
                'amount' => $request->amount,
                'vat_percentage' => ! empty($request->vat_percentage) ? $request->vat_percentage : 0,
                'quantity' => $request->quantity,
            ]);

            $position->update([
                'started_at' => ! empty($request->service_runtime) ? Carbon::parse($request->started_at) : null,
                'ended_at' => ! empty($request->service_runtime) ? Carbon::parse($request->ended_at) : null,
            ]);

            if (
                ! empty($request->tracker) &&
                $position->contract->type->type == 'contract_post_pay'
            ) {
                TrackerInstance::updateOrCreate([
                    'contract_id' => $position->contract_id,
                    'contract_position_id' => $position->id,
                ], [
                    'tracker_id' => $request->tracker,
                ]);
            } else {
                TrackerInstance::where('contract_id', '=', $position->contract_id)
                    ->where('contract_position_id', '=', $position->id)
                    ->delete();
            }

            return redirect()->back()->with('success', __('interface.messages.contract_position_updated'));
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }

    /**
     * Delete an existing contract position.
     *
     * @param int $contract_id
     * @param int $id
     *
     * @return RedirectResponse
     *
     * @throws ValidationException
     */
    public function contract_positions_delete(int $contract_id, int $id): RedirectResponse
    {
        Validator::make([
            'contract_id' => $contract_id,
            'position_id' => $id,
        ], [
            'contract_id' => ['required', 'integer'],
            'position_id' => ['required', 'integer'],
        ])->validate();

        /* @var ContractPosition $position */
        if (
            ! empty($position = ContractPosition::find($id)) &&
            ! $position->contract->started
        ) {
            $position->position->delete();
            $position->delete();

            return redirect()->back()->with('success', __('interface.messages.contract_position_deleted'));
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }

    /**
     * Show list of contract types.
     *
     * @return Renderable
     */
    public function contract_types_index(): Renderable
    {
        return view('admin.contract.types', [
            'types' => InvoiceType::all(),
        ]);
    }

    /**
     * Get list of contract types.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function contract_types_list(Request $request): JsonResponse
    {
        session_write_close();

        $query = ContractType::query();

        $totalCount = (clone $query)->count();

        if (! empty($request->search['value'])) {
            $query = $query->where(function (Builder $query) use ($request) {
                return $query->where('id', 'LIKE', '%' . $request->search['value'] . '%')
                    ->orWhere('name', 'LIKE', '%' . $request->search['value'] . '%')
                    ->orWhere('description', 'LIKE', '%' . $request->search['value'] . '%')
                    ->orWhere('type', 'LIKE', '%' . $request->search['value'] . '%')
                    ->orWhere('invoice_period', 'LIKE', '%' . $request->search['value'] . '%')
                    ->orWhere('cancellation_period', 'LIKE', '%' . $request->search['value'] . '%');
            });
        }

        if (! empty($request->order)) {
            foreach ($request->order as $order) {
                switch ($request->columns[$order['column']]) {
                    case 'name':
                        $orderBy = 'name';
                        break;
                    case 'description':
                        $orderBy = 'description';
                        break;
                    case 'invoice_period':
                        $orderBy = 'invoice_period';
                        break;
                    case 'cancellation_period':
                        $orderBy = 'cancellation_period';
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

        return response()->json([
            'draw' => (int) $request->draw,
            'recordsTotal' => $totalCount,
            'recordsFiltered' => $filteredCount,
            'data' => $query
                ->get()
                ->transform(function (ContractType $type) {
                    switch ($type->type) {
                        case 'contract_pre_pay':
                            $receiptType = __('interface.billing.contract_pre');
                            break;
                        case 'contract_post_pay':
                            $receiptType = __('interface.billing.contract_post');
                            break;
                        case 'prepaid_auto':
                            $receiptType = __('interface.billing.prepaid_auto');
                            break;
                        case 'prepaid_manual':
                            $receiptType = __('interface.billing.prepaid_manual');
                            break;
                        case 'normal':
                        default:
                            $receiptType = __('interface.status.unknown');
                            break;
                    }

                    $contractTypes = '';

                    InvoiceType::each(function (InvoiceType $contractType) use ($type, &$contractTypes) {
                        $contractTypes .= '<option value="' . $contractType->id . '"' . ($contractType->type == $type->type ? ' selected' : '') . '>' . __($contractType->name) . '</option>';
                    });

                    $edit = '
<a class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editPaymentType' . $type->id . '"><i class="bi bi-pencil-square"></i></a>
<div class="modal fade" id="editPaymentType' . $type->id . '" tabindex="-1" aria-labelledby="editPaymentType' . $type->id . 'Label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title" id="editPaymentType' . $type->id . 'Label">' . __('interface.actions.edit') . ' (' . $type->name . ')</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="' . route('admin.contracts.types.update', $type->id) . '" method="post">
                <div class="modal-body">
                    <input type="hidden" name="_token" value="' . csrf_token() . '" />
                    <input type="hidden" name="type_id" value="' . $type->id . '" />
                    <div class="form-group row">
                        <label for="name" class="col-md-4 col-form-label text-md-right">' . __('interface.data.name') . '</label>

                        <div class="col-md-8">
                            <input id="name" type="text" class="form-control" name="name" value="' . $type->name . '">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="description" class="col-md-4 col-form-label text-md-right">' . __('interface.data.description') . '</label>

                        <div class="col-md-8">
                            <input id="description" type="text" class="form-control" name="description" value="' . $type->description . '">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="type" class="col-md-4 col-form-label text-md-right">' . __('interface.data.type') . '</label>

                        <div class="col-md-8">
                            <select id="type" type="text" class="form-control type" name="type" data-id="' . $type->id . '">
                                <option value="contract_pre_pay"' . ($type->type == 'contract_pre_pay' ? ' selected' : '') . '>' . __('interface.billing.contract_pre') . '</option>
                                <option value="contract_post_pay"' . ($type->type == 'contract_post_pay' ? ' selected' : '') . '>' . __('interface.billing.contract_post') . '</option>
                                <option value="prepaid_auto"' . ($type->type == 'prepaid_auto' ? ' selected' : '') . '>' . __('interface.billing.prepaid_auto') . '</option>
                                <option value="prepaid_manual"' . ($type->type == 'prepaid_manual' ? ' selected' : '') . '>' . __('interface.billing.prepaid_manual') . '</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="invoice_type_id" class="col-md-4 col-form-label text-md-right">' . __('interface.data.invoice_type') . '</label>

                        <div class="col-md-8">
                            <select id="invoice_type_id" type="text" class="form-control type" name="invoice_type_id">
                                ' . $contractTypes . '
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="invoice_period" class="col-md-4 col-form-label text-md-right">' . __('interface.data.invoice_period') . '</label>

                        <div class="col-md-8">
                            <div class="input-group">
                                <input id="invoice_period" type="number" step="0.01" min="0.01" class="form-control" name="invoice_period" value="' . $type->invoice_period . '">
                                <div class="input-group-append">
                                    <span class="input-group-text" id="basic-addon2">' . __('interface.units.days') . '</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="cancellation_period" class="col-md-4 col-form-label text-md-right">' . __('interface.data.cancellation_period') . '</label>

                        <div class="col-md-8">
                            <div class="input-group">
                                <input id="cancellation_period" type="number" step="0.01" min="0.01" class="form-control trigger-dunning" name="cancellation_period" value="' . $type->cancellation_period . '">
                                <div class="input-group-append">
                                    <span class="input-group-text" id="basic-addon2">' . __('interface.units.days') . '</span>
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
                        'id' => $type->id,
                        'name' => $type->name,
                        'description' => $type->description,
                        'invoice_period' => $type->invoice_period . ' ' . __('interface.units.days'),
                        'invoice_type' => $type->invoiceType->name ?? __('interface.status.unknown'),
                        'cancellation_period' => $type->cancellation_period . ' ' . __('interface.units.days'),
                        'type' => $receiptType,
                        'edit' => $edit,
                        'delete' => '<a href="' . route('admin.contracts.types.delete', $type->id) . '" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i></a>',
                    ];
                })
        ]);
    }

    /**
     * Create a new contract type.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     *
     * @throws ValidationException
     */
    public function contract_types_add(Request $request): RedirectResponse
    {
        Validator::make($request->toArray(), [
            'name' => ['required', 'string'],
            'description' => ['required', 'string'],
            'type' => ['required', 'string'],
            'invoice_type_id' => ['required', 'integer'],
            'invoice_period' => ['required', 'integer'],
            'cancellation_period' => ['required', 'integer'],
        ])->validate();

        ContractType::create([
            'name' => $request->name,
            'description' => $request->description,
            'type' => $request->type,
            'invoice_type_id' => $request->invoice_type_id,
            'invoice_period' => $request->invoice_period,
            'cancellation_period' => $request->cancellation_period,
        ]);

        return redirect()->back()->with('success', __('interface.messages.contract_type_added'));
    }

    /**
     * Update an existing contract type.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     *
     * @throws ValidationException
     */
    public function contract_types_update(Request $request): RedirectResponse
    {
        Validator::make($request->toArray(), [
            'type_id' => ['required', 'integer'],
            'name' => ['required', 'string'],
            'description' => ['required', 'string'],
            'type' => ['required', 'string'],
            'invoice_type_id' => ['required', 'integer'],
            'invoice_period' => ['required', 'integer'],
            'cancellation_period' => ['required', 'integer'],
        ])->validate();

        if (! empty($type = ContractType::find($request->type_id))) {
            $type->update([
                'name' => $request->name,
                'description' => $request->description,
                'type' => $request->type,
                'invoice_type_id' => $request->invoice_type_id,
                'invoice_period' => $request->invoice_period,
                'cancellation_period' => $request->cancellation_period,
            ]);

            return redirect()->back()->with('success', __('interface.messages.contract_type_updated'));
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }

    /**
     * Delete an existing contract type.
     *
     * @param int $id
     *
     * @return RedirectResponse
     *
     * @throws ValidationException
     */
    public function contract_types_delete(int $id): RedirectResponse
    {
        Validator::make([
            'type_id' => $id,
        ], [
            'type_id' => ['required', 'integer'],
        ])->validate();

        if (! empty($type = ContractType::find($id))) {
            $type->delete();
        }

        return redirect()->back()->with('success', __('interface.messages.contract_type_deleted'));
    }

    /**
     * Show list of usage trackers.
     *
     * @return Renderable
     */
    public function contract_trackers_index(): Renderable
    {
        return view('admin.contract.trackers');
    }

    /**
     * Get list of usage trackers.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function contract_trackers_list(Request $request): JsonResponse
    {
        session_write_close();

        $query = Tracker::query();

        $totalCount = (clone $query)->count();

        if (! empty($request->search['value'])) {
            $query = $query->where(function (Builder $query) use ($request) {
                return $query->where('id', 'LIKE', '%' . $request->search['value'] . '%')
                    ->orWhere('name', 'LIKE', '%' . $request->search['value'] . '%')
                    ->orWhere('description', 'LIKE', '%' . $request->search['value'] . '%')
                    ->orWhere('vat_type', 'LIKE', '%' . $request->search['value'] . '%');
            });
        }

        if (! empty($request->order)) {
            foreach ($request->order as $order) {
                switch ($request->columns[$order['column']]) {
                    case 'name':
                        $orderBy = 'name';
                        break;
                    case 'description':
                        $orderBy = 'description';
                        break;
                    case 'vat_type':
                        $orderBy = 'vat_type';
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
            'draw' => (int) $request->draw,
            'recordsTotal' => $totalCount,
            'recordsFiltered' => $filteredCount,
            'data' => $query
                ->get()
                ->transform(function (Tracker $tracker) {
                    $edit = '
<a class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editTracker' . $tracker->id . '"><i class="bi bi-pencil-square"></i></a>
<div class="modal fade" id="editTracker' . $tracker->id . '" tabindex="-1" aria-labelledby="editTracker' . $tracker->id . 'Label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title" id="editTracker' . $tracker->id . 'Label">' . __('interface.actions.edit') . ' (' . $tracker->name . ')</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="' . route('admin.contracts.trackers.update', $tracker->id) . '" method="post">
                <div class="modal-body">
                    <input type="hidden" name="_token" value="' . csrf_token() . '" />
                    <input type="hidden" name="tracker_id" value="' . $tracker->id . '" />
                    <div class="form-group row">
                        <label for="name" class="col-md-4 col-form-label text-md-right">' . __('interface.data.name') . '</label>

                        <div class="col-md-8">
                            <input id="name" type="text" class="form-control" name="name" value="' . $tracker->name . '">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="description" class="col-md-4 col-form-label text-md-right">' . __('interface.data.description') . '</label>

                        <div class="col-md-8">
                            <input id="description" type="text" class="form-control" name="description" value="' . $tracker->description . '">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="vat_type" class="col-md-4 col-form-label text-md-right">' . __('interface.data.type') . '</label>

                        <div class="col-md-8">
                            <select id="vat_type" type="text" class="form-control type" name="vat_type">
                                <option value="basic"' . ($tracker->vat_type == 'basic' ? ' selected' : '') . '>' . __('interface.misc.basic') . '</option>
                                <option value="reduced"' . ($tracker->vat_type == 'reduced' ? ' selected' : '') . '>' . __('interface.misc.reduced') . '</option>
                            </select>
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
                        'id' => $tracker->id,
                        'name' => $tracker->name,
                        'description' => $tracker->description,
                        'view' => '<a href="' . route('admin.contracts.trackers.details', $tracker->id) . '" class="btn btn-primary btn-sm"><i class="bi bi-eye"></i></a>',
                        'edit' => $edit,
                        'delete' => '<a href="' . route('admin.contracts.trackers.delete', $tracker->id) . '" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i></a>',
                    ];
                })
        ]);
    }

    /**
     * Create a new usage tracker.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     *
     * @throws ValidationException
     */
    public function contract_trackers_add(Request $request): RedirectResponse
    {
        Validator::make($request->toArray(), [
            'name' => ['required', 'string'],
            'description' => ['required', 'string'],
            'vat_type' => ['required', 'string'],
        ])->validate();

        Tracker::create([
            'name' => $request->name,
            'description' => $request->description,
            'vat_type' => $request->vat_type,
        ]);

        return redirect()->back()->with('success', __('interface.messages.usage_tracker_added'));
    }

    /**
     * Update an existing usage tracker.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     *
     * @throws ValidationException
     */
    public function contract_trackers_update(Request $request): RedirectResponse
    {
        Validator::make($request->toArray(), [
            'tracker_id' => ['required', 'integer'],
            'name' => ['required', 'string'],
            'description' => ['required', 'string'],
            'vat_type' => ['required', 'string'],
        ])->validate();

        if (! empty($tracker = Tracker::find($request->tracker_id))) {
            $tracker->update([
                'name' => $request->name,
                'description' => $request->description,
                'vat_type' => $request->vat_type,
            ]);

            return redirect()->back()->with('success', __('interface.messages.usage_tracker_updated'));
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }

    /**
     * Delete an existing usage tracker.
     *
     * @param int $id
     *
     * @return RedirectResponse
     *
     * @throws ValidationException
     */
    public function contract_trackers_delete(int $id): RedirectResponse
    {
        Validator::make([
            'tracker_id' => $id,
        ], [
            'tracker_id' => ['required', 'integer'],
        ])->validate();

        if (! empty($tracker = Tracker::find($id))) {
            $tracker->instances->each(function (TrackerInstance $instance) {
                $instance->data()->delete();
            });
            $tracker->instances()->delete();
            $tracker->items()->delete();
            $tracker->delete();
        }

        return redirect()->back()->with('success', __('interface.messages.usage_tracker_deleted'));
    }

    /**
     * Show list of usage trackers.
     *
     * @param int $id
     *
     * @return Renderable
     */
    public function contract_trackers_details(int $id): Renderable
    {
        return view('admin.contract.trackers-details', [
            'tracker' => Tracker::find($id),
        ]);
    }

    /**
     * Get list of usage trackers.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function contract_trackers_items_list(Request $request): JsonResponse
    {
        session_write_close();

        $query = TrackerItem::where('tracker_id', '=', $request->id);

        $totalCount = (clone $query)->count();

        if (! empty($request->search['value'])) {
            $query = $query->where(function (Builder $query) use ($request) {
                return $query->where('id', 'LIKE', '%' . $request->search['value'] . '%')
                    ->orWhere('type', 'LIKE', '%' . $request->search['value'] . '%')
                    ->orWhere('process', 'LIKE', '%' . $request->search['value'] . '%')
                    ->orWhere('round', 'LIKE', '%' . $request->search['value'] . '%')
                    ->orWhere('value', 'LIKE', '%' . $request->search['value'] . '%')
                    ->orWhere('amount', 'LIKE', '%' . $request->search['value'] . '%');
            });
        }

        if (! empty($request->order)) {
            foreach ($request->order as $order) {
                switch ($request->columns[$order['column']]) {
                    case 'type':
                        $orderBy = 'type';
                        break;
                    case 'process':
                        $orderBy = 'process';
                        break;
                    case 'round':
                        $orderBy = 'round';
                        break;
                    case 'value':
                        $orderBy = 'value';
                        break;
                    case 'amount':
                        $orderBy = 'amount';
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
            'draw' => (int) $request->draw,
            'recordsTotal' => $totalCount,
            'recordsFiltered' => $filteredCount,
            'data' => $query
                ->get()
                ->transform(function (TrackerItem $item) {
                    switch ($item->type) {
                        case 'integer':
                            $type = __('interface.data_type.integer');
                            break;
                        case 'double':
                            $type = __('interface.data_type.double');
                            break;
                        case 'string':
                        default:
                            $type = __('interface.data_type.string');
                            break;
                    }

                    switch ($item->process) {
                        case 'min':
                            $processType = __('interface.data_processing.minimum');
                            break;
                        case 'median':
                            $processType = __('interface.data_processing.median');
                            break;
                        case 'average':
                            $processType = __('interface.data_processing.average');
                            break;
                        case 'max':
                            $processType = __('interface.data_processing.maximum');
                            break;
                        case 'equals':
                        default:
                            $processType = __('interface.data_processing.equals');
                            break;
                    }

                    $numberSetting = '';
                    $availableOptions = '<option value="equals" selected>' . __('interface.data_processing.equals') . '</option>';

                    if ($type !== 'string') {
                        $numberSetting = '
<div class="form-group row">
    <label for="round" class="col-md-4 col-form-label text-md-right">' . __('interface.data.round_number') . '</label>

    <div class="col-md-8">
        <select id="round" type="text" class="form-control" name="round">
            <option value="up"' . ($item->round == 'up' ? ' selected' : '') . '>' . __('interface.data_processing.round_up') . '</option>
            <option value="down"' . ($item->round == 'down' ? ' selected' : '') . '>' . __('interface.data_processing.round_down') . '</option>
            <option value="regular"' . ($item->round == 'regular' ? ' selected' : '') . '>' . __('interface.data_processing.round_regular') . '</option>
            <option value="none"' . ($item->round == 'none' ? ' selected' : '') . '>' . __('interface.misc.none') . '</option>
        </select>
    </div>
</div>
';

                        $availableOptions = '
<option value="min"' . ($item->process == 'min' ? ' selected' : '') . '>' . __('interface.data_processing.minimum') . '</option>
<option value="median"' . ($item->process == 'median' ? ' selected' : '') . '>' . __('interface.data_processing.median') . '</option>
<option value="average"' . ($item->process == 'average' ? ' selected' : '') . '>' . __('interface.data_processing.average') . '</option>
<option value="max"' . ($item->process == 'max' ? ' selected' : '') . '>' . __('interface.data_processing.maximum') . '</option>
<option value="equals"' . ($item->process == 'equals' ? ' selected' : '') . '>' . __('interface.data_processing.equals') . '</option>
';
                    }

                    $edit = '
<a class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editTrackerItem' . $item->id . '"><i class="bi bi-pencil-square"></i></a>
<div class="modal fade" id="editTrackerItem' . $item->id . '" tabindex="-1" aria-labelledby="editTrackerItem' . $item->id . 'Label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title" id="editTrackerItem' . $item->id . 'Label">' . __('interface.actions.edit') . ' (#' . $item->id . ')</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="' . route('admin.contracts.trackers.items.update', ['id' => $item->tracker_id, 'item_id' => $item->id]) . '" method="post">
                <div class="modal-body">
                    <input type="hidden" name="_token" value="' . csrf_token() . '" />
                    <input type="hidden" name="item_id" value="' . $item->id . '" />
                    <div class="form-group row">
                        <label for="process" class="col-md-4 col-form-label text-md-right">' . __('interface.data.process_type') . '</label>

                        <div class="col-md-8">
                            <select id="process" type="text" class="form-control" name="process">
                                ' . $availableOptions . '
                            </select>
                        </div>
                    </div>
                    ' . $numberSetting . '
                    <div class="form-group row">
                        <label for="step" class="col-md-4 col-form-label text-md-right">' . __('interface.data.step') . '</label>

                        <div class="col-md-8">
                            <input id="step" type="text" class="form-control" name="step" value="' . $item->step . '">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="amount" class="col-md-4 col-form-label text-md-right">' . __('interface.data.amount_per_step') . '</label>

                        <div class="col-md-8">
                            <div class="input-group">
                                <input id="amount" type="number" name="amount" step="0.01" min="0.01" class="form-control" value="' . $item->amount . '">
                                <div class="input-group-append">
                                    <span class="input-group-text" id="typeSuffix">€</span>
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
                        'id' => $item->id,
                        'type' => $type,
                        'process' => $processType,
                        'round' => __(ucfirst($item->round)),
                        'step' => $item->step,
                        'amount' => number_format($item->amount, 2) . ' €',
                        'edit' => $edit,
                        'delete' => '<a href="' . route('admin.contracts.trackers.items.delete', ['id' => $item->tracker_id, 'item_id' => $item->id]) . '" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i></a>',
                    ];
                })
        ]);
    }

    /**
     * Create a new usage tracker item.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     *
     * @throws ValidationException
     */
    public function contract_trackers_items_add(Request $request): RedirectResponse
    {
        Validator::make($request->toArray(), [
            'tracker_id' => ['required', 'integer'],
            'type' => ['required', 'string'],
            'process' => ['required', 'string'],
            'round' => ['required', 'string'],
            'step' => ['required', 'string'],
            'amount' => ['required', 'numeric'],
        ])->validate();

        TrackerItem::create([
            'tracker_id' => $request->tracker_id,
            'type' => $request->type,
            'process' => $request->process,
            'round' => $request->round,
            'step' => $request->step,
            'amount' => $request->amount,
        ]);

        return redirect()->back()->with('success', __('interface.messages.usage_tracker_item_added'));
    }

    /**
     * Update an existing usage tracker item.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     *
     * @throws ValidationException
     */
    public function contract_trackers_items_update(Request $request): RedirectResponse
    {
        Validator::make($request->toArray(), [
            'item_id' => ['required', 'integer'],
            'process' => ['required', 'string'],
            'round' => ['required', 'string'],
            'step' => ['required', 'string'],
            'amount' => ['required', 'numeric'],
        ])->validate();

        if (! empty($item = TrackerItem::find($request->item_id))) {
            $item->update([
                'process' => $request->process,
                'round' => $request->round,
                'step' => $request->step,
                'amount' => $request->amount,
            ]);

            return redirect()->back()->with('success', __('interface.messages.usage_tracker_item_updated'));
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }

    /**
     * Delete an existing usage tracker item.
     *
     * @param int $id
     *
     * @return RedirectResponse
     *
     * @throws ValidationException
     */
    public function contract_trackers_items_delete(int $id): RedirectResponse
    {
        Validator::make([
            'item_id' => $id,
        ], [
            'item_id' => ['required', 'integer'],
        ])->validate();

        if (! empty($item = TrackerItem::find($id))) {
            $item->data()->delete();
            $item->delete();
        }

        return redirect()->back()->with('success', __('interface.messages.usage_tracker_item_deleted'));
    }
}
