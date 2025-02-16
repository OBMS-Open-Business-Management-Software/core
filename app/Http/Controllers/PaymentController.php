<?php

namespace App\Http\Controllers;

use App\Helpers\PaymentGateways;
use App\Models\Accounting\Invoice\Invoice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    /**
     * Check payment response.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function customer_check(Request $request): RedirectResponse
    {
        return PaymentGateways::check($request->payment_method, $request->payment_type);
    }

    /**
     * Check payment response pingback.
     *
     * @param Request $request
     *
     * @return void
     */
    public function pingback(Request $request): void
    {
        PaymentGateways::pingback($request->payment_method, $request->payment_type);
    }

    /**
     * Generate payment response.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function customer_response(Request $request): RedirectResponse
    {
        if ($request->payment_type == 'prepaid') {
            $baseRedirect = redirect()->route('customer.profile.transactions');
        } else {
            $baseRedirect = redirect()->route('customer.invoices');
        }

        if ($request->payment_status == 'success') {
            $redirect = $baseRedirect->with('success', __('Payment successful. It might take a couple minutes for the money to be booked.'));
        } elseif ($request->payment_status == 'failure') {
            $redirect = $baseRedirect->with('warning', __('Payment failed. Please try again later.'));
        } elseif ($request->payment_status == 'waiting') {
            $redirect = $baseRedirect->with('success', __('Payment pending. It might take a couple minutes for the money to be booked.'));
        } else {
            $redirect = $baseRedirect->with('warning', __('Ooops, something went wrong. Please try again later.'));
        }

        return $redirect;
    }

    /**
     * Initialize a deposit payment.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function customer_initialize_invoice(Request $request): RedirectResponse
    {
        /* @var Invoice|null $invoice */
        if (! empty($invoice = Invoice::find($request->invoice_id))) {
            return PaymentGateways::initialize($request->payment_method, $invoice->grossSumDiscounted, 'invoice', $invoice);
        }

        return redirect()->back()->with('warning', __('Ooops, something went wrong. Please try again later.'));
    }

    /**
     * Initialize a deposit payment.
     *
     * @param Request $request
     *
     * @return void
     */
    public function customer_initialize_deposit(Request $request): RedirectResponse
    {
        return PaymentGateways::initialize($request->payment_method, $request->amount);
    }
}
