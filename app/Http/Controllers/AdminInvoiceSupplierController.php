<?php

namespace App\Http\Controllers;

use App\Helpers\Download;
use App\Models\Accounting\Invoice\Invoice;
use App\Models\Accounting\Invoice\InvoiceHistory;
use App\Models\Accounting\Invoice\InvoiceImporter;
use App\Models\Accounting\Invoice\InvoiceImporterHistory;
use App\Models\Accounting\Invoice\InvoicePosition;
use App\Models\Accounting\Invoice\InvoiceType;
use App\Models\Accounting\Position;
use App\Models\Accounting\PositionDiscount;
use App\Models\Accounting\Prepaid\PrepaidHistory;
use App\Models\FileManager\File;
use App\Models\ImapInbox;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AdminInvoiceSupplierController extends Controller
{
    /**
     * Show list of invoices.
     *
     * @return Renderable
     */
    public function invoice_index(): Renderable
    {
        return view('admin.invoice.supplier.home', [
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
        return view('admin.invoice.supplier.details', [
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
            <form action="' . route('admin.invoices.suppliers.update', $invoice->id) . '" method="post">
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
                        'view' => '<a href="' . route('admin.invoices.suppliers.details', $invoice->id) . '" class="btn btn-primary btn-sm"><i class="bi bi-eye"></i></a>',
                        'edit' => $invoice->status == 'template' ? $edit : '<button type="button" class="btn btn-warning btn-sm" disabled><i class="bi bi-pencil-square"></i></button>',
                        'delete' => $invoice->status == 'template' ? '<a href="' . route('admin.invoices.suppliers.delete', $invoice->id) . '" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i></a>' : '<button type="button" class="btn btn-danger btn-sm" disabled><i class="bi bi-trash"></i></button>',
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
            'name' => ['required', 'string'],
            'reverse_charge' => ['string', 'nullable'],
        ])->validate();

        /* @var UploadedFile|null $file */
        if (empty($file = $request->files->get('file'))) {
            return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
        }

        if (
            ! empty($user = User::find($request->user_id)) &&
            $user->role == 'supplier'
        ) {
            $file = File::create([
                'user_id' => ! empty($request->private) ? Auth::id() : null,
                'folder_id' => null,
                'name' =>  $file->getClientOriginalName(),
                'data' => $file->getContent(),
                'mime' => $file->getClientMimeType(),
                'size' => $file->getSize(),
            ]);

            if ($file instanceof File) {
                $invoice = Invoice::create([
                    'user_id' => $request->user_id,
                    'type_id' => $request->type_id,
                    'reverse_charge' => (bool) $request->reverse_charge,
                    'archived' => true,
                    'file_id' => $file->id,
                    'name' => $request->name,
                ]);

                return redirect()->route('admin.invoices.suppliers.details', $invoice->id)->with('success', __('interface.messages.invoice_added'));
            }
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
            'name' => ['required', 'string'],
        ])->validate();

        if (
            ! empty($invoice = Invoice::find($request->invoice_id)) &&
            $invoice->status == 'template' &&
            ! empty($user = User::find($request->user_id)) &&
            $user->role == 'supplier'
        ) {
            $invoice->update([
                'name' => $request->name,
                'user_id' => $request->user_id,
                'type_id' => $request->type_id,
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

            return redirect()->route('admin.invoices.suppliers')->with('success', __('interface.messages.invoice_deleted'));
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
                'status' => 'unpaid',
            ]);

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
            ! empty($file = $invoice->file)
        ) {
            $file = $file->makeVisible('data');

            Download::prepare($file->name)
                ->data($file->data)
                ->output();
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
            $invoice->update([
                'status' => 'revoked',
            ]);

            InvoiceHistory::create([
                'user_id' => Auth::id(),
                'invoice_id' => $invoice->id,
                'status' => 'revoke',
            ]);

            return redirect()->back()->with('success', __('interface.messages.invoice_revoked'));
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

            PrepaidHistory::create([
                'user_id' => $invoice->user_id,
                'invoice_id' => $invoice->id,
                'amount' => $invoice->grossSumDiscounted,
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

            PrepaidHistory::create([
                'user_id' => $invoice->user_id,
                'invoice_id' => $invoice->id,
                'amount' => $invoice->grossSumDiscounted * (-1),
            ]);

            return redirect()->back()->with('success', __('interface.messages.invoice_unpaid'));
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }

    /**
     * Refund an existing invoice.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     *
     * @throws ValidationException
     */
    public function invoice_refund(Request $request): RedirectResponse
    {
        Validator::make($request->toArray(), [
            'invoice_id' => ['required', 'integer'],
            'name' => ['required', 'string'],
        ])->validate();

        /* @var UploadedFile|null $file */
        if (empty($file = $request->files->get('file'))) {
            return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
        }

        /* @var Invoice $invoice */
        if (
            ! empty($invoice = Invoice::find($request->invoice_id)) &&
            $invoice->status == 'paid'
        ) {
            $file = File::create([
                'user_id' => ! empty($request->private) ? Auth::id() : null,
                'folder_id' => null,
                'name' =>  $file->getClientOriginalName(),
                'data' => $file->getContent(),
                'mime' => $file->getClientMimeType(),
                'size' => $file->getSize(),
            ]);

            if ($file instanceof File) {
                $revokationInvoice = $invoice->refund($file, $request->name, true);

                InvoiceHistory::create([
                    'user_id' => Auth::id(),
                    'invoice_id' => $invoice->id,
                    'status' => 'refund',
                ]);

                PrepaidHistory::create([
                    'user_id' => $invoice->user_id,
                    'invoice_id' => $invoice->id,
                    'amount' => $invoice->grossSumDiscounted * (-1),
                ]);

                return redirect()->route('admin.invoices.suppliers.details', $revokationInvoice->id)->with('success', __('interface.messages.invoice_refunded'));
            }
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
     * Show list of invoice importers.
     *
     * @return Renderable
     */
    public function invoice_importers_index(): Renderable
    {
        return view('admin.invoice.importers');
    }

    /**
     * Get list of invoice importers.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function invoice_importers_list(Request $request): JsonResponse
    {
        session_write_close();

        $query = InvoiceImporter::query();

        $totalCount = (clone $query)->count();

        if (! empty($request->search['value'])) {
            $query = $query->where(function (Builder $query) use ($request) {
                return $query->where('id', 'LIKE', '%' . $request->search['value'] . '%')
                    ->orWhere('name', 'LIKE', '%' . $request->search['value'] . '%')
                    ->orWhere('description', 'LIKE', '%' . $request->search['value'] . '%');
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

        return response()->json([
            'draw' => (int) $request->draw,
            'recordsTotal' => $totalCount,
            'recordsFiltered' => $filteredCount,
            'data' => $query
                ->get()
                ->transform(function (InvoiceImporter $importer) {
                    $edit = '
<a class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editImporter' . $importer->id . '"><i class="bi bi-pencil-square"></i></a>
<div class="modal fade" id="editImporter' . $importer->id . '" tabindex="-1" aria-labelledby="editImporter' . $importer->id . 'Label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title" id="editImporter' . $importer->id . 'Label">' . __('interface.actions.edit') . ' (' . $importer->name . ')</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="' . route('admin.invoices.importers.update', $importer->id) . '" method="post">
                <div class="modal-body">
                    <input type="hidden" name="_token" value="' . csrf_token() . '" />
                    <input type="hidden" name="importer_id" value="' . $importer->id . '" />
                    <div class="form-group row">
                        <label for="name" class="col-md-4 col-form-label text-md-right">' . __('interface.data.name') . '</label>

                        <div class="col-md-8">
                            <input id="name" type="text" class="form-control" name="name" value="' . $importer->name . '">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="description" class="col-md-4 col-form-label text-md-right">' . __('interface.data.description') . '</label>

                        <div class="col-md-8">
                            <input id="description" type="text" class="form-control" name="description" value="' . $importer->description . '">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="imap_host" class="col-md-4 col-form-label text-md-right">' . __('interface.data.host') . '</label>

                        <div class="col-md-8">
                            <input id="imap_host" type="text" class="form-control" name="imap[host]" value="' . ($importer->imapInbox->host ?? '') . '">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="imap_port" class="col-md-4 col-form-label text-md-right">' . __('interface.data.port') . '</label>

                        <div class="col-md-8">
                            <input id="imap_port" type="text" class="form-control" name="imap[port]" value="' . ($importer->imapInbox->port ?? '') . '">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="imap_protocol" class="col-md-4 col-form-label text-md-right">' . __('interface.data.protocol') . '</label>

                        <div class="col-md-8">
                            <select id="imap_protocol" type="text" class="form-control" name="imap[protocol]">
                                <option value="none"' . (($importer->imapInbox->protocol ?? '') == 'none' ? ' selected' : '') . '>' . __('interface.misc.none') . '</option>
                                <option value="tls"' . (($importer->imapInbox->protocol ?? '') == 'tls' ? ' selected' : '') . '>' . __('interface.misc.tls') . '</option>
                                <option value="ssl"' . (($importer->imapInbox->protocol ?? '') == 'ssl' ? ' selected' : '') . '>' . __('interface.misc.ssl') . '</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="imap_username" class="col-md-4 col-form-label text-md-right">' . __('interface.data.username') . '</label>

                        <div class="col-md-8">
                            <input id="imap_username" type="text" class="form-control" name="imap[username]" value="' . ($importer->imapInbox->username ?? '') . '">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="imap_password" class="col-md-4 col-form-label text-md-right">' . __('interface.data.password') . '</label>

                        <div class="col-md-8">
                            <input id="imap_password" type="password" class="form-control" name="imap[password]">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="imap_folder" class="col-md-4 col-form-label text-md-right">' . __('interface.data.folder') . '</label>

                        <div class="col-md-8">
                            <input id="imap_folder" type="text" class="form-control" name="imap[folder]" value="' . ($importer->imapInbox->folder ?? 'INBOX') . '">
                        </div>
                    </div>
                    <div class="form-group row align-items-center">
                        <label for="imap_validate_cert" class="col-md-4 col-form-label text-md-right">' . __('interface.misc.validate_certificate') . '</label>

                        <div class="col-md-8">
                            <input id="imap_validate_cert" type="checkbox" class="form-control" name="imap[validate_cert]" value="true"' . (($importer->imapInbox->validate_cert ?? false) ? ' checked' : '') . '>
                        </div>
                    </div>
                    <div class="form-group row align-items-center">
                        <label for="delete_after_import" class="col-md-4 col-form-label text-md-right">' . __('interface.misc.delete_after_import') . '</label>

                        <div class="col-md-8">
                            <input id="delete_after_import" type="checkbox" class="form-control" name="imap[delete_after_import]" value="true"' . (($importer->imapInbox->delete_after_import ?? false) ? ' checked' : '') . '>
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
                        'id' => $importer->id,
                        'name' => $importer->name,
                        'description' => $importer->description,
                        'log' => '<a href="' . route('admin.invoices.importers.log', $importer->id) . '" class="btn btn-primary btn-sm"><i class="bi bi-eye"></i></a>',
                        'edit' => $edit,
                        'delete' => '<a href="' . route('admin.invoices.importers.delete', $importer->id) . '" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i></a>',
                    ];
                })
        ]);
    }

    /**
     * Create a new invoice importer.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     *
     * @throws ValidationException
     */
    public function invoice_importers_add(Request $request): RedirectResponse
    {
        Validator::make($request->toArray(), [
            'name' => ['required', 'string'],
            'description' => ['string', 'nullable'],
            'imap' => ['required'],
        ])->validate();

        Validator::make($request->imap, [
            'host' => ['required', 'string'],
            'port' => ['required', 'integer'],
            'protocol' => ['required', 'string'],
            'username' => ['string', 'nullable'],
            'password' => ['string', 'nullable'],
            'folder' => ['required', 'string'],
            'validate_cert' => ['string',  'nullable'],
            'delete_after_import' => ['string',  'nullable'],
        ])->validate();

        if (
            $inbox = ImapInbox::create([
                'host' => $request->imap['host'],
                'username' => $request->imap['username'],
                'password' => $request->imap['password'],
                'port' => (int) $request->imap['port'],
                'protocol' => $request->imap['protocol'],
                'validate_cert' => (bool) $request->imap['validate_cert'] ?? false,
                'folder' => $request->imap['folder'],
                'delete_after_import' => (bool) $request->imap['delete_after_import'] ?? false,
            ])
        ) {
            InvoiceImporter::create([
                'name' => $request->name,
                'description' => $request->description,
                'imap_inbox_id' => $inbox->id,
            ]);

            return redirect()->back()->with('success', __('interface.messages.invoice_importer_added'));
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }

    /**
     * Update an existing invoice importer.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     *
     * @throws ValidationException
     */
    public function invoice_importers_update(Request $request): RedirectResponse
    {
        Validator::make($request->toArray(), [
            'importer_id' => ['required', 'integer'],
        ])->validate();

        Validator::make($request->imap, [
            'host' => ['required', 'string'],
            'port' => ['required', 'integer'],
            'protocol' => ['required', 'string'],
            'username' => ['string', 'nullable'],
            'password' => ['string', 'nullable'],
            'folder' => ['required', 'string'],
            'validate_cert' => ['string',  'nullable'],
            'delete_after_import' => ['string',  'nullable'],
        ])->validate();

        /* @var InvoiceImporter $importer */
        if (! empty($importer = InvoiceImporter::find($request->importer_id))) {
            $importer->update([
                'name' => $request->name,
                'description' => $request->description,
            ]);

            if (! empty($inbox = $importer->imapInbox)) {
                $data = [
                    'host' => $request->imap['host'],
                    'username' => $request->imap['username'],
                    'port' => (int) $request->imap['port'],
                    'protocol' => $request->imap['protocol'],
                    'validate_cert' => isset($request->imap['validate_cert']),
                    'folder' => $request->imap['folder'],
                    'delete_after_import' => isset($request->imap['delete_after_import']),
                ];

                if (! empty($request->imap['password'])) {
                    $data['password'] = $request->imap['password'];
                }

                $inbox->update($data);
            } else {
                /* @var ImapInbox $inbox */
                if (
                    ! empty(
                        $inbox = ImapInbox::create([
                            'host' => $request->imap['host'],
                            'username' => $request->imap['username'],
                            'password' => $request->imap['password'],
                            'port' => (int) $request->imap['port'],
                            'protocol' => $request->imap['protocol'],
                            'validate_cert' => isset($request->imap['validate_cert']),
                            'folder' => $request->imap['folder'],
                            'delete_after_import' => isset($request->imap['delete_after_import']),
                        ])
                    )
                ) {
                    $importer->update([
                        'imap_inbox_id' => $inbox->id,
                    ]);
                }
            }

            return redirect()->back()->with('success', __('interface.messages.invoice_importer_updated'));
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }

    /**
     * Delete an existing invoice importer.
     *
     * @param int $id
     *
     * @return RedirectResponse
     *
     * @throws ValidationException
     */
    public function invoice_importers_delete(int $id): RedirectResponse
    {
        Validator::make([
            'importer_id' => $id,
        ], [
            'importer_id' => ['required', 'integer'],
        ])->validate();

        /* @var InvoiceImporter $importer */
        if (! empty($importer = InvoiceImporter::find($id))) {
            $importer->imapInbox()->delete();
            $importer->delete();

            return redirect()->back()->with('success', __('interface.messages.invoice_importer_deleted'));
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }

    /**
     * Show list of previous invoice imports.
     *
     * @param int $id
     *
     * @return Renderable
     */
    public function invoice_importers_log(int $id): Renderable
    {
        return view('admin.invoice.importer_logs', [
            'importer' => InvoiceImporter::find($id),
        ]);
    }

    /**
     * Get list of invoice importer log entries.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function invoice_importers_log_list(Request $request): JsonResponse
    {
        session_write_close();

        $query = InvoiceImporterHistory::where('importer_id', '=', $request->id);

        $totalCount = (clone $query)->count();

        if (! empty($request->search['value'])) {
            $query = $query->where(function (Builder $query) use ($request) {
                return $query->where('id', 'LIKE', '%' . $request->search['value'] . '%')
                    ->orWhere('created_at', 'LIKE', '%' . $request->search['value'] . '%');
            });
        }

        if (! empty($request->order)) {
            foreach ($request->order as $order) {
                switch ($request->columns[$order['column']]) {
                    case 'to':
                        $orderBy = 'to';
                        break;
                    case 'from_name':
                        $orderBy = 'from_name';
                        break;
                    case 'from':
                        $orderBy = 'from';
                        break;
                    case 'subject':
                        $orderBy = 'subject';
                        break;
                    case 'created_at':
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
                ->transform(function (InvoiceImporterHistory $entry) {
                    return (object) [
                        'created_at' => $entry->created_at->format('d.m.Y, H:i'),
                        'subject' => $entry->subject,
                        'from' => $entry->from,
                        'from_name' => $entry->from_name,
                        'to' => $entry->to,
                        'download' => ! empty($file = $entry->file) ? '<a href="' . route('admin.filemanager.file.download', $file->id) . '" class="btn btn-warning btn-sm" download><i class="bi bi-download"></i></a>' : '<button type="button" class="btn btn-warning btn-sm" disabled><i class="bi bi-download"></i></button>',
                        'invoice' => ! empty($invoice = $entry->invoice) ? '<a href="' . route('admin.invoices.suppliers.details', $invoice->id) . '" class="btn btn-warning btn-sm" target="_blank"><i class="bi bi-file-earmark-text"></i></a>' : '<button type="button" class="btn btn-warning btn-sm" disabled><i class="bi bi-file-earmark-text"></i></button>',
                    ];
                })
        ]);
    }
}
