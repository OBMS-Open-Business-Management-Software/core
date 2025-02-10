<?php

namespace App\Models;

use App\Traits\Encryptable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class ImapInbox
 *
 * This class is the model for basic IMAP inbox metadata.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @property int $id
 * @property string $host
 * @property string $username
 * @property string $password
 * @property string $port
 * @property string $protocol
 * @property string $validate_cert
 * @property string $folder
 * @property bool $delete_after_import
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 */
class ImapInbox extends Model
{
    use Encryptable;
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
        'delete_after_import' => 'boolean',
    ];

    /**
     * The attributes that are encryptable.
     *
     * @var string[]|bool
     */
    protected $encryptable = [
        'host',
        'username',
        'password',
        'port',
        'protocol',
        'validate_cert',
        'folder',
    ];
}
