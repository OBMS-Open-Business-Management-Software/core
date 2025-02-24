<?php

declare(strict_types=1);

namespace App\Jobs\TenantJobs;

use App\Jobs\Structure\TenantJob;
use App\Jobs\Structure\UniquelyQueueable;
use App\Models\Accounting\Contract\Contract;
use App\Models\Accounting\Contract\ContractPosition;
use App\Models\Accounting\Invoice\Invoice;
use App\Models\Accounting\Invoice\InvoicePosition;
use App\Models\Accounting\Position;
use App\Models\Accounting\Prepaid\PrepaidHistory;
use App\Models\FileManager\File;
use Carbon\Carbon;
use Endroid\QrCode\ErrorCorrectionLevel;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\App;
use SepaQr\Data;

/**
 * Class ContractInvoicing.
 *
 * This class is the tenant job for generating contract invoices.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * TODO: Take trackers into account and put additional positions onto invoice.
 */
class ContractInvoicing extends TenantJob
{
    use UniquelyQueueable;

    public $tries = 1;

    public $timeout = 3600;

    public static $onQueue = 'contract_invoicing';

    /**
     * ContractInvoicing constructor.
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        parent::__construct($data);
    }

    /**
     * Execute job algorithm.
     */
    public function handle()
    {
        Contract::where('started_at', '<=', Carbon::now())
            ->where('cancelled_to', '>', Carbon::now())
            ->whereHas('type', function (Builder $builder) {
                return $builder->where('type', '=', 'contract_pre_pay')
                    ->orWhere('type', '=', 'contract_post_pay');
            })
            ->whereHas('user', function (Builder $builder) {
                return $builder->where('role', '=', 'customer');
            })
            ->each(function (Contract $contract) {
                switch ($contract->type->type) {
                    case 'contract_pre_pay':
                        if (empty($contract->last_invoice_at)) {
                            /* @var Invoice $invoice */
                            $invoice = Invoice::create([
                                'user_id'        => $contract->user_id,
                                'type_id'        => $contract->type->invoice_type_id,
                                'contract_id'    => $contract->id,
                                'status'         => 'unpaid',
                                'reverse_charge' => $contract->user->reverseCharge,
                            ]);

                            $contract->positionLinks->each(function (ContractPosition $link) use ($invoice) {
                                /* @var Position $position */
                                $position = Position::create([
                                    'order_id'       => $link->position->order_id,
                                    'product_id'     => $link->position->product_id,
                                    'discount_id'    => $link->position->discount_id,
                                    'name'           => $link->position->name,
                                    'description'    => $link->position->description,
                                    'amount'         => $link->position->amount,
                                    'vat_percentage' => $link->position->vat_percentage,
                                    'quantity'       => $link->position->quantity,
                                ]);

                                InvoicePosition::create([
                                    'invoice_id'  => $invoice->id,
                                    'position_id' => $position->id,
                                    'started_at'  => $link->started_at,
                                    'ended_at'    => $link->ended_at,
                                ]);
                            });

                            $invoice->update([
                                'archived_at' => $contract->started_at,
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
                                'sepaQr'  => $sepaQr,
                            ]);

                            $content = $pdf->output();

                            /* @var File $file */
                            $file = File::create([
                                'user_id'   => null,
                                'folder_id' => null,
                                'name'      => $invoice->number . '.pdf',
                                'data'      => $content,
                                'mime'      => 'application/pdf',
                                'size'      => strlen($content),
                            ]);

                            $invoice->update([
                                'file_id' => $file->id,
                            ]);

                            $contract->update([
                                'last_invoice_at' => $contract->started_at,
                            ]);
                        } elseif (
                            $contract->last_invoice_at
                                ->addDays($contract->type->invoice_period)
                                ->addDay()
                                ->startOfDay()
                                ->lte(Carbon::now())
                        ) {
                            /* @var Invoice $invoice */
                            $invoice = Invoice::create([
                                'user_id'        => $contract->user_id,
                                'type_id'        => $contract->type->invoice_type_id,
                                'contract_id'    => $contract->id,
                                'status'         => 'unpaid',
                                'reverse_charge' => $contract->user->reverseCharge,
                            ]);

                            $contract->positionLinks->each(function (ContractPosition $link) use ($invoice) {
                                /* @var Position $position */
                                $position = Position::create([
                                    'order_id'       => $link->position->order_id,
                                    'product_id'     => $link->position->product_id,
                                    'discount_id'    => $link->position->discount_id,
                                    'name'           => $link->position->name,
                                    'description'    => $link->position->description,
                                    'amount'         => $link->position->amount,
                                    'vat_percentage' => $link->position->vat_percentage,
                                    'quantity'       => $link->position->quantity,
                                ]);

                                InvoicePosition::create([
                                    'invoice_id'  => $invoice->id,
                                    'position_id' => $position->id,
                                    'started_at'  => $link->started_at,
                                    'ended_at'    => $link->ended_at,
                                ]);
                            });

                            $invoice->update([
                                'archived_at' => $contract->last_invoice_at->addDays($contract->type->invoice_period),
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
                                'sepaQr'  => $sepaQr,
                            ]);

                            $content = $pdf->output();

                            /* @var File $file */
                            $file = File::create([
                                'user_id'   => null,
                                'folder_id' => null,
                                'name'      => $invoice->number . '.pdf',
                                'data'      => $content,
                                'mime'      => 'application/pdf',
                                'size'      => strlen($content),
                            ]);

                            $invoice->update([
                                'file_id' => $file->id,
                            ]);

                            $contract->update([
                                'last_invoice_at' => $contract->last_invoice_at->addDays($contract->type->invoice_period),
                            ]);
                        }

                        break;
                    case 'contract_post_pay':
                        if (empty($contract->last_invoice_at)) {
                            $contract->update([
                                'last_invoice_at' => $contract->started_at,
                            ]);
                        } elseif (
                            $contract->last_invoice_at
                                ->addDays($contract->type->invoice_period)
                                ->addDay()
                                ->startOfDay()
                                ->lte(Carbon::now())
                        ) {
                            /* @var Invoice $invoice */
                            $invoice = Invoice::create([
                                'user_id'        => $contract->user_id,
                                'type_id'        => $contract->type->invoice_type_id,
                                'contract_id'    => $contract->id,
                                'status'         => 'unpaid',
                                'reverse_charge' => $contract->user->reverseCharge,
                            ]);

                            $contract->positionLinks->each(function (ContractPosition $link) use ($invoice, $contract) {
                                if (! empty($tracker = $link->trackerInstance)) {
                                    $trackFrom = $contract->last_invoice_at;
                                    $trackTo   = $invoice->created_at;

                                    if (
                                        ! empty($trackFrom) &&
                                        ! empty($trackTo)
                                    ) {
                                        $amount = $tracker->calculate($trackFrom, $trackTo, $positionDraft);

                                        if (
                                            $amount > 0 &&
                                            ! empty($positionDraft)
                                        ) {
                                            /* @var Position $position */
                                            $position = Position::create([
                                                'order_id'       => $link->position->order_id,
                                                'product_id'     => $link->position->product_id,
                                                'discount_id'    => $link->position->discount_id,
                                                'name'           => $positionDraft->name,
                                                'description'    => $positionDraft->description,
                                                'amount'         => $positionDraft->amount,
                                                'vat_percentage' => $positionDraft->vat_percentage,
                                                'quantity'       => $positionDraft->quantity,
                                            ]);
                                        }
                                    }
                                } else {
                                    /* @var Position $position */
                                    $position = Position::create([
                                        'order_id'       => $link->position->order_id,
                                        'product_id'     => $link->position->product_id,
                                        'discount_id'    => $link->position->discount_id,
                                        'name'           => $link->position->name,
                                        'description'    => $link->position->description,
                                        'amount'         => $link->position->amount,
                                        'vat_percentage' => $link->position->vat_percentage,
                                        'quantity'       => $link->position->quantity,
                                    ]);
                                }

                                InvoicePosition::create([
                                    'invoice_id'  => $invoice->id,
                                    'position_id' => $position->id,
                                    'started_at'  => $link->started_at,
                                    'ended_at'    => $link->ended_at,
                                ]);
                            });

                            $invoice->update([
                                'archived_at' => $contract->last_invoice_at->addDays($contract->type->invoice_period),
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
                                'sepaQr'  => $sepaQr,
                            ]);

                            $content = $pdf->output();

                            /* @var File $file */
                            $file = File::create([
                                'user_id'   => null,
                                'folder_id' => null,
                                'name'      => $invoice->number . '.pdf',
                                'data'      => $content,
                                'mime'      => 'application/pdf',
                                'size'      => strlen($content),
                            ]);

                            $invoice->update([
                                'file_id' => $file->id,
                            ]);

                            $contract->update([
                                'last_invoice_at' => $contract->last_invoice_at->addDays($contract->type->invoice_period),
                            ]);
                        }

                        break;
                }
            });

        Contract::where('started_at', '>', Carbon::now())
            ->whereNull('last_invoice_at')
            ->whereHas('type', function (Builder $builder) {
                return $builder->where('type', '=', 'prepaid_manual');
            })
            ->whereHas('user', function (Builder $builder) {
                return $builder->where('role', '=', 'customer');
            })
            ->each(function (Contract $contract) {
                if (
                    $contract->user->prepaidAccountBalance >= ($contract->grossSum - $contract->reserved_prepaid_amount) &&
                    $this->processPrepaidTransaction($contract)
                ) {
                    /* @var Invoice $invoice */
                    $invoice = Invoice::create([
                        'user_id'        => $contract->user_id,
                        'type_id'        => $contract->type->invoice_type_id,
                        'contract_id'    => $contract->id,
                        'status'         => 'paid',
                        'reverse_charge' => $contract->user->reverseCharge,
                    ]);

                    $contract->positionLinks->each(function (ContractPosition $link) use ($invoice) {
                        /* @var Position $position */
                        $position = Position::create([
                            'order_id'       => $link->position->order_id,
                            'product_id'     => $link->position->product_id,
                            'discount_id'    => $link->position->discount_id,
                            'name'           => $link->position->name,
                            'description'    => $link->position->description,
                            'amount'         => $link->position->amount,
                            'vat_percentage' => $link->position->vat_percentage,
                            'quantity'       => $link->position->quantity,
                        ]);

                        InvoicePosition::create([
                            'invoice_id'  => $invoice->id,
                            'position_id' => $position->id,
                            'started_at'  => $link->started_at,
                            'ended_at'    => $link->ended_at,
                        ]);
                    });

                    $invoice->update([
                        'archived_at' => $contract->started_at,
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
                        'sepaQr'  => $sepaQr,
                    ]);

                    $content = $pdf->output();

                    /* @var File $file */
                    $file = File::create([
                        'user_id'   => null,
                        'folder_id' => null,
                        'name'      => $invoice->number . '.pdf',
                        'data'      => $content,
                        'mime'      => 'application/pdf',
                        'size'      => strlen($content),
                    ]);

                    $invoice->update([
                        'file_id' => $file->id,
                    ]);

                    $contract->update([
                        'last_invoice_at'         => $contract->started_at,
                        'cancelled_at'            => $contract->started_at,
                        'cancellation_revoked_at' => null,
                        'cancelled_to'            => $contract->started_at->addDays($contract->type->invoice_period),
                    ]);
                }
            });

        Contract::where('started_at', '>', Carbon::now())
            ->whereNotNull('last_invoice_at')
            ->whereHas('type', function (Builder $builder) {
                return $builder->where('type', '=', 'prepaid_auto');
            })
            ->whereHas('user', function (Builder $builder) {
                return $builder->where('role', '=', 'customer');
            })
            ->each(function (Contract $contract) {
                if (
                    empty($contract->last_invoice_at) &&
                    $contract->user->prepaidAccountBalance >= ($contract->grossSum - $contract->reserved_prepaid_amount) &&
                    $this->processPrepaidTransaction($contract)
                ) {
                    /* @var Invoice $invoice */
                    $invoice = Invoice::create([
                        'user_id'        => $contract->user_id,
                        'type_id'        => $contract->type->invoice_type_id,
                        'contract_id'    => $contract->id,
                        'status'         => 'paid',
                        'reverse_charge' => $contract->user->reverseCharge,
                    ]);

                    $contract->positionLinks->each(function (ContractPosition $link) use ($invoice) {
                        /* @var Position $position */
                        $position = Position::create([
                            'order_id'       => $link->position->order_id,
                            'product_id'     => $link->position->product_id,
                            'discount_id'    => $link->position->discount_id,
                            'name'           => $link->position->name,
                            'description'    => $link->position->description,
                            'amount'         => $link->position->amount,
                            'vat_percentage' => $link->position->vat_percentage,
                            'quantity'       => $link->position->quantity,
                        ]);

                        InvoicePosition::create([
                            'invoice_id'  => $invoice->id,
                            'position_id' => $position->id,
                            'started_at'  => $link->started_at,
                            'ended_at'    => $link->ended_at,
                        ]);
                    });

                    $invoice->update([
                        'archived_at' => $contract->started_at,
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
                        'sepaQr'  => $sepaQr,
                    ]);

                    $content = $pdf->output();

                    /* @var File $file */
                    $file = File::create([
                        'user_id'   => null,
                        'folder_id' => null,
                        'name'      => $invoice->number . '.pdf',
                        'data'      => $content,
                        'mime'      => 'application/pdf',
                        'size'      => strlen($content),
                    ]);

                    $invoice->update([
                        'file_id' => $file->id,
                    ]);

                    $contract->update([
                        'last_invoice_at'         => $contract->started_at,
                        'cancelled_at'            => $contract->started_at,
                        'cancellation_revoked_at' => null,
                        'cancelled_to'            => $contract->started_at->addDays($contract->type->invoice_period),
                    ]);
                } elseif (
                    $contract->last_invoice_at
                        ->addDays($contract->type->invoice_period)
                        ->addDay()
                        ->startOfDay()
                        ->lte(Carbon::now()) &&
                    $contract->user->prepaidAccountBalance >= ($contract->grossSum - $contract->reserved_prepaid_amount) &&
                    $this->processPrepaidTransaction($contract)
                ) {
                    /* @var Invoice $invoice */
                    $invoice = Invoice::create([
                        'user_id'        => $contract->user_id,
                        'type_id'        => $contract->type->invoice_type_id,
                        'contract_id'    => $contract->id,
                        'status'         => 'paid',
                        'reverse_charge' => $contract->user->reverseCharge,
                    ]);

                    $contract->positionLinks->each(function (ContractPosition $link) use ($invoice) {
                        /* @var Position $position */
                        $position = Position::create([
                            'order_id'       => $link->position->order_id,
                            'product_id'     => $link->position->product_id,
                            'discount_id'    => $link->position->discount_id,
                            'name'           => $link->position->name,
                            'description'    => $link->position->description,
                            'amount'         => $link->position->amount,
                            'vat_percentage' => $link->position->vat_percentage,
                            'quantity'       => $link->position->quantity,
                        ]);

                        InvoicePosition::create([
                            'invoice_id'  => $invoice->id,
                            'position_id' => $position->id,
                            'started_at'  => $link->started_at,
                            'ended_at'    => $link->ended_at,
                        ]);
                    });

                    $invoice->update([
                        'archived_at' => $contract->last_invoice_at->addDays($contract->type->invoice_period),
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
                        'sepaQr'  => $sepaQr,
                    ]);

                    $content = $pdf->output();

                    /* @var File $file */
                    $file = File::create([
                        'user_id'   => null,
                        'folder_id' => null,
                        'name'      => $invoice->number . '.pdf',
                        'data'      => $content,
                        'mime'      => 'application/pdf',
                        'size'      => strlen($content),
                    ]);

                    $invoice->update([
                        'file_id' => $file->id,
                    ]);

                    $contract->update([
                        'last_invoice_at'         => $contract->last_invoice_at->addDays($contract->type->invoice_period),
                        'cancelled_at'            => $contract->last_invoice_at->addDays($contract->type->invoice_period),
                        'cancellation_revoked_at' => null,
                        'cancelled_to'            => $contract->last_invoice_at->addDays($contract->type->invoice_period)->addDays($contract->type->invoice_period),
                    ]);
                }
            });

        Contract::where('started_at', '>', Carbon::now())
            ->where('cancelled_to', '<=', Carbon::now())
            ->whereColumn('last_invoice_at', '<=', 'cancelled_to')
            ->whereHas('type', function (Builder $builder) {
                return $builder->where('type', '=', 'contract_post_pay');
            })
            ->whereHas('user', function (Builder $builder) {
                return $builder->where('role', '=', 'customer');
            })
            ->each(function (Contract $contract) {
                $factor = $contract->last_invoice_at->diffInDays($contract->cancelled_to) / $contract->type->invoice_period;

                /* @var Invoice $invoice */
                $invoice = Invoice::create([
                    'user_id'        => $contract->user_id,
                    'type_id'        => $contract->type->invoice_type_id,
                    'contract_id'    => $contract->id,
                    'status'         => 'unpaid',
                    'reverse_charge' => $contract->user->reverseCharge,
                ]);

                $contract->positionLinks->each(function (ContractPosition $link) use ($invoice, $contract, $factor) {
                    if (! empty($tracker = $link->trackerInstance)) {
                        $trackFrom = $contract->last_invoice_at;
                        $trackTo   = $invoice->created_at;

                        if (
                            ! empty($trackFrom) &&
                            ! empty($trackTo)
                        ) {
                            $amount = $tracker->calculate($trackFrom, $trackTo, $positionDraft);

                            if (
                                $amount > 0 &&
                                ! empty($positionDraft)
                            ) {
                                /* @var Position $position */
                                $position = Position::create([
                                    'order_id'       => $link->position->order_id,
                                    'product_id'     => $link->position->product_id,
                                    'discount_id'    => $link->position->discount_id,
                                    'name'           => $positionDraft->name,
                                    'description'    => $positionDraft->description,
                                    'amount'         => $positionDraft->amount,
                                    'vat_percentage' => $positionDraft->vat_percentage,
                                    'quantity'       => $positionDraft->quantity,
                                ]);
                            }
                        }
                    } else {
                        /* @var Position $position */
                        $position = Position::create([
                            'order_id'       => $link->position->order_id,
                            'product_id'     => $link->position->product_id,
                            'discount_id'    => $link->position->discount_id,
                            'name'           => $link->position->name,
                            'description'    => $link->position->description,
                            'amount'         => $link->position->amount * $factor,
                            'vat_percentage' => $link->position->vat_percentage,
                            'quantity'       => $link->position->quantity,
                        ]);
                    }

                    InvoicePosition::create([
                        'invoice_id'  => $invoice->id,
                        'position_id' => $position->id,
                        'started_at'  => $link->started_at,
                        'ended_at'    => $link->ended_at,
                    ]);
                });

                $invoice->update([
                    'archived_at' => $contract->cancelled_to,
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
                    'sepaQr'  => $sepaQr,
                ]);

                $content = $pdf->output();

                /* @var File $file */
                $file = File::create([
                    'user_id'   => null,
                    'folder_id' => null,
                    'name'      => $invoice->number . '.pdf',
                    'data'      => $content,
                    'mime'      => 'application/pdf',
                    'size'      => strlen($content),
                ]);

                $invoice->update([
                    'file_id' => $file->id,
                ]);

                $contract->update([
                    'last_invoice_at' => $contract->cancelled_to,
                ]);
            });
    }

