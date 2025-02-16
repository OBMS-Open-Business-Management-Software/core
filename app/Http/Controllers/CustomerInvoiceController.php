<?php

namespace App\Http\Controllers;

use App\Helpers\Download;
use App\Helpers\PaymentGateways;
use App\Models\Accounting\Invoice\Invoice;
use App\Models\Accounting\Invoice\InvoiceHistory;
use App\Models\Accounting\Invoice\InvoiceReminder;
use App\Models\Accounting\Invoice\InvoiceType;
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

class CustomerInvoiceController extends Controller
{
    /**
     * Show list of invoices.
     *
     * @return Renderable
     */
    public function invoice_index(): Renderable
    {
        return view('customer.invoice.home', [
            'types' => InvoiceType::all(),
        ]);
    }

    /**
     * Show list of invoices.
     *
     * @param int $id
     *
     * @return Renderable|RedirectResponse
     */
    public function invoice_details(int $id)
    {
        if (
            ! empty($invoice = Invoice::find($id)) &&
            ! empty($invoice->archived_at) &&
            $invoice->user_id == Auth::id()
        ) {
            return view('customer.invoice.details', [
                'invoice' => $invoice,
                'paymentMethods' => PaymentGateways::list(),
            ]);
        }

        return redirect()->back()->with('warning', __('Ooops, something went wrong. Please try again later.'));
    }

    /**
     * Get list of invoices.
     *
     * @param Request $request
     */
    public function invoice_list(Request $request): void
    {
        session_write_close();

        $query = Invoice::where('user_id', '=', Auth::id())
            ->whereNotNull('archived_at');

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

        header('Content-type: application/json');
        echo json_encode([
            'draw' => (int) $request->draw,
            'recordsTotal' => $totalCount,
            'recordsFiltered' => $filteredCount,
            'data' => $query
                ->get()
                ->transform(function (Invoice $invoice) {
                    switch ($invoice->status) {
                        case 'unpaid':
                            if ($invoice->overdue) {
                                $status = '<span class="badge badge-danger badge-pill">' . __('Overdue') . '</span>';
                            } else {
                                $status = '<span class="badge badge-warning badge-pill">' . __('Unpaid') . '</span>';
                            }
                            break;
                        case 'paid':
                            $status = '<span class="badge badge-success badge-pill">' . __('Paid') . '</span>';
                            break;
                        case 'refunded':
                            $status = '<span class="badge badge-secondary badge-pill">' . __('Refunded') . '</span>';
                            break;
                        case 'refund':
                            $status = '<span class="badge badge-info badge-pill text-white">' . __('Refund') . '</span>';
                            break;
                        case 'revoked':
                            $status = '<span class="badge badge-secondary badge-pill">' . __('Revoked') . '</span>';
                            break;
                        case 'template':
                        default:
                            $status = '<span class="badge badge-primary badge-pill">' . __('Draft') . '</span>';
                            break;
                    }

                    return (object) [
                        'id' => $invoice->number,
                        'status' => $status,
                        'type' => $invoice->type->name ?? __('N/A'),
                        'date' => ! empty($invoice->archived_at) ? $invoice->archived_at->format('d.m.Y, H:i') : __('N/A'),
                        'due' => ! empty($invoice->archived_at) ? $invoice->archived_at->addDays($invoice->type->period)->format('d.m.Y') . ', 23:59' : __('N/A'),
                        'view' => '<a href="' . route('customer.invoices.details', $invoice->id) . '" class="btn btn-primary btn-sm"><i class="bi bi-eye"></i></a>',
                    ];
                })
        ]);
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
            $invoice->user_id == Auth::id() &&
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
            $invoice->user_id == Auth::id() &&
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
     * Get list of invoice history entries.
     *
     * @param Request $request
     */
    public function invoice_history(Request $request): void
    {
        $query = InvoiceHistory::where('invoice_id', '=', $request->id)
            ->whereHas('invoice', function (Builder $builder) {
                return $builder->where('user_id', '=', Auth::id());
            });

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

        header('Content-type: application/json');
        echo json_encode([
            'draw' => (int) $request->draw,
            'recordsTotal' => $totalCount,
            'recordsFiltered' => $filteredCount,
            'data' => $query
                ->get()
                ->transform(function (InvoiceHistory $history) {
                    switch ($history->status) {
                        case 'publish':
                            $status = '<span class="badge badge-success badge-pill">' . __('Published') . '</span>';
                            break;
                        case 'revoke':
                            $status = '<span class="badge badge-secondary badge-pill">' . __('Revoked') . '</span>';
                            break;
                        case 'refund':
                            $status = '<span class="badge badge-info badge-pill text-white">' . __('Refund') . '</span>';
                            break;
                        case 'unpay':
                            $status = '<span class="badge badge-warning badge-pill">' . __('Unpaid') . '</span>';
                            break;
                        case 'pay':
                            $status = '<span class="badge badge-success badge-pill">' . __('Paid') . '</span>';
                            break;
                        default:
                            $status = '<span class="badge badge-secondary badge-pill">' . __('Unknown') . '</span>';
                            break;
                    }

                    return (object) [
                        'id' => $history->id,
                        'date' => $history->created_at->format('d.m.Y, H:i'),
                        'name' => ! empty($history->user) && ! empty($history->user->realName) ? '<i class="bi bi-person mr-2"></i> ' . $history->user->realName : '<i class="bi bi-robot mr-2""></i> ' . __('System'),
                        'status' => $status,
                    ];
                })
        ]);
    }
}
