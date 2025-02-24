<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Helpers\PaymentGateways;
use App\Models\PaymentGatewaySetting;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AdminPaymentGatewayController extends Controller
{
    /**
     * Show list of payment gateways.
     *
     * @return Renderable
     */
    public function gateway_index(): Renderable
    {
        return view('admin.payment.gateways', [
            'gateways' => PaymentGateways::list(),
        ]);
    }

    /**
     * Save payment gateway settings.
     *
     * @param Request $request
     *
     * @throws ValidationException
     *
     * @return RedirectResponse
     */
    public function gateway_save(Request $request): RedirectResponse
    {
        if (
            ! empty(
                $gateway = PaymentGateways::list()
                    ->filter(function ($object, $key) use ($request) {
                        return $key == $request->gateway;
                    })
                    ->first()
            )
        ) {
            $validation = [];
            $fields     = collect();

            $gateway->parameters()->each(function ($name, $key) use (&$validation, &$fields) {
                $validation[$key] = ['required'];
                $fields->push($key);
            });

            Validator::make($request->toArray(), $validation)->validate();

            PaymentGatewaySetting::where('gateway', '=', $request->gateway)
                ->delete();

            collect($request->toArray())->each(function ($value, $key) use ($fields, $request) {
                if ($fields->contains($key)) {
                    PaymentGatewaySetting::create([
                        'gateway' => $request->gateway,
                        'setting' => $key,
                        'value'   => $value,
                    ]);
                }
            });

            return redirect()->back()->with('success', __('interface.messages.payment_method_updated'));
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }
}
