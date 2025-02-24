<?php

declare(strict_types=1);

namespace App\Jobs\TenantJobs;

use App\Jobs\Structure\TenantJob;
use App\Jobs\Structure\UniquelyQueueable;
use App\Models\Accounting\Contract\ContractPosition;
use App\Models\Accounting\Invoice\Invoice;
use App\Models\Accounting\Invoice\InvoiceDunning;
use App\Models\Accounting\Invoice\InvoicePosition;
use App\Models\Accounting\Invoice\InvoiceReminder;
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
 * Class InvoiceReminders.
 *
 * This class is the tenant job for sending out invoice reminders.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class InvoiceReminders extends TenantJob
{
    use UniquelyQueueable;

    public $tries = 1;

    public $timeout = 3600;

    public static $onQueue = 'invoice_reminders';

    /**
     * InvoiceReminders constructor.
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
        // Send reminders
        Invoice::where('status', '=', 'unpaid')
            ->whereNotNull('archived_at')
            ->whereHas('type', function (Builder $builder) {
                return $builder->whereHas('dunnings')
                    ->where('dunning', '=', true)
                    ->where('type', '=', 'normal');
            })
            ->whereHas('user', function (Builder $builder) {
                return $builder->where('role', '=', 'customer');
            })
            ->each(function (Invoice $invoice) {
                if ($invoice->overdue) {
                    $overdueSince = $invoice->archived_at
                        ->addDays($invoice->type->period)
                        ->addDay()
                        ->startOfDay();

                    /* @var InvoiceDunning|null $lastDunning */
                    $lastDunning = null;

                    $invoice->type
                        ->dunnings
                        ->sortBy('after')
                        ->each(function (InvoiceDunning $dunning) use ($invoice, $overdueSince, &$lastDunning) {
                            if (empty($invoice->reminders->where('dunning_id', '=', $dunning->id)->first())) {
                                if (($sendAt = $overdueSince->addDays($dunning->after))->lte(Carbon::now())) {
                                    if (
                                        ! isset($lastDunning) ||
                                        $lastDunning->after < $dunning->after
                                    ) {
                                        if (
                                            ! empty(
                                                $reminder = InvoiceReminder::create([
                                                    'invoice_id' => $invoice->id,
                                                    'dunning_id' => $dunning->id,
                                                    'due_at'     => $sendAt,
                                                ])
                                            ) &&
                                            $reminder instanceof InvoiceReminder
                                        ) {
                                            $reminder->update([
                                                'archived_at' => Carbon::now(),
                                            ]);

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
                                                'sepaQr'   => $sepaQr,
                                            ]);

                                            $content = $pdf->output();

                                            $file = File::create([
                                                'user_id'   => null,
                                                'folder_id' => null,
                                                'name'      => $reminder->number . '.pdf',
                                                'data'      => $content,
                                                'mime'      => 'application/pdf',
                                                'size'      => strlen($content),
                                            ]);

                                            if ($file instanceof File) {
                                                $reminder->update([
                                                    'file_id' => $file,
                                                ]);

                                                $reminder->sendReminderNotification();
                                            } else {
                                                $reminder->update([
                                                    'archived_at' => null,
                                                ]);
                                            }

                                            if (! empty($contract = $reminder->invoice->contract)) {
                                                if ($reminder->dunning->cancel_contract_instant) {
                                                    if ($contract->status == 'started') {
                                                        if ($contract->type->type == 'contract_pre_pay') {
                                                            /* @var Invoice|null $invoice */
                                                            if (! empty($invoice = $contract->invoices()->whereNotNull('archived_at')->orderByDesc('archived_at')->first())) {
                                                                $factor = ($contract->type->invoice_period - $contract->last_invoice_at->diffInDays(Carbon::now())) / $contract->type->invoice_period;

                                                                /* @var Invoice $revokationInvoice */
                                                                $revokationInvoice = Invoice::create([
                                                                    'user_id'        => $contract->user_id,
                                                                    'type_id'        => $contract->type->invoice_type_id,
                                                                    'contract_id'    => $contract->id,
                                                                    'original_id'    => $invoice->id,
                                                                    'status'         => 'refund',
                                                                    'reverse_charge' => $contract->user->reverseCharge,
                                                                ]);

                                                                $contract->positionLinks->each(function (ContractPosition $link) use ($revokationInvoice, $factor) {
                                                                    /* @var Position $position */
                                                                    $position = Position::create([
                                                                        'order_id'       => $link->position->order_id,
                                                                        'product_id'     => $link->position->product_id,
                                                                        'discount_id'    => $link->position->discount_id,
                                                                        'name'           => $link->position->name,
                                                                        'description'    => $link->position->description,
                                                                        'amount'         => $link->position->amount * $factor * (-1),
                                                                        'vat_percentage' => $link->position->vat_percentage,
                                                                        'quantity'       => $link->position->quantity,
                                                                    ]);

                                                                    InvoicePosition::create([
                                                                        'invoice_id'  => $revokationInvoice->id,
                                                                        'position_id' => $position->id,
                                                                        'started_at'  => $link->started_at,
                                                                        'ended_at'    => $link->ended_at,
                                                                    ]);
                                                                });

                                                                $revokationInvoice->update([
                                                                    'archived_at' => Carbon::now(),
                                                                ]);

                                                                try {
                                                                    $sepaQr = ! empty($amount = $revokationInvoice->grossSum) && $amount > 0 ? Data::create()
                                                                    ->setName(config('company.bank.owner'))
                                                                    ->setIban(str_replace(' ', '', config('company.bank.iban')))
                                                                    ->setRemittanceText($revokationInvoice->number)
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
                                                                    'invoice' => $revokationInvoice,
                                                                    'sepaQr'  => $sepaQr,
                                                                ]);

                                                                $content = $pdf->output();

                                                                /* @var File $file */
                                                                $file = File::create([
                                                                    'user_id'   => null,
                                                                    'folder_id' => null,
                                                                    'name'      => $revokationInvoice->number . '.pdf',
                                                                    'data'      => $content,
                                                                    'mime'      => 'application/pdf',
                                                                    'size'      => strlen($content),
                                                                ]);

                                                                $revokationInvoice->update([
                                                                    'file_id' => $file->id,
                                                                ]);
                                                            }
                                                        } elseif ($contract->type->type == 'contract_post_pay') {
                                                            $factor = $contract->last_invoice_at->diffInDays(Carbon::now()) / $contract->type->invoice_period;

                                                            /* @var Invoice $invoice */
                                                            $invoice = Invoice::create([
                                                                'user_id'        => $contract->user_id,
                                                                'type_id'        => $contract->type->invoice_type_id,
                                                                'contract_id'    => $contract->id,
                                                                'status'         => 'unpaid',
                                                                'reverse_charge' => $contract->user->reverseCharge,
                                                            ]);

                                                            $contract->positionLinks->each(function (ContractPosition $link) use ($invoice, $factor) {
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

                                                                InvoicePosition::create([
                                                                    'invoice_id'  => $invoice->id,
                                                                    'position_id' => $position->id,
                                                                    'started_at'  => $link->started_at,
                                                                    'ended_at'    => $link->ended_at,
                                                                ]);
                                                            });

                                                            $invoice->update([
                                                                'archived_at' => Carbon::now(),
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
                                                                'last_invoice_at' => Carbon::now(),
                                                            ]);
                                                        } elseif (
                                                            $contract->type->type == 'prepaid_auto' ||
                                                            $contract->type->type == 'prepaid_manual'
                                                        ) {
                                                            $factor = ($contract->type->invoice_period - $contract->last_invoice_at->diffInDays(Carbon::now())) / $contract->type->invoice_period;
                                                            $refund = $contract->grossSum * $factor;

                                                            PrepaidHistory::all([
                                                                'user_id'            => $contract->user_id,
                                                                'creator_user_id'    => null,
                                                                'contract_id'        => $contract->id,
                                                                'amount'             => $refund,
                                                                'transaction_method' => 'account',
                                                                'transaction_id'     => null,
                                                            ]);

                                                            /* @var Invoice $revokationInvoice */
                                                            $revokationInvoice = Invoice::create([
                                                                'user_id'        => $contract->user_id,
                                                                'type_id'        => $contract->type->invoice_type_id,
                                                                'contract_id'    => $contract->id,
                                                                'status'         => 'refund',
                                                                'reverse_charge' => $contract->user->reverseCharge,
                                                            ]);

                                                            $contract->positionLinks->each(function (ContractPosition $link) use ($revokationInvoice, $factor) {
                                                                /* @var Position $position */
                                                                $position = Position::create([
                                                                    'order_id'       => $link->position->order_id,
                                                                    'product_id'     => $link->position->product_id,
                                                                    'discount_id'    => $link->position->discount_id,
                                                                    'name'           => $link->position->name,
                                                                    'description'    => $link->position->description,
                                                                    'amount'         => $link->position->amount * $factor * (-1),
                                                                    'vat_percentage' => $link->position->vat_percentage,
                                                                    'quantity'       => $link->position->quantity,
                                                                ]);

                                                                InvoicePosition::create([
                                                                    'invoice_id'  => $revokationInvoice->id,
                                                                    'position_id' => $position->id,
                                                                    'started_at'  => $link->started_at,
                                                                    'ended_at'    => $link->ended_at,
                                                                ]);
                                                            });

                                                            $revokationInvoice->update([
                                                                'archived_at' => Carbon::now(),
                                                            ]);

                                                            try {
                                                                $sepaQr = ! empty($amount = $revokationInvoice->grossSum) && $amount > 0 ? Data::create()
                                                                ->setName(config('company.bank.owner'))
                                                                ->setIban(str_replace(' ', '', config('company.bank.iban')))
                                                                ->setRemittanceText($revokationInvoice->number)
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
                                                                'invoice' => $revokationInvoice,
                                                                'sepaQr'  => $sepaQr,
                                                            ]);

                                                            $content = $pdf->output();

                                                            /* @var File $file */
                                                            $file = File::create([
                                                                'user_id'   => null,
                                                                'folder_id' => null,
                                                                'name'      => $revokationInvoice->number . '.pdf',
                                                                'data'      => $content,
                                                                'mime'      => 'application/pdf',
                                                                'size'      => strlen($content),
                                                            ]);

                                                            $revokationInvoice->update([
                                                                'file_id' => $file->id,
                                                            ]);
                                                        }

                                                        $contract->update([
                                                            'cancelled_at'            => Carbon::now(),
                                                            'cancellation_revoked_at' => null,
                                                            'cancelled_to'            => Carbon::now(),
                                                        ]);
                                                    }
                                                } elseif ($reminder->dunning->cancel_contract_regular) {
                                                    if ($contract->status == 'started') {
                                                        if (
                                                            $contract->type->type == 'contract_pre_pay' ||
                                                            $contract->type->type == 'contract_post_pay'
                                                        ) {
                                                            if (
                                                                $contract->last_invoice_at->addDays($contract->type->invoice_period)->subDays($contract->type->cancellation_period)
                                                                    ->gte(Carbon::now())
                                                            ) {
                                                                $cancelledTo = $contract->last_invoice_at->addDays($contract->type->invoice_period);
                                                            } else {
                                                                $cancelledTo = $contract->last_invoice_at->addDays($contract->type->invoice_period * 2);
                                                            }
                                                        } elseif (
                                                            $contract->type->type == 'prepaid_auto' ||
                                                            $contract->type->type == 'prepaid_manual'
                                                        ) {
                                                            $cancelledTo = $contract->last_invoice_at->addDays($contract->type->invoice_period);
                                                        }

                                                        if (! empty($cancelledTo)) {
                                                            $contract->update([
                                                                'cancelled_at'            => Carbon::now(),
                                                                'cancellation_revoked_at' => null,
                                                                'cancelled_to'            => $cancelledTo,
                                                            ]);
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }

                            if (! empty($reminder)) {
                                $lastDunning = $dunning;
                            }
                        });
                }
            });

        // Archive and send unarchived reminders
        InvoiceReminder::whereNull('archived_at')
            ->where('sent', '=', false)
            ->each(function (InvoiceReminder $reminder) {
                $reminder->update([
                    'archived_at' => Carbon::now(),
                ]);

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
                    'sepaQr'   => $sepaQr,
                ]);

                $content = $pdf->output();

                $file = File::create([
                    'user_id'   => null,
                    'folder_id' => null,
                    'name'      => $reminder->number . '.pdf',
                    'data'      => $content,
                    'mime'      => 'application/pdf',
                    'size'      => strlen($content),
                ]);

                if ($file instanceof File) {
                    $reminder->update([
                        'file_id' => $file,
                    ]);

                    $reminder->sendReminderNotification();
                } else {
                    $reminder->update([
                        'archived_at' => null,
                    ]);
                }
            });

        // Send unsent but archived reminders
        InvoiceReminder::whereNotNull('archived_at')
            ->where('sent', '=', false)
            ->each(function (InvoiceReminder $reminder) {
                $reminder->sendReminderNotification();
            });
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
            'job:tenant:InvoiceReminders',
        ]);
    }

    /**
     * Set a unique identifier to avoid duplicate queuing of the same task.
     *
     * @return string
     */
    public function getUniqueIdentifier(): string
    {
        return 'invoice-reminders-' . $this->tenant_id;
    }
}
