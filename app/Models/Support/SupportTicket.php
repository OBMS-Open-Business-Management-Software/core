<?php

namespace App\Models\Support;

use App\Emails\Support\SupportTicketClose;
use App\Emails\Support\SupportTicketDeescalation;
use App\Emails\Support\SupportTicketEscalation;
use App\Emails\Support\SupportTicketFileUpload;
use App\Emails\Support\SupportTicketHold;
use App\Emails\Support\SupportTicketLock;
use App\Emails\Support\SupportTicketNew;
use App\Emails\Support\SupportTicketPriority;
use App\Emails\Support\SupportTicketReopen;
use App\Emails\Support\SupportTicketUnhold;
use App\Emails\Support\SupportTicketUnlock;
use App\Models\FileManager\File;
use App\Models\Support\Category\SupportCategory;
use App\Models\Support\Run\SupportRun;
use App\Models\Support\Run\SupportRunHistory;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

/**
 * Class SupportTicket
 *
 * This class is the model for basic ticket metadata.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @property int $id
 * @property int $category_id
 * @property string $imap_name
 * @property string $imap_email
 * @property string $subject
 * @property string $status
 * @property string $priority
 * @property bool $hold
 * @property bool $escalated
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 *
 * @property Collection<SupportTicketAssignment> $assignments
 * @property Collection<SupportTicketMessage> $messages
 * @property Collection<SupportTicketHistory> $history
 * @property Collection<SupportTicketFile> $fileLinks
 * @property SupportCategory|null $category
 *
 * @property Collection<SupportTicketHistory> $runActions
 * @property Collection<SupportTicketHistory> $activeAssignments
 * @property Collection<File> $files
 * @property string|null $name
 * @property string|null $email
 * @property bool $external
 * @property string $answerEmailAddress
 * @property string $answerEmailName
 */
