<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Models\Accounting\Invoice\Invoice;
use App\Models\Accounting\Invoice\InvoiceHistory;
use App\Models\Accounting\Prepaid\PrepaidHistory;
use App\Models\Payment;
use App\Models\PaymentGatewaySetting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

/**
 * Class PaymentGateways
 *
 * This class is the helper for handling payment gateways.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class PaymentGateways
{
    /**
     * Get a list of available payment methods as class instances
     * of the handlers.
     *
     * @return Collection
     */
    public static function list(): Collection
    {
        $list = collect();

        collect(scandir(__DIR__ . '/../PaymentGateways'))->reject(function (string $path) {
            return $path == '.' || $path == '..' || str_contains($path, '.php');
        })->transform(function (string $folder) use (&$list) {
            ClassFinder::getClassesInNamespace('App\PaymentGateways\\' . $folder)->transform(function (string $classPath) {
                return new $classPath();
            })->each(function ($method) use (&$list) {
                $list->put($method->technicalName(), $method);
            });
        });

        return $list;
    }

    /**
     * Initialize a payment.
     *
     * @param string       $method
     * @param float        $value
     * @param string       $type
     * @param Invoice|null $invoice
     */
    public static function initialize(string $method, float $value, string $type = 'prepaid', ?Invoice $invoice = null): ?RedirectResponse
    {
        if (
            $method == 'account' &&
            $type == 'invoice'
        ) {
            // TODO: Try to pay invoice via. account balance.
        } else {
            if (
                ! empty(
                    $method = PaymentGateways::list()->filter(function ($object, $name) use ($method) {
                        return $name == $method;
                    })->first()
                )
            ) {
                $checkUrl = route('customer.payment.check', [
                    'payment_method' => $method->technicalName(),
                    'payment_type'   => $type,
                ]);

                $successUrl = route('customer.payment.response', [
                    'payment_type'   => $type,
                    'payment_status' => 'success',
                ]);

                $failedUrl = route('customer.payment.response', [
                    'payment_type'   => $type,
                    'payment_status' => 'failure',
                ]);

                $waitingUrl = route('customer.payment.response', [
                    'payment_type'   => $type,
                    'payment_status' => 'waiting',
                ]);

                $pingbackUrl = route('customer.payment.pingback', [
                    'payment_method' => $method->technicalName(),
                    'payment_type'   => $type,
                ]);

                $paymentIdentification = 'P' . Carbon::now()->format('YmdHis') . Auth::id();
                $paymentName           = ! empty($invoice) ? $invoice->number : $paymentIdentification;

                $methodData = [];

                $method->settings()->transform(function (PaymentGatewaySetting $setting) use (&$methodData) {
                    $methodData[$setting->setting] = $setting->value;
                });

                $status = $method->initialize(
                    $type,
                    (object) $methodData,
                    Auth::user(),
                    $paymentName,
                    $paymentIdentification,
                    $value,
                    $invoice,
                    $checkUrl,
                    $successUrl,
                    $failedUrl,
                    $waitingUrl,
                    $pingbackUrl
                );

                if ($status['status'] == 'success') {
                    Payment::create([
                        'user_id'            => Auth::id(),
                        'invoice_id'         => ! empty($invoice) ? $invoice->id : null,
                        'method'             => $method->technicalName(),
                        'amount'             => $value,
                        'transaction_id'     => $status['payment_id'],
                        'transaction_status' => 0,
                    ]);

                    if (! empty($status['redirect'])) {
                        return redirect($status['redirect']);
                    }
                }
            }
        }

        return null;
    }

    /**
     * Synchronously check payment status when user returns to
     * application.
     *
     * @param string $method
     * @param string $type
     *
     * @return RedirectResponse
     */
    public static function check(string $method, string $type = 'prepaid'): RedirectResponse
    {
        if (
            ! empty(
                $method = PaymentGateways::list()->filter(function ($object, $name) use ($method) {
                    return $name == $method;
                })->first()
            )
        ) {
            $methodData = [];

            $method->settings()->transform(function (PaymentGatewaySetting $setting) use (&$methodData) {
                $methodData[$setting->setting] = $setting->value;
            });

            if (! empty($_GET['user_id'])) {
                $client = User::find($_GET['user_id']);
            } else {
                $client = Auth::user();
            }

            $status = $method->return($type, (object) $methodData, $client);

            if ($status['status'] == 'success') {
                if (
                    ! empty($type) &&
                    ! empty($status['payment_id'])
                ) {
                    /* @var Payment $transaction */
                    if (
                        ! empty(
                            $transaction = Payment::where('user_id', '=', $client->id)
                                ->where('id', '=', $status['payment_id'])
                                ->first()
                        )
                    ) {
                        if ($type == 'prepaid') {
                            if ($status['payment_status'] == 'success') {
                                PrepaidHistory::create([
                                    'user_id'    => $transaction->user_id,
                                    'invoice_id' => ! empty($invoiceId = $transaction->invoice_id) ? $invoiceId : null,
                                    'amount'     => $transaction->amount,
                                ]);
                            } elseif ($status['payment_status'] == 'revoked') {
                                PrepaidHistory::create([
                                    'user_id'    => $transaction->user_id,
                                    'invoice_id' => ! empty($invoiceId = $transaction->invoice_id) ? $invoiceId : null,
                                    'amount'     => $transaction->amount * (-1),
                                ]);
                            }

                            $transaction->update([
                                'transaction_status' => $status['payment_status'],
                            ]);

                            if (! empty($status['redirect'])) {
                                header('Location: ' . $status['redirect']);
                            } else {
                                if ($status['payment_status'] == 'success') {
                                    return redirect()->route('customer.invoices')->with('success', __('interface.messages.payment_successful_booked'));
                                } elseif (
                                    $status['payment_status'] == 'revoked' ||
                                    $status['payment_status'] == 'failed'
                                ) {
                                    return redirect()->route('customer.invoices')->with('warning', __('interface.messages.payment_failed'));
                                } elseif ($status['payment_status'] == 'waiting') {
                                    return redirect()->route('customer.invoices')->with('success', __('interface.messages.payment_waiting_booked'));
                                }
                            }
                        } else {
                            if (! empty($invoice = $transaction->invoice)) {
                                if ($status['payment_status'] == 'success') {
                                    $invoice->update([
                                        'status' => 'paid',
                                    ]);

                                    InvoiceHistory::create([
                                        'user_id'    => $client->id ?? null,
                                        'invoice_id' => $invoice->id,
                                        'status'     => 'pay',
                                    ]);
                                } elseif (
                                    (
                                        $status['payment_status'] == 'revoked' ||
                                        $status['payment_status'] == 'failed'
                                    ) &&
                                    $invoice->status !== 'unpaid'
                                ) {
                                    $invoice->update([
                                        'status' => 'unpaid',
                                    ]);

                                    InvoiceHistory::create([
                                        'user_id'    => $client->id ?? null,
                                        'invoice_id' => $invoice->id,
                                        'status'     => 'unpay',
                                    ]);
                                }

                                $transaction->update([
                                    'transaction_status' => $status['payment_status'],
                                ]);

                                if (! empty($status['redirect'])) {
                                    header('Location: ' . $status['redirect']);
                                } else {
                                    if ($status['payment_status'] == 'success') {
                                        return redirect()->route('customer.profile.transactions')->with('success', __('interface.messages.payment_successful'));
                                    } elseif (
                                        $status['payment_status'] == 'revoked' ||
                                        $status['payment_status'] == 'failed'
                                    ) {
                                        return redirect()->route('customer.invoices')->with('warning', __('interface.messages.payment_failed'));
                                    } elseif ($status['payment_status'] == 'waiting') {
                                        return redirect()->route('customer.profile.transactions')->with('success', __('interface.messages.payment_waiting'));
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        return redirect()->route('customer.home')->with('warning', __('interface.misc.something_wrong_notice'));
    }

    /**
     * Handle asynchronous payment status checks.
     *
     * @param string $method
     * @param string $type
     */
    public static function pingback(string $method, string $type = 'prepaid'): void
    {
        if (
            ! empty(
                $method = PaymentGateways::list()->filter(function ($object, $name) use ($method) {
                    return $name == $method;
                })->first()
            )
        ) {
            $methodData = [];

            $method->settings()->transform(function (PaymentGatewaySetting $setting) use (&$methodData) {
                $methodData[$setting->setting] = $setting->value;
            });

            if (! empty($_GET['user_id'])) {
                $client = User::find($_GET['user_id']);
            } else {
                $client = Auth::user();
            }

            $status = $method->return($type, (object) $methodData, $client);

            if ($status['status'] == 'success') {
                if (
                    ! empty($type) &&
                    ! empty($status['payment_id'])
                ) {
                    /* @var Payment $transaction */
                    if (
                        ! empty(
                            $transaction = Payment::where('user_id', '=', $client->id)
                                ->where('id', '=', $status['payment_id'])
                                ->first()
                        )
                    ) {
                        if ($type == 'prepaid') {
                            if ($status['payment_status'] == 'success') {
                                PrepaidHistory::create([
                                    'user_id'    => $transaction->user_id,
                                    'invoice_id' => ! empty($invoiceId = $transaction->invoice_id) ? $invoiceId : null,
                                    'amount'     => $transaction->amount,
                                ]);
                            } elseif ($status['payment_status'] == 'revoked') {
                                PrepaidHistory::create([
                                    'user_id'    => $transaction->user_id,
                                    'invoice_id' => ! empty($invoiceId = $transaction->invoice_id) ? $invoiceId : null,
                                    'amount'     => $transaction->amount * (-1),
                                ]);
                            }

                            $transaction->update([
                                'transaction_status' => $status['payment_status'],
                            ]);
                        } else {
                            if (! empty($invoice = $transaction->invoice)) {
                                if ($status['payment_status'] == 'success') {
                                    $invoice->update([
                                        'status' => 'paid',
                                    ]);

                                    InvoiceHistory::create([
                                        'user_id'    => $client->id ?? null,
                                        'invoice_id' => $invoice->id,
                                        'status'     => 'pay',
                                    ]);
                                } elseif (
                                    (
                                        $status['payment_status'] == 'revoked' ||
                                        $status['payment_status'] == 'failed'
                                    ) &&
                                    $invoice->status !== 'unpaid'
                                ) {
                                    $invoice->update([
                                        'status' => 'unpaid',
                                    ]);

                                    InvoiceHistory::create([
                                        'user_id'    => $client->id ?? null,
                                        'invoice_id' => $invoice->id,
                                        'status'     => 'unpay',
                                    ]);
                                }

                                $transaction->update([
                                    'transaction_status' => $status['payment_status'],
                                ]);
                            }
                        }
                    }
                }
            }
        }
    }
}
