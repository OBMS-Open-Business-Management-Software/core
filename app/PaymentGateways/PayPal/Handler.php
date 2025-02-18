<?php

namespace App\PaymentGateways\PayPal;

use App\PaymentGateways\Gateway;
use App\Traints\PaymentGateway\HasSettings;
use App\PaymentGateways\PayPal\Helpers\PaypalIPNClient;
use App\PaymentGateways\PayPal\Helpers\PaypalMerchantClient;
use Exception;
use Illuminate\Support\Collection;

/**
 * Class Handler.
 *
 * This class is a payment method handler for PayPal (SOAP).
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class Handler implements Gateway
{
    use HasSettings;

    /**
     * Register the parameters which are being used by the payment method
     * (e.g. to authenticate against the API).
     *
     * @return Collection
     */
    public function parameters(): Collection
    {
        return collect([
            "username" => __('paypal.username'),
            "publickey" => __('paypal.public_key'),
            "privatekey" => __('paypal.private_key'),
            "api_type" => __('paypal.api_type'),
        ]);
    }

    /**
     * Get payment method technical name.
     *
     * @return string
     */
    public function technicalName(): string
    {
        return 'paypal';
    }

    /**
     * Get payment method name.
     *
     * @return string
     */
    public function name(): string
    {
        return 'PayPal';
    }

    /**
     * Get payment method icon src.
     *
     * @return string
     */
    public function icon(): ?string
    {
        return null;
    }

    /**
     * Get payment method status.
     *
     * @return bool
     */
    public function status(): bool
    {
        return true;
    }

    /**
     * Initialize a new payment. This should either return a result array or
     * redirect the user directly.
     *
     * @param $type
     * @param $method
     * @param $client
     * @param $description
     * @param $identification
     * @param mixed $payment Either an invoice object or the amount which the user has to pay
     * @param $invoice
     * @param $returnCheckUrl
     * @param $returnSuccessUrl
     * @param $returnFailedUrl
     * @param $returnNeutral
     * @param string $pingbackUrl
     *
     * @return array|null
     */
    public function initialize($type, $method, $client, $description, $identification, $payment, $invoice, $returnCheckUrl, $returnSuccessUrl, $returnFailedUrl, $returnNeutral, $pingbackUrl): ?array
    {
        $mtid = $description . '_' . rand('1', '9999999');
        $get = '?payment=' . $mtid . '&amount=' . $payment;
        $returnCheckUrl = $returnCheckUrl . $get;

        $api = new PaypalMerchantClient($method);
        $query = $api->buildQuery([
            'PAYMENTACTION' => 'Sale',
            'AMT' => $payment,
            'RETURNURL' => $returnCheckUrl,
            'CANCELURL' => $returnCheckUrl,
            'DESC' => $description,
            'NOSHIPPING' => '1',
            'ALLOWNOTE' => '1',
            'CURRENCYCODE' => 'EUR',
            'METHOD' => 'SetExpressCheckout',
            'INVNUM' => $description,
            'CUSTOM' => $mtid
        ]);
        $result = $api->response($query);
        if (! $result) {
            return null;
        }
        $response = $result->getContent();
        $return = $api->responseParse($response);

        if ($return['ACK'] !== 'Success') {
            return [
                "status" => "success",
                "redirect" => $returnFailedUrl,
                "payment_id" => $mtid,
                "payment_status" => "failed",
            ];
        } else {
            $paymentPanel = $api->getGateway() . 'cmd=_express-checkout&useraction=commit&token=' . $return['TOKEN'];

            return [
                "status" => "success",
                "redirect" => $paymentPanel,
                "payment_id" => $mtid,
                "payment_status" => "waiting",
            ];
        }
    }

    /**
     * This function is called when the user returns to the page. It may already
     * check for the current payment status.
     *
     * @param $type
     * @param $method
     * @param $client
     *
     * @return array
     */
    public function return($type, $method, $client): array
    {
        $api = new PaypalMerchantClient($method);
        $return = $api->doPayment();

        if ($return['ACK'] == 'Success') {
            return [
                "status" => "success",
                "payment_id" => $_GET["payment"],
                "payment_status" => "success",
            ];
        } else {
            return [
                "status" => "success",
                "payment_id" => $_GET["payment"],
                "payment_status" => "failed",
            ];
        }
    }

    /**
     * This function is called when a pingback is received by the payment service provider.
     * It may already check for the current payment status. Since PayPal doesn't provide
     * pingback functionality this is disabled.
     *
     * @param $type
     * @param $method
     * @param $client
     *
     * @return array
     *
     * @throws Exception
     */
    public function pingback($type, $method, $client): array
    {
        $ipn = new PaypalIPNClient();
        if ($method->api_type == "test") {
            $ipn->useSandbox();
        }
        $verified = $ipn->verifyIPN();

        if ($verified) {
            if (
                $_POST["payment_status"] == "Failed" ||
                $_POST["payment_status"] == "Denied" ||
                $_POST["payment_status"] == "Expired"
            ) {
                return [
                    "status" => "success",
                    "payment_id" => $_POST["custom"],
                    "payment_status" => "failed",
                ];
            } elseif (
                $_POST["payment_status"] == "Refunded" ||
                $_POST["payment_status"] == "Reversed" ||
                $_POST["payment_status"] == "Voided"
            ) {
                return [
                    "status" => "success",
                    "payment_id" => $_POST["custom"],
                    "payment_status" => "revoked",
                ];
            } elseif (
                $_POST["payment_status"] == "Canceled_Reversal" ||
                $_POST["payment_status"] == "Completed" ||
                $_POST["payment_status"] == "Processed"
            ) {
                return [
                    "status" => "success",
                    "payment_id" => $_POST["custom"],
                    "payment_status" => "success",
                ];
            }
        }

        return [
            "status" => "false",
            "payment_id" => null,
            "payment_status" => null,
        ];
    }
}
