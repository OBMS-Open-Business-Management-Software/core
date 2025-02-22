<?php

namespace App\Http\Controllers;

use App\Helpers\Download;
use App\Models\Accounting\Invoice\Invoice;
use App\Models\Accounting\Invoice\InvoiceDiscount;
use App\Models\Accounting\Invoice\InvoiceDunning;
use App\Models\Accounting\Invoice\InvoiceHistory;
use App\Models\Accounting\Invoice\InvoicePosition;
use App\Models\Accounting\Invoice\InvoiceReminder;
use App\Models\Accounting\Invoice\InvoiceType;
use App\Models\Accounting\Position;
use App\Models\Accounting\PositionDiscount;
use App\Models\FileManager\File;
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

class AdminInvoiceCustomerController extends Controller
{
    /**
     * Show list of invoices.
     *
     * @return Renderable
     */
    public function invoice_index(): Renderable
    {
        return view('admin.invoice.customer.home', [
            'types' => InvoiceType::all(),
        ]);
    }

    /**
     * Show list of invoices.
     *
     * @param int $id
     *
     * @return Renderable
     */
    public function invoice_details(int $id): Renderable
    {
        return view('admin.invoice.customer.details', [
            'invoice' => Invoice::find($id),
            'types' => InvoiceType::all(),
            'discounts' => PositionDiscount::all(),
        ]);
    }

