<?php

namespace App\Models\Accounting\Invoice;

use App\Emails\Accounting\AccountingInvoice;
use App\Models\Accounting\Contract\Contract;
use App\Models\Accounting\Position;
use App\Models\FileManager\File;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Error;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\App;
use SepaQr\Data;

/**
 * Class Invoice
 *
 * This class is the model for basic invoice metadata.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @property int $id
 * @property int $user_id
 * @property int|null $type_id
 * @property int|null $contract_id
 * @property int|null $file_id
 * @property int|null $original_id
 * @property string|null $name
 * @property string $status
 * @property bool $reverse_charge
 * @property bool $sent
 * @property Carbon|null $archived_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 *
 * @property User|null $user
 * @property InvoiceType|null $type
 * @property Contract|null $contract
 * @property File|null $file
 * @property Invoice|null $original
 * @property Invoice|null $refunded
 * @property Collection<InvoicePosition> $positionLinks
 * @property Collection<InvoiceReminder> $reminders
 * @property bool $archived
 * @property string $number
 * @property bool $overdue
 * @property float $netSum
 * @property float $grossSum
 * @property float $grossSumDiscounted
 * @property \Illuminate\Support\Collection $vatPositions
 * @property string $email
 */
class Invoice extends Model
{
    use Notifiable;
    use SoftDeletes;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var string[]|bool
     */
    protected $guarded = [
        'id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'reverse_charge' => 'bool',
        'sent' => 'bool',
        'archived' => 'bool',
        'archived_at' => 'datetime',
    ];

    /**
     * Relation to user.
     *
     * @return HasOne
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    /**
     * Relation to type.
     *
     * @return HasOne
     */
    public function type(): HasOne
    {
        return $this->hasOne(InvoiceType::class, 'id', 'type_id');
    }

    /**
     * Relation to contract.
     *
     * @return HasOne
     */
    public function contract(): HasOne
    {
        return $this->hasOne(Contract::class, 'id', 'contract_id');
    }

    /**
     * Relation to file.
     *
     * @return HasOne
     */
    public function file(): HasOne
    {
        return $this->hasOne(File::class, 'id', 'file_id');
    }

    /**
     * Relation to original invoice.
     *
     * @return HasOne
     */
    public function original(): HasOne
    {
        return $this->hasOne(Invoice::class, 'id', 'original_id');
    }

    /**
     * Relation to original invoice.
     *
     * @return HasOne
     */
    public function refunded(): HasOne
    {
        return $this->hasOne(Invoice::class, 'original_id', 'id');
    }

    /**
     * Relation to position links.
     *
     * @return HasMany
     */
    public function positionLinks(): HasMany
    {
        return $this->hasMany(InvoicePosition::class, 'invoice_id', 'id');
    }

    /**
     * Relation to reminders.
     *
     * @return HasMany
     */
    public function reminders(): HasMany
    {
        return $this->hasMany(InvoiceReminder::class, 'invoice_id', 'id');
    }

    /**
     * Relation to history.
     *
     * @return HasMany
     */
    public function history(): HasMany
    {
        return $this->hasMany(InvoiceHistory::class, 'invoice_id', 'id');
    }

    /**
     * Get email address to send invoices to.
     *
     * @return string
     */
    public function getEmailAttribute(): string
    {
        return $this->user->billingEmailAddress->email ?? $this->user->email;
    }

    /**
     * Get archive status.
     *
     * @return bool
     */
    public function getArchivedAttribute(): bool
    {
        return isset($this->archived_at) &&
            $this->status !== 'template';
    }

    /**
     * Get processed invoice number.
     *
     * @return string
     */
    public function getNumberAttribute(): string
    {
        return ! empty($this->name) ? $this->name : 'INV' . $this->created_at->format('Ymd') . $this->id;
    }

    /**
     * Identify if an invoice is overdue.
     *
     * @return bool
     */
    public function getOverdueAttribute(): bool
    {
        return $this->status == 'unpaid' &&
            $this->archived_at
                ->addDays($this->type->period)
                ->addDay()
                ->startOfDay()
                ->lte(Carbon::now());
    }

    /**
     * Get net sum for invoice.
     *
     * @return float
     */
    public function getNetSumAttribute(): float
    {
        $netSum = 0;

        $this->positionLinks->each(function (InvoicePosition $link) use (&$netSum) {
            $netSum += $link->position->netSum - $link->position->discountNetSum;
        });

        return (float) $netSum;
    }

