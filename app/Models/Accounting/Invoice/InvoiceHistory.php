<?php

declare(strict_types=1);

namespace App\Models\Accounting\Invoice;

use App\Models\FileManager\File;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class InvoiceHistory.
 *
 * This class is the model for basic invoice history metadata.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @property int          $id
 * @property int|null     $user_id
 * @property int          $invoice_id
 * @property int          $file_id
 * @property string       $status
 * @property Carbon       $created_at
 * @property Carbon       $updated_at
 * @property Carbon       $deleted_at
 * @property User|null    $user
 * @property Invoice|null $invoice
 * @property File|null    $file
 */
class InvoiceHistory extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'invoice_history';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var bool|string[]
     */
    protected $guarded = [
        'id',
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
     * Relation to invoice.
     *
     * @return HasOne
     */
    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class, 'id', 'invoice_id');
    }

    /**
     * Relation to file.
     *
     * @return HasOne
     */
    public function file(): HasOne
    {
        return $this->hasOne(Invoice::class, 'id', 'invoice_id');
    }
}
