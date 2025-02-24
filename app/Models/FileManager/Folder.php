<?php

declare(strict_types=1);

namespace App\Models\FileManager;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class File.
 *
 * This class is the model for basic folder metadata.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @property int                $id
 * @property int|null           $user_id
 * @property int|null           $parent_id
 * @property string             $name
 * @property Carbon             $created_at
 * @property Carbon             $updated_at
 * @property Carbon             $deleted_at
 * @property User|null          $user
 * @property Folder|null        $parent
 * @property Collection<File>   $files
 * @property Collection<Folder> $children
 * @property string             $path
 * @property int                $folderSize
 */
class Folder extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'filemanager_folders';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var bool|string[]
     */
    protected $guarded = [
        'id',
    ];

    /**
     * Relation to parent folder.
     *
     * @return HasOne
     */
    public function parent(): HasOne
    {
        return $this->hasOne(Folder::class, 'id', 'parent_id');
    }

    /**
     * Relation to children folders.
     *
     * @return HasMany
     */
    public function children(): HasMany
    {
        return $this->hasMany(Folder::class, 'parent_id', 'id');
    }

    /**
     * Relation to children files.
     *
     * @return HasMany
     */
    public function files(): HasMany
    {
        return $this->hasMany(File::class, 'folder_id', 'id');
    }

    /**
     * Build path attribute.
     *
     * @return string
     */
    public function getPathAttribute(): string
    {
        if (! empty($this->parent)) {
            return $this->parent->path . '/' . $this->name;
        } else {
            return '/' . $this->name;
        }
    }

    /**
     * Build size attribute.
     *
     * @return string
     */
    public function getFolderSizeAttribute(): string
    {
        $size = 0;

        $this->files->pluck('size')->each(function (int $fileSize) use (&$size) {
            $size = $size + $fileSize;
        });

        $this->children->each(function (Folder $folder) use (&$size) {
            $size = $size + $folder->folderSize;
        });

        return $size;
    }

    /**
     * Recursively delete a folder. This includes the folder itself
     * but also all of its children. Deeper structures are also completely
     * removed.
     *
     * @return bool|null
     */
    public function recursiveDelete(): ?bool
    {
        $this->children()->each(function (Folder $folder) {
            $folder->recursiveDelete();
        });

        $this->files()->delete();

        return $this->delete();
    }
}
