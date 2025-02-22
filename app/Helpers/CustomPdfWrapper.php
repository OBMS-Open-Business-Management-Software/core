<?php

namespace App\Helpers;

use App\Models\Accounting\Invoice\Invoice;
use App\Models\Accounting\Invoice\InvoicePosition;
use App\Models\Address\Country;
use Barryvdh\DomPDF\PDF;
use Error;
use Exception;
use horstoeko\zugferd\ZugferdDocumentBuilder;
use horstoeko\zugferd\ZugferdDocumentPdfMerger;
use horstoeko\zugferd\ZugferdProfiles;

/**
 * Class CustomPdfWrapper.
 *
 * This class is the helper for custom pdf xrechnung injection.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class CustomPdfWrapper extends PDF
{
    protected string $xmlPayload = '';
    protected ?Invoice $invoice;

    public function loadView(string $view, array $data = [], array $mergeData = [], ?string $encoding = null): self
    {
        if (isset($data['invoice'])) {
            $this->invoice = $data['invoice'];

            try {
                $this->xmlPayload = $this->generateXmlPayload($data['invoice']);
            } catch (Exception|Error $exception) {
            }
        }

        return parent::loadView($view, $data, $mergeData, $encoding);
    }

    public function output(array $options = []): string
    {
        $pdfOutput = parent::output($options);

        if ($pdfOutput && $this->xmlPayload) {
            return $this->mergePdfWithXml($pdfOutput, $this->xmlPayload);
        }

        return $pdfOutput;
    }

    protected function generateXmlPayload(Invoice $invoice)
    {
        /**
         * Build document base.
         */
        $document = ZugferdDocumentBuilder::createNew(ZugferdProfiles::PROFILE_EN16931)
            ->setDocumentInformation(
                $invoice->number,
                $invoice->status == 'refund' ? '381' : '380',
                $invoice->archived_at->toDate(),
                'EUR'
            );

        if (! empty($sellerName = config('company.name'))) {
            $document = $document->setDocumentSeller($sellerName, strtolower(str_replace(' ', '', $sellerName)));
        }

        if (! empty($sellerTaxId = config('company.tax_id'))) {
            $document = $document->addDocumentSellerTaxRegistration('FC', $sellerTaxId);
        }

        if (! empty($sellerVatId = config('company.vat_id'))) {
            $document = $document->addDocumentSellerTaxRegistration('VA', $sellerVatId);
        }

        if (
            ! empty($sellerAddressStreet = config('company.address.street')) &&
            ! empty($sellerAddressHousenumber = config('company.address.housenumber')) &&
            ! empty($sellerAddressPostalcode = config('company.address.postalcode')) &&
            ! empty($sellerAddressCity = config('company.address.city')) &&
            ! empty($sellerAddressCountry = Country::find(config('company.default_country')))
        ) {
            $sellerAddressAddition = config('company.address.addition');

            $document = $document->setDocumentSellerAddress(
                $sellerAddressStreet . ' ' . $sellerAddressHousenumber,
                $sellerAddressAddition,
                '',
                $sellerAddressPostalcode,
                $sellerAddressCity,
                $sellerAddressCountry->iso2
            );
        }

        if (! empty($sellerEmail = config('company.email'))) {
            $document = $document->setDocumentSellerCommunication('EM', $sellerEmail);
        }

        if (! empty($sellerPhone = config('company.phone'))) {
            $document = $document->setDocumentSellerCommunication('TE', $sellerPhone);
        }

        if (! empty($sellerFax = config('company.fax'))) {
            $document = $document->setDocumentSellerCommunication('FX', $sellerFax);
        }

        if (! empty($sellerRepresentative = config('company.representative'))) {
            $document = $document->setDocumentSellerContact(
                $sellerRepresentative,
                null,
                null,
                null,
                null,
            );
        }

        if (! empty($buyerProfile = $invoice->user->profile)) {
            if (
                ! empty($buyerAddress = $buyerProfile->billingPostalAddress) &&
                $buyerAddress->country
            ) {
                $document = $document->setDocumentBuyer($buyerProfile->company ?: $buyerProfile->firstname . ' ' . $buyerProfile->lastname)
                    ->setDocumentBuyerAddress(
                        $buyerAddress->street . ' ' . $buyerAddress->housenumber,
                        $buyerAddress->addition,
                        '',
                        $buyerAddress->postalcode,
                        $buyerAddress->city,
                        $buyerAddress->country?->iso2
                    );
            }

            if (! empty($buyerEmail = $buyerProfile->billingEmailAddress?->email)) {
                $document = $document->setDocumentSellerCommunication('EM', $buyerEmail);
            }

            if (! empty($buyerPhone = $buyerProfile->billingPhoneNumber?->phone)) {
                $document = $document->setDocumentSellerCommunication('TE', $buyerPhone);
            }

            $buyerBankAccount = $buyerProfile->primaryBankAccount;

            $document = $document->addDocumentPaymentMean(
                $buyerBankAccount?->sepa_signed_at ? '59' : '30',
                null,
                null,
                null,
                null,
                $buyerBankAccount?->iban,
                config('company.bank.iban'),
                config('company.bank.owner'),
                config('company.bank.national_account_number'),
                config('company.bank.bic')
            );
        }

        $document = $document->addDocumentPaymentTerm(
            __('interface.misc.payment_notice', [
                    'days' => $invoice->type->period,
                ]),
            $invoice->archived_at->addDays($invoice->type->period)->toDate()
        )
            ->setDocumentBuyerReference('UNKNOWN');

        $allowanceCharges = collect();

        /**
         * Inject document positions.
         */
        $invoice->positionLinks->each(function (InvoicePosition $positionLink, int $key) use ($invoice, &$document, &$allowanceCharges) {
            if (! empty($position = $positionLink->position)) {
                $document = $document->addNewPosition((string)($key + 1))
                    ->setDocumentPositionProductDetails($position->name, $position->description)
                    ->setDocumentPositionNetPrice($position->amount, 1, 'H87')
                    ->setDocumentPositionQuantity($position->quantity, 'H87') // See: https://www.xrepository.de/details/urn:xoev-de:kosit:codeliste:rec20_1
                    ->addDocumentPositionTax($invoice->reverse_charge ? 'AE' : ($position->vat_percentage == 0 ? 'G' : 'S'), 'VAT', $position->vat_percentage, $position->vatSum)
                    ->setDocumentPositionLineSummation($position->netSum);

                if ($position->discount) {
                    $allowanceChargeKey = $allowanceCharges->filter(function ($allowanceCharge) use ($invoice, $position) {
                        return $allowanceCharge->taxCategoryCode === ($invoice->reverse_charge ? 'AE' : ($position->vat_percentage == 0 ? 'G' : 'S')) &&
                            $allowanceCharge->taxVATPercentage === $position->vat_percentage;
                    })->keys()->first();

                    $allowanceCharge = null;

                    if (is_numeric($allowanceChargeKey)) {
                        $allowanceCharge = $allowanceCharges->pull($allowanceChargeKey);
                    }

                    $allowanceCharges->push((object) [
                        'taxCategoryCode' => $invoice->reverse_charge ? 'AE' : ($position->vat_percentage == 0 ? 'G' : 'S'),
                        'taxVATPercentage' => $position->vat_percentage,
                        'amount' => $allowanceCharge ? $allowanceCharge->amount + $position->discountNetSum : $position->discountNetSum,
                        'tax' => $allowanceCharge ? $allowanceCharge->tax + $position->discountVatSum : $position->discountVatSum,
                    ]);
                }
            }
        });

        /**
         * Inject document allowances.
         */
        $allowanceCharges = $allowanceCharges->filter(function ($allowance) {
            return $allowance->amount > 0;
        });

        $allowanceCharges->each(function ($allowance) use (&$document) {
            $document = $document->addDocumentAllowanceCharge(
                $allowance->amount,
                false,
                $allowance->taxCategoryCode,
                'VAT',
                $allowance->taxVATPercentage,
                null,
                null,
                null,
                null,
                null,
                null,
                '95',
                __('interface.data.discount'),
            );
        });

        $allowanceChargesTotal = $allowanceCharges->pluck('amount')->map(function ($amount) {
            return round($amount, 2);
        })->sum();

        /**
         * Inject document tax rates.
         */
        $invoice->vatPositions->each(function (float $vatAmount, float $vatRate) use ($invoice, &$document) {
            $netPosition = $invoice->netPositions->first(function (float $netAmount, float $netVatRate) use ($vatRate) {
                return $vatRate === $netVatRate;
            });

            $document = $document->addDocumentTax(
                $invoice->reverse_charge ? 'AE' : ($vatRate == 0 ? 'G' : 'S'),
                'VAT',
                $netPosition,
                $vatAmount,
                $vatRate
            );
        });

        /**
         * Inject document summary.
         */
        $document = $document->setDocumentSummation(
            $invoice->grossSum,
            $invoice->grossSum,
            $invoice->netSum + $allowanceChargesTotal,
            null,
            $allowanceChargesTotal > 0 ? $allowanceChargesTotal : null,
            $invoice->netSum,
            $invoice->vatSum,
        );


        return $document->getContent();
    }

    protected function mergePdfWithXml(string $pdfOutput, string $xmlPayload): string
    {
        $zugferdPdfMerger = new ZugferdDocumentPdfMerger($xmlPayload, $pdfOutput);
        $zugferdPdfObject = $zugferdPdfMerger->generateDocument();

        return $zugferdPdfObject->downloadString($this->invoice->number . '.pdf');
    }
}
