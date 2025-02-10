<?php

namespace App\Models\Support\Category;

use App\Models\ImapInbox;
use App\Models\Support\SupportTicket;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class SupportCategory
 *
 * This class is the model for basic category metadata.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @property int $id
 * @property int $imap_inbox_id
 * @property string $name
 * @property string $description
 * @property string $email_address
 * @property string $email_name
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 *
 * @property ImapInbox|null $imapInbox
 * @property Collection<SupportTicket> $tickets
 * @property Collection<SupportCategoryAssignment> $assignments
 * @property string $answerEmailAddress
 * @property string $answerEmailName
 */
class SupportCategory extends Model
{
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
     * Relation to IMAP inbox.
     *
     * @return HasOne
     */
    public function imapInbox(): HasOne
    {
        return $this->hasOne(ImapInbox::class, 'id', 'imap_inbox_id');
    }

    /**
     * Relation to tickets.
     *
     * @return HasMany
     */
    public function tickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class, 'category_id', 'id');
    }

    /**
     * Relation to assignments.
     *
     * @return HasMany
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(SupportCategoryAssignment::class, 'category_id', 'id');
    }

    /**
     * Get ticket answer email address.
     *
     * @return string
     */
    public function getAnswerEmailAddressAttribute(): string
    {
        return ! empty($this->email_address) ? $this->email_address : (config('mail.support.address') ?? config('mail.from.address'));
    }

    /**
     * Get ticket answer email name.
     *
     * @return string
     */
    public function getAnswerEmailNameAttribute(): string
    {
        return ! empty($this->email_address) ? $this->email_address : (config('mail.support.name') ?? config('mail.from.name'));
    }
}