    /**
     * Get gross sum for position.
     *
     * @return float
     */
    public function getGrossSumAttribute(): float
    {
        $grossSum = 0;

        $this->positionLinks->each(function (InvoicePosition $link) use (&$grossSum) {
            if (! $this->reverse_charge) {
                $grossSum += $link->position->grossSum - $link->position->discountGrossSum;
            } else {
                $grossSum += $link->position->netSum - $link->position->discountNetSum;
            }
        });

        return (float) $grossSum;
    }

    /**
     * Get gross sum for position.
     *
     * @return float
     */
    public function getGrossSumDiscountedAttribute(): float
    {
        $grossSum = 0;

        $this->positionLinks->each(function (InvoicePosition $link) use (&$grossSum) {
            $grossSum += $link->position->grossSum - $link->position->discountGrossSum;
        });

        if (
            ! empty($discount = $this->type->discount) &&
            Carbon::now()->lte($this->archived_at->addDays($discount->period)->endOfDay())
        ) {
            $grossSum = $grossSum * ((100 - $discount->percentage_amount) / 100);
        }

        return (float) $grossSum;
    }

    public function getVatPositionsAttribute(): \Illuminate\Support\Collection
    {
        $vat = collect();

        if (! $this->reverse_charge) {
            $this->positionLinks->each(function (InvoicePosition $link) use (&$vat) {
                if (! empty($position = $vat->pull($link->position->vat_percentage))) {
                    $vat->put($link->position->vat_percentage, $position + $link->position->vatSum - $link->position->discountVatSum);
                } else {
                    $vat->put($link->position->vat_percentage, $link->position->vatSum - $link->position->discountVatSum);
                }
            });
        }

        return $vat;
    }

    /**
     * Send reminder to customer via. mail.
     *
     * @return bool
     */
    public function sendInvoiceNotification(): bool
    {
        $status = false;

        try {
            $this->notify(new AccountingInvoice());

            $status = true;
        } catch (Exception|Error $exception) {
        }

        $this->update([
            'sent' => $status,
        ]);

        return $status;
    }

    /**
     * Refund the invoice.
     *
     * @param File|null $file
     * @param string|null $name
     * @param bool $silent
     *
     * @return Invoice
     */
    public function refund(?File $file = null, ?string $name = null, bool $silent = false): Invoice
    {
        /* @var Invoice $revokationInvoice */
        $revokationInvoice = Invoice::create([
            'user_id' => $this->user_id,
            'type_id' => $this->type_id,
            'contract_id' => $this->contract_id,
            'original_id' => $this->id,
            'name' => $name,
            'status' => 'refund',
            'reverse_charge' => $this->reverse_charge,
            'file_id' => ! empty($file) ? $file->id : null,
            'archived' => ! empty($file),
        ]);

        $this->positionLinks->each(function (InvoicePosition $link) use ($revokationInvoice) {
            /* @var Position $position */
            $position = Position::create([
                'order_id' => $link->position->order_id,
                'product_id' => $link->position->product_id,
                'discount_id' => $link->position->discount_id,
                'name' => $link->position->name,
                'description' => $link->position->description,
                'amount' => $link->position->amount * (-1),
                'vat_percentage' => $link->position->vat_percentage,
                'quantity' => $link->position->quantity,
            ]);

            InvoicePosition::create([
                'invoice_id' => $revokationInvoice->id,
                'position_id' => $position->id,
                'started_at' => $link->started_at,
                'ended_at' => $link->ended_at,
            ]);
        });

        $revokationInvoice->update([
            'archived_at' => Carbon::now(),
        ]);

        if (empty($file)) {
            try {
                $sepaQr = ! empty($amount = $revokationInvoice->grossSum) && $amount > 0 ? Data::create()
                    ->setName(config('company.bank.owner'))
                    ->setIban(str_replace(' ', '', config('company.bank.iban')))
                    ->setRemittanceText($revokationInvoice->number)
                    ->setAmount($amount) : null;
            } catch (Exception $exception) {
                $sepaQr = null;
            }

            $pdf = App::make('dompdf.wrapper')->loadView('pdf.invoice', [
                'invoice' => $revokationInvoice,
                'sepaQr' => $sepaQr,
            ]);

            $content = $pdf->output();

            /* @var File $file */
            $file = File::create([
                'user_id' => null,
                'folder_id' => null,
                'name' => $revokationInvoice->number . '.pdf',
                'data' => $content,
                'mime' => 'application/pdf',
                'size' => strlen($content),
            ]);

            $revokationInvoice->update([
                'file_id' => $file->id,
            ]);
        }

        $this->update([
            'status' => 'refunded',
        ]);

        if (! $silent) {
            $this->sendInvoiceNotification();
        }

        return $revokationInvoice;
    }
}
