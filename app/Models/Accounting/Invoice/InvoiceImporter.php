<?php

declare(strict_types=1);

namespace App\Models\Accounting\Invoice;

use App\Models\ImapInbox;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class SupportCategory.
 *
 * This class is the model for basic invoice importer metadata.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @property int            $id
 * @property int            $imap_inbox_id
 * @property string         $name
 * @property string         $description
 * @property Carbon         $created_at
 * @property Carbon         $updated_at
 * @property Carbon         $deleted_at
 * @property ImapInbox|null $imapInbox
 */
class InvoiceImporter extends Model
{
    use SoftDeletes;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var bool|string[]
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
     * Relation to log entries.
     *
     * @return HasMany
     */
    public function log(): HasMany
    {
        return $this->hasMany(InvoiceImporterHistory::class, 'importer_id', 'id');
    }
}
