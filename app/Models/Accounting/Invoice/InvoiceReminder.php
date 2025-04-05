<?php

declare(strict_types=1);

namespace App\Models\Accounting\Invoice;

use App\Emails\Accounting\AccountingReminder;
use App\Helpers\NumberRanges;
use App\Models\FileManager\File;
use Carbon\Carbon;
use Error;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

/**
 * Class InvoiceReminder.
 *
 * This class is the model for basic invoice reminder metadata.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @property int                 $id
 * @property int                 $invoice_id
 * @property int                 $dunning_id
 * @property int|null            $file_id
 * @property bool                $sent
 * @property Carbon              $due_at
 * @property Carbon              $archived_at
 * @property Carbon              $created_at
 * @property Carbon              $updated_at
 * @property Carbon              $deleted_at
 * @property Invoice|null        $invoice
 * @property InvoiceDunning|null $dunning
 * @property File|null           $file
 * @property string              $number
 */
class InvoiceReminder extends Model
{
    use SoftDeletes;
    use Notifiable;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var bool|string[]
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
        'sent'        => 'boolean',
        'due_at'      => 'datetime',
        'archived_at' => 'datetime',
    ];

    /**
     * Relation to invoice.
     *
     * @return HasOne
     */
    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class, 'id', 'invoice_id');
    }

    /**
     * Relation to dunning type.
     *
     * @return HasOne
     */
    public function dunning(): HasOne
    {
        return $this->hasOne(InvoiceDunning::class, 'id', 'dunning_id');
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
     * Get email address to send reminders to.
     *
     * @return string
     */
    public function getEmailAttribute(): string
    {
        return $this->invoice->user->billingEmailAddress->email ?? $this->invoice->user->email;
    }

    /**
     * Get processed reminder number.
     *
     * @return string
     */
    public function getNumberAttribute(): string
    {
        return NumberRanges::getNumber(self::class, $this);
    }

    /**
     * Send reminder to customer via. mail.
     *
     * @return bool
     */
    public function sendReminderNotification(): bool
    {
        $status = false;

        try {
            $this->notify(new AccountingReminder());

            $status = true;
        } catch (Exception | Error $exception) {
        }

        $this->update([
            'sent' => $status,
        ]);

        return $status;
    }
}
