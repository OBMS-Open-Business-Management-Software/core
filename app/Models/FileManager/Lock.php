<?php

declare(strict_types=1);

namespace App\Models\FileManager;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Lock
 *
 * This class is the model for basic WebDAV lock metadata.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @property int    $id
 * @property string $owner
 * @property int    $timeout
 * @property int    $created
 * @property string $token
 * @property string $scope
 * @property int    $depth
 * @property string $uri
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 */
class Lock extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'filemanager_locks';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var bool|string[]
     */
    protected $guarded = [
        'id',
    ];
}
