<?php

declare(strict_types=1);

namespace App\PaymentGateways\PayPal\Helpers;

class PaypalMerchantClient
{
    private $host;

    private $gate;

    private $endpoint;

    private $config;

    /**
     * PaypalMerchantClient constructor.
     *
     * @param $config
     */
    public function __construct($config)
    {
        $this->config = $config;

        if ($this->config->api_type == 'test') {
            $this->gate = 'https://www.sandbox.paypal.com/cgi-bin/webscr?';
            $this->host = 'api-3t.sandbox.paypal.com';
        } elseif ($this->config->api_type == 'live') {
            $this->gate = 'https://www.paypal.com/cgi-bin/webscr?';
            $this->host = 'api-3t.paypal.com';
        }
        $this->endpoint = '/nvp';
    }

    /**
     * @return string
     */
    public function getGateway()
    {
        return $this->gate;
    }

    /**
     * @param $data
     *
     * @return false|HTTPRequest
     */
    public function response($data)
    {
        $request = new HTTPRequest($this->host, $this->endpoint, 'POST', true);
        $result  = $request->connect($data);

        if ($result < 400) {
            return $request;
        }

        return false;
    }

    /**
     * @param array $data
     *
     * @return string
     */
    public function buildQuery($data = [])
    {
        $data['USER']      = $this->config->username;
        $data['PWD']       = $this->config->publickey;
        $data['SIGNATURE'] = $this->config->privatekey;
        $data['VERSION']   = '52.0';
        $query             = http_build_query($data);

        return $query;
    }

    /**
     * @param $token
     *
     * @return array|false
     */
    public function getCheckoutDetails($token)
    {
        $data = [
            'TOKEN'  => $token,
            'METHOD' => 'GetExpressCheckoutDetails',
        ];
        $query = $this->buildQuery($data);

        $result = $this->response($query);

        if (!$result) {
            return false;
        }
        $response = $result->getContent();
        $return   = $this->responseParse($response);

        return $return;
    }

    public function doPayment()
    {
        $token   = $_GET['token'];
        $payer   = $_GET['PayerID'];
        $details = $this->getCheckoutDetails($token);

        if (!$details) {
            return false;
        }
        list($amount, $currency, $invoice) = explode('|', $details['CUSTOM']);
        $data                              = [
            'PAYMENTACTION' => 'Sale',
            'PAYERID'       => $payer,
            'TOKEN'         => $token,
            'AMT'           => $amount,
            'CURRENCYCODE'  => $currency,
            'METHOD'        => 'DoExpressCheckoutPayment',
        ];
        $query = $this->buildQuery($data);

        $result = $this->response($query);

        if (!$result) {
            return false;
        }
        $response = $result->getContent();
        $return   = $this->responseParse($response);

        return $return;
    }

    /**
     * @param $response
     *
     * @return array
     */
    public function responseParse($response)
    {
        $a   = explode('&', $response);
        $out = [];

        foreach ($a as $v) {
            $k = strpos($v, '=');

            if ($k) {
                $key   = trim(substr($v, 0, $k));
                $value = trim(substr($v, $k + 1));

                if (!$key) {
                    continue;
                }
                $out[$key] = urldecode($value);
            } else {
                $out[] = $v;
            }
        }

        return $out;
    }
}