    /**
     * Get list of invoices.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function invoice_list(Request $request): JsonResponse
    {
        session_write_close();

        $query = Invoice::query();

        if (! empty($request->user_id)) {
            $query = $query->where('user_id', '=', $request->user_id);
        }

        $totalCount = (clone $query)->count();

        if (! empty($request->search['value'])) {
            $query = $query->where(function (Builder $query) use ($request) {
                return $query->where('id', 'LIKE', '%' . $request->search['value'] . '%')
                    ->orWhere('name', 'LIKE', '%' . $request->search['value'] . '%')
                    ->orWhere('description', 'LIKE', '%' . $request->search['value'] . '%')
                    ->orWhere('type', 'LIKE', '%' . $request->search['value'] . '%')
                    ->orWhere('period', 'LIKE', '%' . $request->search['value'] . '%');
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
                ->transform(function (Invoice $invoice) {
                    switch ($invoice->status) {
                        case 'unpaid':
                            if ($invoice->overdue) {
                                $status = '<span class="badge badge-danger">' . __('interface.status.overdue') . '</span>';
                            } else {
                                $status = '<span class="badge badge-warning">' . __('interface.status.unpaid') . '</span>';
                            }
                            break;
                        case 'paid':
                            $status = '<span class="badge badge-success">' . __('interface.status.paid') . '</span>';
                            break;
                        case 'refunded':
                            $status = '<span class="badge badge-secondary">' . __('interface.status.refunded') . '</span>';
                            break;
                        case 'refund':
                            $status = '<span class="badge badge-info text-white">' . __('interface.actions.refund') . '</span>';
                            break;
                        case 'revoked':
                            $status = '<span class="badge badge-secondary">' . __('interface.status.revoked') . '</span>';
                            break;
                        case 'template':
                        default:
                            $status = '<span class="badge badge-primary">' . __('interface.status.draft') . '</span>';
                            break;
                    }

                    $types = '';

                    InvoiceType::all()->each(function (InvoiceType $type) use ($invoice, &$types) {
                        if ($invoice->type_id !== $type->id) {
                            $types .= '<option value="' . $type->id . '">' . $type->name . '</option>';
                        } else {
                            $types .= '<option value="' . $type->id . '" selected>' . $type->name . '</option>';
                        }
                    });

                    if ($invoice->status == 'template') {
                        $edit = '
<a class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editInvoice' . $invoice->id . '"><i class="bi bi-pencil-square"></i></a>
<div class="modal fade" id="editInvoice' . $invoice->id . '" tabindex="-1" aria-labelledby="editInvoice' . $invoice->id . 'Label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title" id="editInvoice' . $invoice->id . 'Label">' . __('interface.actions.edit') . ' (' . $invoice->number . ')</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="' . route('admin.invoices.customers.update', $invoice->id) . '" method="post">
                <div class="modal-body">
                    <input type="hidden" name="_token" value="' . csrf_token() . '" />
                    <input type="hidden" name="invoice_id" value="' . $invoice->id . '" />
                    <div class="form-group row">
                        <label for="user_id" class="col-md-4 col-form-label text-md-right">' . __('interface.data.user_id') . '</label>

                        <div class="col-md-8">
                            <input id="user_id" type="number" class="form-control" name="user_id" value="' . $invoice->user_id . '">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="type_id" class="col-md-4 col-form-label text-md-right">' . __('interface.data.payment_type') . '</label>

                        <div class="col-md-8">
                            <select id="type_id" class="form-control" name="type_id">
                                ' . $types . '
                            </select>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="contract_id" class="col-md-4 col-form-label text-md-right">' . __('interface.data.contract_id') . '</label>

                        <div class="col-md-8">
                            <input id="contract_id" type="number" class="form-control" name="contract_id" value="' . $invoice->contract_id . '">
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
                        'id' => $invoice->number,
                        'user' => $invoice->user->realName ?? __('interface.misc.not_available'),
                        'status' => $status,
                        'type' => $invoice->type->name ?? __('interface.misc.not_available'),
                        'date' => ! empty($invoice->archived_at) ? $invoice->archived_at->format('d.m.Y, H:i') : __('interface.misc.not_available'),
                        'due' => ! empty($invoice->archived_at) ? $invoice->archived_at->addDays($invoice->type->period)->format('d.m.Y') . ', 23:59' : __('interface.misc.not_available'),
                        'view' => '<a href="' . route('admin.invoices.customers.details', $invoice->id) . '" class="btn btn-primary btn-sm"><i class="bi bi-eye"></i></a>',
                        'edit' => $invoice->status == 'template' ? $edit : '<button type="button" class="btn btn-warning btn-sm" disabled><i class="bi bi-pencil-square"></i></button>',
                        'delete' => $invoice->status == 'template' ? '<a href="' . route('admin.invoices.customers.delete', $invoice->id) . '" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i></a>' : '<button type="button" class="btn btn-danger btn-sm" disabled><i class="bi bi-trash"></i></button>',
                    ];
                })
        ]);
    }

    /**
     * Create a new invoice.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     *
     * @throws ValidationException
     */
    public function invoice_add(Request $request): RedirectResponse
    {
        Validator::make($request->toArray(), [
            'user_id' => ['required', 'integer'],
            'type_id' => ['required', 'integer'],
            'contract_id' => ['integer', 'nullable'],
        ])->validate();

        if (
            ! empty($user = User::find($request->user_id)) &&
            $user->role == 'customer'
        ) {
            $invoice = Invoice::create([
                'user_id' => $request->user_id,
                'type_id' => $request->type_id,
                'contract_id' => $request->contract_id,
                'reverse_charge' => $user->reverseCharge,
            ]);

            return redirect()->route('admin.invoices.customers.details', $invoice->id)->with('success', __('interface.messages.invoice_added'));
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }

    /**
     * Update an existing invoice.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     *
     * @throws ValidationException
     */
    public function invoice_update(Request $request): RedirectResponse
    {
        Validator::make($request->toArray(), [
            'invoice_id' => ['required', 'integer'],
            'user_id' => ['required', 'integer'],
            'type_id' => ['required', 'integer'],
            'contract_id' => ['integer', 'nullable'],
        ])->validate();

        if (
            ! empty($invoice = Invoice::find($request->invoice_id)) &&
            $invoice->status == 'template' &&
            ! empty($user = User::find($request->user_id)) &&
            $user->role == 'customer'
        ) {
            $invoice->update([
                'user_id' => $request->user_id,
                'type_id' => $request->type_id,
                'contract_id' => $request->contract_id,
            ]);

            return redirect()->back()->with('success', __('interface.messages.invoice_updated'));
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }

    /**
     * Delete an existing invoice.
     *
     * @param int $id
     *
     * @return RedirectResponse
     *
     * @throws ValidationException
     */
    public function invoice_delete(int $id): RedirectResponse
    {
        Validator::make([
            'invoice_id' => $id,
        ], [
            'invoice_id' => ['required', 'integer'],
        ])->validate();

        if (
            ! empty($invoice = Invoice::find($id)) &&
            $invoice->status == 'template'
        ) {
            $invoice->delete();

            return redirect()->route('admin.invoices.customers')->with('success', __('interface.messages.invoice_deleted'));
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }

    /**
     * Publish an existing invoice.
     *
     * @param int $id
     *
     * @return RedirectResponse
     *
     * @throws ValidationException
     */
    public function invoice_publish(int $id): RedirectResponse
    {
        Validator::make([
            'invoice_id' => $id,
        ], [
            'invoice_id' => ['required', 'integer'],
        ])->validate();

        /* @var Invoice $invoice */
        if (
            ! empty($invoice = Invoice::find($id)) &&
            $invoice->status == 'template'
        ) {
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
                'status' => 'unpaid',
            ]);

            $invoice->sendInvoiceNotification();

            InvoiceHistory::create([
                'user_id' => Auth::id(),
                'invoice_id' => $invoice->id,
                'status' => 'publish',
            ]);

            return redirect()->back()->with('success', __('interface.messages.invoice_published'));
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }

    /**
     * Download an existing invoice.
     *
     * @param int $id
     *
     * @return void
     *
     * @throws ValidationException
     */
    public function invoice_download(int $id)
    {
        Validator::make([
            'invoice_id' => $id,
        ], [
            'invoice_id' => ['required', 'integer'],
        ])->validate();

        if (
            ! empty($invoice = Invoice::find($id)) &&
            $invoice->status !== 'template'
        ) {
            if (! empty($file = $invoice->file)) {
                $file = $file->makeVisible('data');

                Download::prepare($file->name)
                    ->data($file->data)
                    ->output();
            } else {
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

                return $pdf->download($invoice->number . '.pdf');
            }
        }
    }

    /**
     * Download an existing invoice reminder.
     *
     * @param int $id
     * @param int $reminder_id
     *
     * @return void
     *
     * @throws ValidationException
     */
    public function invoice_reminder_download(int $id, int $reminder_id)
    {
        Validator::make([
            'invoice_id' => $id,
            'reminder_id' => $reminder_id,
        ], [
            'invoice_id' => ['required', 'integer'],
            'reminder_id' => ['required', 'integer'],
        ])->validate();

        if (
            ! empty($reminder = InvoiceReminder::find($reminder_id)) &&
            ! empty($invoice = $reminder->invoice) &&
            $invoice->status !== 'template'
        ) {
            if (! empty($file = $reminder->file)) {
                $file = $file->makeVisible('data');

                Download::prepare($file->name)
                    ->data($file->data)
                    ->output();
            } else {
                try {
                    $sepaQr = ! empty($amount = round($reminder->invoice->grossSum + ($reminder->dunning->fixed_amount ?? 0) + (! empty($reminder->dunning->percentage_amount) ? $reminder->invoice->netSum * ($reminder->dunning->percentage_amount / 100) : 0), 2)) && $amount > 0 ? Data::create()
                        ->setName(config('company.bank.owner'))
                        ->setIban(str_replace(' ', '', config('company.bank.iban')))
                        ->setRemittanceText($reminder->number)
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

                $pdf = App::make('dompdf.wrapper')->loadView('pdf.reminder', [
                    'reminder' => $reminder,
                    'sepaQr' => $sepaQr,
                ]);

                return $pdf->download($reminder->number . '.pdf');
            }
        }
    }

    /**
     * Revoke an existing invoice.
     *
     * @param int $id
     *
     * @return RedirectResponse
     *
     * @throws ValidationException
     */
    public function invoice_revoke(int $id): RedirectResponse
    {
        Validator::make([
            'invoice_id' => $id,
        ], [
            'invoice_id' => ['required', 'integer'],
        ])->validate();

        if (
            ! empty($invoice = Invoice::find($id)) &&
            $invoice->status == 'unpaid'
        ) {
            $revokationInvoice = $invoice->refund('revoked');

            InvoiceHistory::create([
                'user_id' => Auth::id(),
                'invoice_id' => $invoice->id,
                'status' => 'revoke',
            ]);

            return redirect()->route('admin.invoices.customers.details', $revokationInvoice->id)->with('success', __('interface.messages.invoice_revoked'));
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }

    /**
     * Set an existing invoice to paid.
     *
     * @param int $id
     *
     * @return RedirectResponse
     *
     * @throws ValidationException
     */
    public function invoice_paid(int $id): RedirectResponse
    {
        Validator::make([
            'invoice_id' => $id,
        ], [
            'invoice_id' => ['required', 'integer'],
        ])->validate();

        if (
            ! empty($invoice = Invoice::find($id)) &&
            $invoice->status == 'unpaid'
        ) {
            $invoice->update([
                'status' => 'paid',
            ]);

            InvoiceHistory::create([
                'user_id' => Auth::id(),
                'invoice_id' => $invoice->id,
                'status' => 'pay',
            ]);

            return redirect()->back()->with('success', __('interface.messages.invoice_paid'));
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }

    /**
     * Set an existing invoice to unpaid.
     *
     * @param int $id
     *
     * @return RedirectResponse
     *
     * @throws ValidationException
     */
    public function invoice_unpaid(int $id): RedirectResponse
    {
        Validator::make([
            'invoice_id' => $id,
        ], [
            'invoice_id' => ['required', 'integer'],
        ])->validate();

        if (
            ! empty($invoice = Invoice::find($id)) &&
            $invoice->status == 'paid'
        ) {
            $invoice->update([
                'status' => 'unpaid',
            ]);

            InvoiceHistory::create([
                'user_id' => Auth::id(),
                'invoice_id' => $invoice->id,
                'status' => 'unpay',
            ]);

            return redirect()->back()->with('success', __('interface.messages.invoice_unpaid'));
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }

    /**
     * Refund an existing invoice.
     *
     * @param int $id
     *
     * @return RedirectResponse
     *
     * @throws ValidationException
     */
    public function invoice_refund(int $id): RedirectResponse
    {
        Validator::make([
            'invoice_id' => $id,
        ], [
            'invoice_id' => ['required', 'integer'],
        ])->validate();

        /* @var Invoice $invoice */
        if (
            ! empty($invoice = Invoice::find($id)) &&
            $invoice->status == 'paid'
        ) {
            $revokationInvoice = $invoice->refund('refunded');

            InvoiceHistory::create([
                'user_id' => Auth::id(),
                'invoice_id' => $invoice->id,
                'status' => 'refund',
            ]);

            return redirect()->route('admin.invoices.customers.details', $revokationInvoice->id)->with('success', __('interface.messages.invoice_refunded'));
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }

    /**
     * Set an existing invoice to unpaid.
     *
     * @param int $id
     *
     * @return RedirectResponse
     *
     * @throws ValidationException
     */
    public function invoice_resend(int $id): RedirectResponse
    {
        Validator::make([
            'invoice_id' => $id,
        ], [
            'invoice_id' => ['required', 'integer'],
        ])->validate();

        if (
            ! empty($invoice = Invoice::find($id)) &&
            $invoice->status !== 'template'
        ) {
            $invoice->sendInvoiceNotification();

            return redirect()->back()->with('success', __('interface.messages.invoice_sent'));
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }

    /**
     * Create a new invoice position.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     *
     * @throws ValidationException
     */
    public function invoice_positions_add(Request $request): RedirectResponse
    {
        Validator::make($request->toArray(), [
            'invoice_id' => ['required', 'integer'],
            'name' => ['required', 'string'],
            'description' => ['required', 'string'],
            'amount' => ['required', 'numeric'],
            'vat_percentage' => ['nullable', 'numeric'],
            'quantity' => ['required', 'numeric'],
            'discount_id' => ['nullable', 'integer'],
        ])->validate();

        if (! empty($request->service_runtime)) {
            Validator::make($request->toArray(), [
                'started_at' => ['required', 'string'],
                'ended_at' => ['required', 'string'],
            ])->validate();
        }

        /* @var Invoice $invoice */
        if (
            ! empty($invoice = Invoice::find($request->invoice_id)) &&
            $invoice->status == 'template'
        ) {
            $position = Position::create([
                'discount_id' => ! empty($request->discount_id) ? $request->discount_id : null,
                'name' => $request->name,
                'description' => $request->description,
                'amount' => $request->amount,
                'vat_percentage' => ! empty($request->vat_percentage) ? $request->vat_percentage : 0,
                'quantity' => $request->quantity,
            ]);

            InvoicePosition::create([
                'invoice_id' => $request->invoice_id,
                'position_id' => $position->id,
                'started_at' => ! empty($request->service_runtime) ? Carbon::parse($request->started_at) : null,
                'ended_at' => ! empty($request->service_runtime) ? Carbon::parse($request->ended_at) : null,
            ]);

            return redirect()->back()->with('success', __('interface.messages.invoice_position_added'));
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }

    /**
     * Update an existing invoice position.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     *
     * @throws ValidationException
     */
    public function invoice_positions_update(Request $request): RedirectResponse
    {
        Validator::make($request->toArray(), [
            'position_id' => ['required', 'integer'],
            'name' => ['required', 'string'],
            'description' => ['required', 'string'],
            'amount' => ['required', 'numeric'],
            'vat_percentage' => ['nullable', 'numeric'],
            'quantity' => ['required', 'numeric'],
            'discount_id' => ['nullable', 'integer'],
        ])->validate();

        if (! empty($request->service_runtime)) {
            Validator::make($request->toArray(), [
                'started_at' => ['required', 'string'],
                'ended_at' => ['required', 'string'],
            ])->validate();
        }

        /* @var InvoicePosition $position */
        if (
            ! empty($position = InvoicePosition::find($request->position_id)) &&
            $position->invoice->status == 'template'
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

            return redirect()->back()->with('success', __('interface.messages.invoice_position_updated'));
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }

    /**
     * Delete an existing invoice position.
     *
     * @param int $invoice_id
     * @param int $id
     *
     * @return RedirectResponse
     *
     * @throws ValidationException
     */
    public function invoice_positions_delete(int $invoice_id, int $id): RedirectResponse
    {
        Validator::make([
            'invoice_id' => $invoice_id,
            'position_id' => $id,
        ], [
            'invoice_id' => ['required', 'integer'],
            'position_id' => ['required', 'integer'],
        ])->validate();

        /* @var InvoicePosition $position */
        if (
            ! empty($position = InvoicePosition::find($id)) &&
            $position->invoice->status == 'template'
        ) {
            $position->position->delete();
            $position->delete();

            return redirect()->back()->with('success', __('interface.messages.invoice_position_deleted'));
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }

    /**
     * Get list of invoice history entries.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function invoice_history(Request $request): JsonResponse
    {
        $query = InvoiceHistory::where('invoice_id', '=', $request->id);

        $totalCount = (clone $query)->count();

        if (! empty($request->search['value'])) {
            $query = $query->where(function (Builder $query) use ($request) {
                return $query->where('id', 'LIKE', '%' . $request->search['value'] . '%')
                    ->orWhere('created_at', 'LIKE', '%' . $request->search['value'] . '%')
                    ->orWhere('status', 'LIKE', '%' . $request->search['value'] . '%');
            });
        }

        if (! empty($request->order)) {
            foreach ($request->order as $order) {
                switch ($request->columns[$order['column']]) {
                    case 'status':
                        $orderBy = 'status';
                        break;
                    case 'date':
                        $orderBy = 'created_at';
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
                ->transform(function (InvoiceHistory $history) {
                    switch ($history->status) {
                        case 'publish':
                            $status = '<span class="badge badge-success">' . __('interface.status.published') . '</span>';
                            break;
                        case 'revoke':
                            $status = '<span class="badge badge-secondary">' . __('interface.status.revoked') . '</span>';
                            break;
                        case 'refund':
                            $status = '<span class="badge badge-info text-white">' . __('interface.actions.refund') . '</span>';
                            break;
                        case 'unpay':
                            $status = '<span class="badge badge-warning">' . __('interface.status.unpaid') . '</span>';
                            break;
                        case 'pay':
                            $status = '<span class="badge badge-success">' . __('interface.status.paid') . '</span>';
                            break;
                        default:
                            $status = '<span class="badge badge-secondary">' . __('interface.status.unknown') . '</span>';
                            break;
                    }

                    return (object) [
                        'id' => $history->id,
                        'date' => $history->created_at->format('d.m.Y, H:i'),
                        'name' => ! empty($history->user) && ! empty($history->user->realName) ? '<i class="bi bi-person mr-2"></i> ' . $history->user->realName : '<i class="bi bi-robot mr-2""></i> ' . __('interface.data.system'),
                        'status' => $status,
                    ];
                })
        ]);
    }

    /**
     * Show list of invoice types.
     *
     * @return Renderable
     */
    public function invoice_types_index(): Renderable
    {
        return view('admin.invoice.types', [
            'discounts' => InvoiceDiscount::all(),
        ]);
    }

    /**
     * Get list of invoice types.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function invoice_types_list(Request $request): JsonResponse
    {
        session_write_close();

        $query = InvoiceType::query();

        $totalCount = (clone $query)->count();

        if (! empty($request->search['value'])) {
            $query = $query->where(function (Builder $query) use ($request) {
                return $query->where('id', 'LIKE', '%' . $request->search['value'] . '%')
                    ->orWhere('name', 'LIKE', '%' . $request->search['value'] . '%')
                    ->orWhere('description', 'LIKE', '%' . $request->search['value'] . '%')
                    ->orWhere('type', 'LIKE', '%' . $request->search['value'] . '%')
                    ->orWhere('period', 'LIKE', '%' . $request->search['value'] . '%');
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

        $discounts = InvoiceDiscount::all();

        return response()->json([
            'draw' => (int) $request->draw,
            'recordsTotal' => $totalCount,
            'recordsFiltered' => $filteredCount,
            'data' => $query
                ->get()
                ->transform(function (InvoiceType $type) use ($discounts) {
                    switch ($type->type) {
                        case 'prepaid':
                            $receiptType = __('interface.misc.prepaid_receipt');
                            break;
                        case 'auto_revoke':
                            $receiptType = __('interface.misc.auto_revoked_invoice');
                            break;
                        case 'normal':
                        default:
                            $receiptType = __('interface.misc.invoice');
                            break;
                    }

                    $discount = '';

                    $discounts->each(function (InvoiceDiscount $discounts) use ($type, &$discount) {
                        $discount .= '<option value="' . $discounts->id . '"' . ($type->discount_id == $discounts->id ? ' selected' : '') . '>' . __($discounts->name) . '</option>';
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
            <form action="' . route('admin.invoices.types.update', $type->id) . '" method="post">
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
                                <option value="normal"' . ($type->type == 'normal' ? ' selected' : '') . '>' . __('interface.misc.basic') . '</option>
                                <option value="auto_revoke"' . ($type->type == 'auto_revoke' ? ' selected' : '') . '>' . __('interface.misc.autorevoke_overdue') . '</option>
                                <option value="prepaid"' . ($type->type == 'prepaid' ? ' selected' : '') . '>' . __('interface.misc.prepaid_receipt') . '</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="period" class="col-md-4 col-form-label text-md-right">' . __('interface.data.payment_period') . '</label>

                        <div class="col-md-8">
                            <div class="input-group">
                                <input id="period" type="number" step="0.01" min="0.01" class="form-control trigger-dunning" name="period" value="' . $type->period . '">
                                <div class="input-group-append">
                                    <span class="input-group-text" id="basic-addon2">' . __('interface.units.days') . '</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="hiddenDunning' . $type->id . '"' . ($type->type !== 'normal' ? ' style="display: none"' : '') . '>
                        <div class="form-group row align-items-center">
                            <label for="dunning" class="col-md-4 col-form-label text-md-right">' . __('interface.actions.enable_dunning') . '</label>

                            <div class="col-md-8">
                                <input id="dunning' . $type->id . '" type="checkbox" class="form-control" name="dunning" value="true" ' . ($type->dunning ? ' checked' : '') . '>
                            </div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="discount_id" class="col-md-4 col-form-label text-md-right">' . __('interface.data.discount') . '</label>

                        <div class="col-md-8">
                            <select id="discount_id" type="text" class="form-control" name="discount_id">
                                <option value=""' . (empty($type->id) ? ' selected' : '') . '>' . __('interface.misc.none') . '</option>
                                ' . $discount . '
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
                        'id' => $type->id,
                        'name' => $type->name,
                        'description' => $type->description,
                        'period' => $type->period . ' ' . __('interface.units.days'),
                        'type' => $receiptType,
                        'dunning' => $type->dunning ? '<span class="badge badge-success">' . __('interface.status.enabled') . '</span>' : '<span class="badge badge-warning">' . __('interface.status.disabled') . '</span>',
                        'view' => '<a href="' . route('admin.invoices.types.details', $type->id) . '" class="btn btn-primary btn-sm"><i class="bi bi-eye"></i></a>',
                        'edit' => $edit,
                        'delete' => '<a href="' . route('admin.invoices.types.delete', $type->id) . '" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i></a>',
                    ];
                })
        ]);
    }

    /**
     * Show invoice type details.
     *
     * @param int $id
     *
     * @return Renderable
     */
    public function invoice_types_details(int $id): Renderable
    {
        return view('admin.invoice.types-details', [
            'type' => InvoiceType::find($id),
            'discounts' => InvoiceDiscount::all(),
        ]);
    }

    /**
     * Create a new invoice type.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     *
     * @throws ValidationException
     */
    public function invoice_types_add(Request $request): RedirectResponse
    {
        Validator::make($request->toArray(), [
            'name' => ['required', 'string'],
            'description' => ['required', 'string'],
            'type' => ['required', 'string'],
            'period' => ['required', 'integer'],
            'dunning' => ['string', 'nullable'],
            'discount_id' => ['nullable', 'integer'],
        ])->validate();

        InvoiceType::create([
            'name' => $request->name,
            'description' => $request->description,
            'type' => $request->type,
            'period' => $request->period,
            'dunning' => $request->type == 'normal' && isset($request->dunning) && $request->dunning == 'true',
            'discount_id' => $request->discount_id ?? null,
        ]);

        return redirect()->back()->with('success', __('interface.messages.invoice_type_added'));
    }

    /**
     * Update an existing invoice type.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     *
     * @throws ValidationException
     */
    public function invoice_types_update(Request $request): RedirectResponse
    {
        Validator::make($request->toArray(), [
            'type_id' => ['required', 'integer'],
            'name' => ['required', 'string'],
            'description' => ['required', 'string'],
            'type' => ['required', 'string'],
            'period' => ['required', 'integer'],
            'dunning' => ['string', 'nullable'],
            'discount_id' => ['nullable', 'integer'],
        ])->validate();

        if (! empty($type = InvoiceType::find($request->type_id))) {
            $type->update([
                'name' => $request->name,
                'description' => $request->description,
                'type' => $request->type,
                'period' => $request->period,
                'dunning' => $request->type == 'normal' && isset($request->dunning) && $request->dunning == 'true',
                'discount_id' => $request->discount_id ?? null,
            ]);

            return redirect()->back()->with('success', __('interface.messages.invoice_type_updated'));
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }

    /**
     * Delete an existing invoice type.
     *
     * @param int $id
     *
     * @return RedirectResponse
     *
     * @throws ValidationException
     */
    public function invoice_types_delete(int $id): RedirectResponse
    {
        Validator::make([
            'type_id' => $id,
        ], [
            'type_id' => ['required', 'integer'],
        ])->validate();

        if (! empty($type = InvoiceType::find($id))) {
            $type->dunnings()->delete();
            $type->delete();
        }

        return redirect()->back()->with('success', __('interface.messages.invoice_type_deleted'));
    }

    /**
     * Get list of invoice dunning types.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function invoice_dunning_list(Request $request): JsonResponse
    {
        session_write_close();

        $query = InvoiceDunning::where('type_id', '=', $request->id);

        $totalCount = (clone $query)->count();

        if (! empty($request->search['value'])) {
            $query = $query->where(function (Builder $query) use ($request) {
                return $query->where('id', 'LIKE', '%' . $request->search['value'] . '%')
                    ->orWhere('after', 'LIKE', '%' . $request->search['value'] . '%')
                    ->orWhere('fixed_amount', 'LIKE', '%' . $request->search['value'] . '%')
                    ->orWhere('percentage_amount', 'LIKE', '%' . $request->search['value'] . '%');
            });
        }

        if (! empty($request->order)) {
            foreach ($request->order as $order) {
                switch ($request->columns[$order['column']]) {
                    case 'after':
                        $orderBy = 'after';
                        break;
                    case 'fees':
                        $orderBy = 'fixed_amount';
                        break;
                    case 'interest':
                        $orderBy = 'percentage_amount';
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
                ->transform(function (InvoiceDunning $type) {
                    $edit = '
<a class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editPaymentType' . $type->id . '"><i class="bi bi-pencil-square"></i></a>
<div class="modal fade" id="editPaymentType' . $type->id . '" tabindex="-1" aria-labelledby="editPaymentType' . $type->id . 'Label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title" id="editPaymentType' . $type->id . 'Label">' . __('interface.actions.edit') . ' (#' . $type->id . ')</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="' . route('admin.invoices.dunning.update', ['id' => $type->type_id, 'dunning_id' => $type->id]) . '" method="post">
                <div class="modal-body">
                    <input type="hidden" name="_token" value="' . csrf_token() . '" />
                    <input type="hidden" name="dunning_id" value="' . $type->id . '" />
                    <div class="form-group row">
                        <label for="after" class="col-md-4 col-form-label text-md-right">' . __('interface.data.after') . '</label>

                        <div class="col-md-8">
                            <div class="input-group">
                                <input id="after" type="number" step="0.01" min="0.01" class="form-control" name="after" value="' . $type->after . '">
                                <div class="input-group-append">
                                    <span class="input-group-text" id="basic-addon2">' . __('interface.units.days') . '</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="period" class="col-md-4 col-form-label text-md-right">' . __('interface.data.payment_period') . '</label>

                        <div class="col-md-8">
                            <div class="input-group">
                                <input id="period" type="number" step="0.01" min="0.01" class="form-control" name="period" value="' . $type->period . '">
                                <div class="input-group-append">
                                    <span class="input-group-text" id="basic-addon2">' . __('interface.units.days') . '</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="fixed_amount" class="col-md-4 col-form-label text-md-right">' . __('interface.data.fees') . '</label>

                        <div class="col-md-8">
                            <div class="input-group">
                                <input id="fixed_amount" type="number" step="0.01" min="0.01" class="form-control" name="fixed_amount" value="' . $type->fixed_amount . '">
                                <div class="input-group-append">
                                    <span class="input-group-text" id="basic-addon2">€</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="percentage_amount" class="col-md-4 col-form-label text-md-right">' . __('interface.data.interest_charges') . '</label>

                        <div class="col-md-8">
                            <div class="input-group">
                                <input id="percentage_amount" type="number" step="0.01" min="0.01" class="form-control" name="percentage_amount" value="' . $type->percentage_amount . '">
                                <div class="input-group-append">
                                    <span class="input-group-text" id="basic-addon2">%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group row align-items-center">
                        <label for="cancel_contract_regular" class="col-md-4 col-form-label text-md-right">' . __('interface.actions.cancel_regularly') . '</label>

                        <div class="col-md-8">
                            <input id="cancel_contract_regular" type="checkbox" class="form-control" name="cancel_contract_regular" value="true"' . ($type->cancel_contract_regular ? ' checked' : '') . '>
                        </div>
                    </div>
                    <div class="form-group row align-items-center">
                        <label for="cancel_contract_instant" class="col-md-4 col-form-label text-md-right">' . __('interface.actions.cancel_instantly') . '</label>

                        <div class="col-md-8">
                            <input id="cancel_contract_instant" type="checkbox" class="form-control" name="cancel_contract_instant" value="true"' . ($type->cancel_contract_instant ? ' checked' : '') . '>
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
                        'after' => $type->after . ' ' . __('interface.units.days'),
                        'period' => $type->period . ' ' . __('interface.units.days'),
                        'fees' => $type->fixed_amount . ' €',
                        'interest' => $type->percentage_amount . ' %',
                        'edit' => $edit,
                        'delete' => '<a href="' . route('admin.invoices.dunning.delete', ['id' => $type->type_id, 'dunning_id' => $type->id]) . '" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i></a>',
                    ];
                })
        ]);
    }

    /**
     * Create a new invoice dunning type.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     *
     * @throws ValidationException
     */
    public function invoice_dunning_add(Request $request): RedirectResponse
    {
        Validator::make($request->toArray(), [
            'type_id' => ['required', 'integer'],
            'after' => ['required', 'numeric'],
            'period' => ['required', 'numeric'],
            'fixed_amount' => ['numeric', 'nullable'],
            'percentage_amount' => ['numeric', 'nullable'],
            'cancel_contract_regular' => ['string', 'nullable'],
            'cancel_contract_instant' => ['string', 'nullable'],
        ])->validate();

        if (! empty($type = InvoiceType::find($request->type_id))) {
            InvoiceDunning::create([
                'type_id' => $type->id,
                'after' => $request->after,
                'period' => $request->period,
                'fixed_amount' => $request->fixed_amount,
                'percentage_amount' => $request->percentage_amount,
                'cancel_contract_regular' => ! empty($request->cancel_contract_regular),
                'cancel_contract_instant' => ! empty($request->cancel_contract_instant),
            ]);

            return redirect()->back()->with('success', __('interface.messages.payment_type_dunning_added'));
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }

    /**
     * Update an existing invoice dunning type.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     *
     * @throws ValidationException
     */
    public function invoice_dunning_update(Request $request): RedirectResponse
    {
        Validator::make($request->toArray(), [
            'dunning_id' => ['required', 'integer'],
            'after' => ['required', 'numeric'],
            'period' => ['required', 'numeric'],
            'fixed_amount' => ['numeric', 'nullable'],
            'percentage_amount' => ['numeric', 'nullable'],
            'cancel_contract_regular' => ['string', 'nullable'],
            'cancel_contract_instant' => ['string', 'nullable'],
        ])->validate();

        if (! empty($dunning = InvoiceDunning::find($request->dunning_id))) {
            $dunning->update([
                'after' => $request->after,
                'period' => $request->period,
                'fixed_amount' => $request->fixed_amount,
                'percentage_amount' => $request->percentage_amount,
                'cancel_contract_regular' => ! empty($request->cancel_contract_regular),
                'cancel_contract_instant' => ! empty($request->cancel_contract_instant),
            ]);

            return redirect()->back()->with('success', __('interface.messages.payment_type_dunning_updated'));
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }

    /**
     * Delete an existing invoice dunning type.
     *
     * @param int $id
     * @param int $type_id
     *
     * @return RedirectResponse
     *
     * @throws ValidationException
     */
    public function invoice_dunning_delete(int $id, int $type_id): RedirectResponse
    {
        Validator::make([
            'id' => $id,
            'type_id' => $type_id,
        ], [
            'id' => ['required', 'integer'],
            'type_id' => ['required', 'integer'],
        ])->validate();

        if (! empty($dunning = InvoiceDunning::find($type_id))) {
            $dunning->delete();

            return redirect()->back()->with('success', __('interface.messages.payment_type_dunning_deleted'));
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }

    /**
     * Show list of invoice types.
     *
     * @return Renderable
     */
    public function discount_index(): Renderable
    {
        return view('admin.discounts');
    }

    /**
     * Get list of invoice types.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function discount_list(Request $request): JsonResponse
    {
        session_write_close();

        $query = PositionDiscount::query();

        $totalCount = (clone $query)->count();

        if (! empty($request->search['value'])) {
            $query = $query->where(function (Builder $query) use ($request) {
                return $query->where('id', 'LIKE', '%' . $request->search['value'] . '%')
                    ->orWhere('name', 'LIKE', '%' . $request->search['value'] . '%')
                    ->orWhere('description', 'LIKE', '%' . $request->search['value'] . '%')
                    ->orWhere('type', 'LIKE', '%' . $request->search['value'] . '%')
                    ->orWhere('amount', 'LIKE', '%' . $request->search['value'] . '%');
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
                    case 'type':
                        $orderBy = 'type';
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
                ->transform(function (PositionDiscount $type) {
                    switch ($type->type) {
                        case 'percentage':
                            $typeString = __('interface.units.percentage');
                            $typeSuffix = '%';
                            break;
                        case 'fixed':
                        default:
                            $typeString = __('interface.units.fixed');
                            $typeSuffix = '€';
                            break;
                    }

                    $edit = '
<a class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editDiscount' . $type->id . '"><i class="bi bi-pencil-square"></i></a>
<div class="modal fade" id="editDiscount' . $type->id . '" tabindex="-1" aria-labelledby="editDiscount' . $type->id . 'Label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title" id="editDiscount' . $type->id . 'Label">' . __('interface.actions.edit') . ' (' . $type->name . ')</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="' . route('admin.discounts.update', ['id' => $type->id]) . '" method="post">
                <div class="modal-body">
                    <input type="hidden" name="_token" value="' . csrf_token() . '" />
                    <input type="hidden" name="discount_id" value="' . $type->id . '" />
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
                        <label for="type_dynamic' . $type->id . '" class="col-md-4 col-form-label text-md-right">' . __('interface.data.type') . '</label>

                        <div class="col-md-8">
                            <select id="type_dynamic' . $type->id . '" type="text" class="form-control type_dynamic" data-id="' . $type->id . '" name="type">
                                <option value="fixed"' . ($type->type == 'fixed' ? ' selected' : '') . '>' . __('interface.units.fixed') . '</option>
                                <option value="percentage"' . ($type->type == 'percentage' ? ' selected' : '') . '>' . __('interface.units.percentage') . '</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="amount" class="col-md-4 col-form-label text-md-right">' . __('interface.data.amount') . '</label>

                        <div class="col-md-8">
                            <div class="input-group">
                                <input id="amount" type="number" step="0.01" min="0.01" class="form-control" name="amount" value="' . $type->amount . '">
                                <div class="input-group-append">
                                    <span class="input-group-text" id="typeSuffixDynamic' . $type->id . '">' . $typeSuffix . '</span>
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
                        'type' => $typeString,
                        'amount' => $type->amount . ' ' . $typeSuffix,
                        'edit' => empty($discount->positions) ? $edit : '<button type="button" class="btn btn-warning btn-sm"><i class="bi bi-pencil-square"></i></button>',
                        'delete' => empty($discount->positions) ? '<a href="' . route('admin.discounts.delete', $type->id) . '" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i></a>' : '<button type="button" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i></button>',
                    ];
                })
        ]);
    }

    /**
     * Create a new discount.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     *
     * @throws ValidationException
     */
    public function discount_add(Request $request): RedirectResponse
    {
        Validator::make($request->toArray(), [
            'name' => ['required', 'string'],
            'description' => ['required', 'string'],
            'type' => ['required', 'string'],
            'amount' => ['required', 'numeric'],
        ])->validate();

        PositionDiscount::create([
            'name' => $request->name,
            'description' => $request->description,
            'type' => $request->type,
            'amount' => $request->amount,
        ]);

        return redirect()->back()->with('success', __('interface.messages.discount_added'));
    }

    /**
     * Update an existing discount.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     *
     * @throws ValidationException
     */
    public function discount_update(Request $request): RedirectResponse
    {
        Validator::make($request->toArray(), [
            'discount_id' => ['required', 'integer'],
        ])->validate();

        /* @var PositionDiscount $discount */
        if (
            ! empty($discount = PositionDiscount::find($request->discount_id)) &&
            $discount->positions->isEmpty()
        ) {
            $discount->update([
                'name' => $request->name,
                'description' => $request->description,
                'type' => $request->type,
                'amount' => $request->amount,
            ]);

            return redirect()->back()->with('success', __('interface.messages.discount_updated'));
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }

    /**
     * Delete an existing discount.
     *
     * @param int $id
     *
     * @return RedirectResponse
     *
     * @throws ValidationException
     */
    public function discount_delete(int $id): RedirectResponse
    {
        Validator::make([
            'discount_id' => $id,
        ], [
            'discount_id' => ['required', 'integer'],
        ])->validate();

        /* @var PositionDiscount $discount */
        if (
            ! empty($discount = PositionDiscount::find($id)) &&
            $discount->positions->isEmpty()
        ) {
            $discount->delete();

            return redirect()->back()->with('success', __('interface.messages.discount_deleted'));
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }

    /**
     * Show list of invoice types.
     *
     * @return Renderable
     */
    public function invoice_discounts_index(): Renderable
    {
        return view('admin.invoice.discounts');
    }

    /**
     * Get list of invoice types.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function invoice_discounts_list(Request $request): JsonResponse
    {
        session_write_close();

        $query = InvoiceDiscount::query();

        $totalCount = (clone $query)->count();

        if (! empty($request->search['value'])) {
            $query = $query->where(function (Builder $query) use ($request) {
                return $query->where('id', 'LIKE', '%' . $request->search['value'] . '%')
                    ->orWhere('name', 'LIKE', '%' . $request->search['value'] . '%')
                    ->orWhere('description', 'LIKE', '%' . $request->search['value'] . '%')
                    ->orWhere('percentage_amount', 'LIKE', '%' . $request->search['value'] . '%')
                    ->orWhere('period', 'LIKE', '%' . $request->search['value'] . '%');
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
                    case 'period':
                        $orderBy = 'period';
                        break;
                    case 'percentage_amount':
                        $orderBy = 'percentage_amount';
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
                ->transform(function (InvoiceDiscount $discount) {
                    $edit = '
<a class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editPaymentType' . $discount->id . '"><i class="bi bi-pencil-square"></i></a>
<div class="modal fade" id="editPaymentType' . $discount->id . '" tabindex="-1" aria-labelledby="editPaymentType' . $discount->id . 'Label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title" id="editPaymentType' . $discount->id . 'Label">' . __('interface.actions.edit') . ' (' . $discount->name . ')</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="' . route('admin.invoices.discounts.update', $discount->id) . '" method="post">
                <div class="modal-body">
                    <input type="hidden" name="_token" value="' . csrf_token() . '" />
                    <input type="hidden" name="discount_id" value="' . $discount->id . '" />
                    <div class="form-group row">
                        <label for="name" class="col-md-4 col-form-label text-md-right">' . __('interface.data.name') . '</label>

                        <div class="col-md-8">
                            <input id="name" type="text" class="form-control" name="name" value="' . $discount->name . '">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="description" class="col-md-4 col-form-label text-md-right">' . __('interface.data.description') . '</label>

                        <div class="col-md-8">
                            <input id="description" type="text" class="form-control" name="description" value="' . $discount->description . '">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="period" class="col-md-4 col-form-label text-md-right">' . __('interface.data.discount_period') . '</label>

                        <div class="col-md-8">
                            <div class="input-group">
                                <input id="period" type="number" step="0.01" min="0.01" class="form-control trigger-dunning" name="period" value="' . $discount->period . '">
                                <div class="input-group-append">
                                    <span class="input-group-text" id="basic-addon2">' . __('interface.units.days') . '</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="percentage_amount" class="col-md-4 col-form-label text-md-right">' . __('interface.data.discount_percentage') . '</label>

                        <div class="col-md-8">
                            <div class="input-group">
                                <input id="percentage_amount" type="number" step="0.01" min="0.01" class="form-control" name="percentage_amount" value="' . $discount->percentage_amount . '">
                                <div class="input-group-append">
                                    <span class="input-group-text" id="basic-addon2">%</span>
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
                        'id' => $discount->id,
                        'name' => $discount->name,
                        'description' => $discount->description,
                        'period' => $discount->period . ' ' . __('interface.units.days'),
                        'percentage_amount' => $discount->percentage_amount . ' %',
                        'edit' => $edit,
                        'delete' => '<a href="' . route('admin.invoices.discounts.delete', $discount->id) . '" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i></a>',
                    ];
                })
        ]);
    }

    /**
     * Create a new invoice type.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     *
     * @throws ValidationException
     */
    public function invoice_discounts_add(Request $request): RedirectResponse
    {
        Validator::make($request->toArray(), [
            'name' => ['required', 'string'],
            'description' => ['required', 'string'],
            'period' => ['required', 'integer'],
            'percentage_amount' => ['required', 'numeric'],
        ])->validate();

        InvoiceDiscount::create([
            'name' => $request->name,
            'description' => $request->description,
            'period' => $request->period,
            'percentage_amount' => $request->percentage_amount,
        ]);

        return redirect()->back()->with('success', __('interface.messages.invoice_discount_added'));
    }

    /**
     * Update an existing invoice type.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     *
     * @throws ValidationException
     */
    public function invoice_discounts_update(Request $request): RedirectResponse
    {
        Validator::make($request->toArray(), [
            'discount_id' => ['required', 'integer'],
            'name' => ['required', 'string'],
            'description' => ['required', 'string'],
            'period' => ['required', 'integer'],
            'percentage_amount' => ['required', 'numeric'],
        ])->validate();

        if (! empty($discount = InvoiceDiscount::find($request->discount_id))) {
            $discount->update([
                'name' => $request->name,
                'description' => $request->description,
                'period' => $request->period,
                'percentage_amount' => $request->percentage_amount,
            ]);

            return redirect()->back()->with('success', __('interface.messages.invoice_discount_updated'));
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }

    /**
     * Delete an existing invoice discount.
     *
     * @param int $id
     *
     * @return RedirectResponse
     *
     * @throws ValidationException
     */
    public function invoice_discounts_delete(int $id): RedirectResponse
    {
        Validator::make([
            'discount_id' => $id,
        ], [
            'discount_id' => ['required', 'integer'],
        ])->validate();

        if (! empty($discount = InvoiceDiscount::find($id))) {
            $discount->delete();
        }

        return redirect()->back()->with('success', __('interface.messages.invoice_discount_deleted'));
    }
}
