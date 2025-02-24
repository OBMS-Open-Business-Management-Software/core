<?php

declare(strict_types=1);

namespace App\PaymentGateways;

use Exception;
use Illuminate\Support\Collection;

/**
 * Interface Gateway.
 *
 * This interface defines the structure for payment gateway implementations.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
interface Gateway
{
    /**
     * Register the parameters which are being used by the payment method
     * (e.g. to authenticate against the API).
     *
     * @return Collection
     */
    public function parameters(): Collection;

    /**
     * Get a list of set settings for the payment method technical name.
     *
     * @return Collection
     */
    public function settings(): Collection;

    /**
     * Get payment method technical name.
     *
     * @return string
     */
    public function technicalName(): string;

    /**
     * Get payment method name.
     *
     * @return string
     */
    public function name(): string;

    /**
     * Get payment method icon src.
     *
     * @return string
     */
    public function icon(): ?string;

    /**
     * Get payment method status.
     *
     * @return bool
     */
    public function status(): bool;

    /**
     * Initialize a new payment. This should either return a result array or
     * redirect the user directly.
     *
     * @param        $type
     * @param        $method
     * @param        $client
     * @param        $description
     * @param        $identification
     * @param mixed  $payment          Either an invoice object or the amount which the user has to pay
     * @param        $invoice
     * @param        $returnCheckUrl
     * @param        $returnSuccessUrl
     * @param        $returnFailedUrl
     * @param        $returnNeutral
     * @param string $pingbackUrl
     *
     * @return array|null
     */
    public function initialize($type, $method, $client, $description, $identification, $payment, $invoice, $returnCheckUrl, $returnSuccessUrl, $returnFailedUrl, $returnNeutral, $pingbackUrl): ?array;

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
    public function return($type, $method, $client): array;

    /**
     * This function is called when a pingback is received by the payment service provider.
     * It may already check for the current payment status. Since PayPal doesn't provide
     * pingback functionality this is disabled.
     *
     * @param $type
     * @param $method
     * @param $client
     *
     * @throws Exception
     *
     * @return array
     */
    public function pingback($type, $method, $client): array;
}