    /**
     * Process and write prepaid transaction data.
     *
     * @param Contract $contract
     *
     * @return bool
     */
    private function processPrepaidTransaction(Contract $contract): bool
    {
        if ($contract->grossSum == 0) {
            return true;
        } elseif ($contract->reserved_prepaid_amount == 0) {
            PrepaidHistory::create([
                'user_id'            => $contract->user_id,
                'creator_user_id'    => null,
                'contract_id'        => $contract->id,
                'amount'             => $contract->grossSum * (-1),
                'transaction_method' => 'account',
                'transaction_id'     => null,
            ]);

            $contract->update([
                'reserved_prepaid_amount' => 0,
            ]);

            return true;
        } elseif (
            $contract->reserved_prepaid_amount > 0 &&
            $contract->reserved_prepaid_amount < $contract->grossSum
        ) {
            PrepaidHistory::create([
                'user_id'            => $contract->user_id,
                'creator_user_id'    => null,
                'contract_id'        => $contract->id,
                'amount'             => $contract->grossSum * (-1) + $contract->reserved_prepaid_amount,
                'transaction_method' => 'account',
                'transaction_id'     => null,
            ]);

            $contract->update([
                'reserved_prepaid_amount' => 0,
            ]);

            return true;
        } elseif ($contract->reserved_prepaid_amount == $contract->grossSum) {
            $contract->update([
                'reserved_prepaid_amount' => 0,
            ]);

            return true;
        }

        return false;
    }

    /**
     * Define tags which the job can be identified by.
     *
     * @return array
     */
    public function tags(): array
    {
        return $this->injectTenantTags([
            'job',
            'job:tenant',
            'job:tenant:ContractInvoicing',
        ]);
    }

    /**
     * Set a unique identifier to avoid duplicate queuing of the same task.
     *
     * @return string
     */
    public function getUniqueIdentifier(): string
    {
        return 'contract-invoicing-' . $this->tenant_id;
    }
}