class SupportTicket extends Model
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
        'hold' => 'bool',
        'escalate' => 'bool',
    ];

    /**
     * Relation to assignments.
     *
     * @return HasMany
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(SupportTicketAssignment::class, 'ticket_id', 'id');
    }

    /**
     * Relation to messages.
     *
     * @return HasMany
     */
    public function messages(): HasMany
    {
        return $this->hasMany(SupportTicketMessage::class, 'ticket_id', 'id');
    }

    /**
     * Relation to file links.
     *
     * @return HasMany
     */
    public function fileLinks(): HasMany
    {
        return $this->hasMany(SupportTicketFile::class, 'ticket_id', 'id');
    }

    /**
     * Relation to category.
     *
     * @return HasOne
     */
    public function category(): HasOne
    {
        return $this->hasOne(SupportCategory::class, 'id', 'category_id');
    }

    /**
     * Relation to history.
     *
     * @return HasMany
     */
    public function history(): HasMany
    {
        return $this->hasMany(SupportTicketHistory::class, 'ticket_id', 'id');
    }

    /**
     * Relation to run history.
     *
     * @return HasMany
     */
    public function runHistory(): HasMany
    {
        return $this->hasMany(SupportRunHistory::class, 'ticket_id', 'id');
    }

    /**
     * Relation to run.
     *
     * @return HasMany
     */
    public function run(): HasMany
    {
        return $this->hasMany(SupportRun::class, 'ticket_id', 'id');
    }

    /**
     * Get value for computed property "runActions".
     *
     * @return Collection
     */
    public function getRunActionsAttribute(): Collection
    {
        return $this->history
            ->where('type', '=', 'run')
            ->sortByDesc('created_at');
    }

    /**
     * Get value for computed property "activeAssignments".
     *
     * @return Collection
     */
    public function getActiveAssignmentsAttribute(): Collection
    {
        $history = $this->history
            ->where('type', '=', 'assignment')
            ->where('action', '=', 'assign')
            ->sortByDesc('created_at');

        return (clone $history)
            ->reject(function (SupportTicketHistory $entry) use ($history) {
                $revertedHistory = $this->history
                    ->where('user_id', '=', $entry->user_id)
                    ->where('type', '=', 'assignment')
                    ->where('action', '=', 'unassign')
                    ->where('created_at', '>', $entry->created_at)
                    ->first();

                return ! empty($revertedHistory);
            });
    }

    /**
     * Get value for computed property "historyItems".
     * Get a combined list of messages and history items.
     *
     * @return Collection
     */
    public function getHistoryItemsAttribute(): Collection
    {
        return $this->messages
            ->concat($this->history)
            ->sortByDesc('created_at');
    }

    /**
     * Get a list of ticket files and write it to an attribute.
     *
     * @return Collection
     */
    public function getFilesAttribute(): Collection
    {
        return $this->fileLinks
            ->transform(function (SupportTicketFile $fileLink) {
                return $fileLink->file;
            })
            ->reject(function (?File $file) {
                return ! isset($file);
            });
    }

    /**
     * Get the associated email address to send notifications to.
     *
     * @return string|null
     */
    public function getEmailAttribute(): ?string
    {
        if (! empty($this->imap_email)) {
            return $this->imap_email;
        }

        if (
            ! empty(
                $assignment = $this->assignments
                    ->where('role', '=', 'customer')
                    ->first()
            ) &&
            ! empty($user = $assignment->user)
        ) {
            if (! empty($user->contactEmailAddress)) {
                return $user->contactEmailAddress->email ?? null;
            } else {
                return $user->email;
            }
        }

        return null;
    }

    /**
     * Get the associated name to use within notifications.
     *
     * @return string|null
     */
    public function getNameAttribute(): ?string
    {
        if (! empty($this->imap_name)) {
            return $this->imap_name;
        }

        if (
            ! empty(
                $assignment = $this->assignments
                    ->where('role', '=', 'customer')
                    ->first()
            ) &&
            ! empty($user = $assignment->user)
        ) {
            return $user->realName;
        }

        return null;
    }

    /**
     * Identify if the support ticket has been added from an external source.
     *
     * @return bool
     */
    public function getExternalAttribute(): bool
    {
        return ! empty($this->imap_email) &&
            ! empty($this->imap_name);
    }

    /**
     * Send the email creation notification.
     *
     * @return void
     */
    public function sendEmailCreationNotification(): void
    {
        try {
            $this->notify(new SupportTicketNew());
        } catch (Exception $exception) {
        }
    }

    /**
     * Send the email lock notification.
     *
     * @return void
     */
    public function sendEmailLockNotification(): void
    {
        try {
            $this->notify(new SupportTicketLock());
        } catch (Exception $exception) {
        }
    }

    /**
     * Send the email unlock notification.
     *
     * @return void
     */
    public function sendEmailUnlockNotification(): void
    {
        try {
            $this->notify(new SupportTicketUnlock());
        } catch (Exception $exception) {
        }
    }

    /**
     * Send the email escalation notification.
     *
     * @return void
     */
    public function sendEmailEscalationNotification(): void
    {
        try {
            $this->notify(new SupportTicketEscalation());
        } catch (Exception $exception) {
        }
    }

    /**
     * Send the email deescalation notification.
     *
     * @return void
     */
    public function sendEmailDeescalationNotification(): void
    {
        try {
            $this->notify(new SupportTicketDeescalation());
        } catch (Exception $exception) {
        }
    }

    /**
     * Send the email hold notification.
     *
     * @return void
     */
    public function sendEmailHoldNotification(): void
    {
        try {
            $this->notify(new SupportTicketHold());
        } catch (Exception $exception) {
        }
    }

    /**
     * Send the email unhold notification.
     *
     * @return void
     */
    public function sendEmailUnholdNotification(): void
    {
        try {
            $this->notify(new SupportTicketUnhold());
        } catch (Exception $exception) {
        }
    }

    /**
     * Send the email file upload notification.
     *
     * @return void
     */
    public function sendEmailFileUploadNotification(): void
    {
        try {
            $this->notify(new SupportTicketFileUpload());
        } catch (Exception $exception) {
        }
    }

    /**
     * Send the email hold notification.
     *
     * @return void
     */
    public function sendEmailCloseNotification(): void
    {
        try {
            $this->notify(new SupportTicketClose());
        } catch (Exception $exception) {
        }
    }

    /**
     * Send the email hold notification.
     *
     * @return void
     */
    public function sendEmailReopenNotification(): void
    {
        try {
            $this->notify(new SupportTicketReopen());
        } catch (Exception $exception) {
        }
    }

    /**
     * Send the email hold notification.
     *
     * @return void
     */
    public function sendEmailPriorityNotification(): void
    {
        try {
            $this->notify(new SupportTicketPriority());
        } catch (Exception $exception) {
        }
    }

    /**
     * Get ticket answer email address.
     *
     * @return string
     */
    public function getAnswerEmailAddressAttribute(): string
    {
        return ! empty($this->category) ? $this->category->answerEmailAddress : config('mail.from.address');
    }

    /**
     * Get ticket answer email name.
     *
     * @return string
     */
    public function getAnswerEmailNameAttribute(): string
    {
        return ! empty($this->category) ? $this->category->answerEmailName : config('mail.from.name');
    }
}
