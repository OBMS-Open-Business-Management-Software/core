<?php

namespace App\Http\Controllers;

use App\Models\Accounting\Contract\Contract;
use App\Models\Accounting\Contract\ContractPosition;
use App\Models\Accounting\Invoice\Invoice;
use App\Models\Accounting\Invoice\InvoiceHistory;
use App\Models\Accounting\Invoice\InvoicePosition;
use App\Models\Accounting\Position;
use App\Models\Accounting\Prepaid\PrepaidHistory;
use App\Models\FileManager\File;
use Carbon\Carbon;
use Endroid\QrCode\ErrorCorrectionLevel;
use Exception;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use SepaQr\Data;

class CustomerContractController extends Controller
{
    /**
     * Show list of contracts.
     *
     * @return Renderable
     */
    public function contract_index(): Renderable
    {
        return view('customer.contract.home');
    }

    /**
     * Show list of contracts.
     *
     * @param int $id
     *
     * @return Renderable|RedirectResponse
     */
    public function contract_details(int $id)
    {
        if (
            ! empty($contract = Contract::find($id)) &&
            ! empty($contract->last_invoice_at)
        ) {
            return view('customer.contract.details', [
                'contract' => $contract,
            ]);
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }

    /**
     * Get list of contracts.
     *
     * @param Request $request
     */
    public function contract_list(Request $request): void
    {
        session_write_close();

        $query = Contract::where('user_id', '=', Auth::id())
            ->whereNotNull('last_invoice_at');

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

        header('Content-type: application/json');
        echo json_encode([
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

                    return (object) [
                        'id' => $contract->number,
                        'status' => $status,
                        'type' => $contract->type->name ?? __('interface.misc.not_available'),
                        'view' => '<a href="' . route('customer.contracts.details', $contract->id) . '" class="btn btn-primary btn-sm"><i class="bi bi-eye"></i></a>',
                    ];
                })
        ]);
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
            ! empty($contract->last_invoice_at) &&
            $contract->status == 'expires' &&
            $contract->user_id == Auth::id() &&
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
            ! empty($contract->last_invoice_at) &&
            $contract->status == 'started' &&
            $contract->user_id == Auth::id()
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
            ! empty($contract->last_invoice_at) &&
            $contract->status == 'expires' &&
            $contract->type->type !== 'prepaid_manual' &&
            $contract->user_id == Auth::id()
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
}
