<?php

namespace App\Models\FileManager;

use App\Models\Support\SupportTicketFile;
use App\Models\User;
use App\Traits\Encryptable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class File
 *
 * This class is the model for basic file metadata.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @property int $id
 * @property int|null $user_id
 * @property int|null $folder_id
 * @property string $name
 * @property string $data
 * @property string $mime
 * @property int $size
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 *
 * @property User|null $user
 * @property Folder|null $folder
 * @property Collection<SupportTicketFile> $ticketLinks
 */
class File extends Model
{
    use Encryptable;
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'filemanager_files';

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'data',
    ];

    /**
     * The attributes that are encryptable.
     *
     * @var string[]|bool
     */
    protected $encryptable = [
        'data',
    ];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var string[]|bool
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
     * Relation to folder.
     *
     * @return HasOne
     */
    public function folder(): HasOne
    {
        return $this->hasOne(Folder::class, 'id', 'folder_id');
    }

    /**
     * Relation to ticket links.
     *
     * @return HasMany
     */
    public function ticketLinks(): HasMany
    {
        return $this->hasMany(SupportTicketFile::class, 'file_id', 'id');
    }
}
