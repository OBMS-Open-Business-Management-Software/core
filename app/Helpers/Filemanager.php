<?php

namespace App\Helpers;

use App\Models\FileManager\File;
use App\Models\FileManager\Folder;
use Illuminate\Database\Eloquent\Builder;

class Filemanager
{
    /**
     * Resolve a path to either a Folder or File object. If no matching object
     * could be found a null value will be returned.
     *
     * @param string $path
     * @param Folder|null $parent
     *
     * @return Folder|File|null
     */
    public static function resolve(string $path, ?Folder $parent = null)
    {
        $path = trim($path, '/');
        $segments = explode('/', $path);
        $segment = $segments[0];

        $folder = Folder::where('name', '=', $segment);
        $file = File::where('name', '=', $segment);

        if (! empty($parent)) {
            $folder = $folder->where('parent_id', '=', $parent->id);
            $file = $file->where('folder_id', '=', $parent->id);
        } else {
            $folder = $folder->where(function (Builder $builder) {
                return $builder->where('parent_id', '=', 0)
                    ->orWhereNull('parent_id');
            });

            $file = $file->where(function (Builder $builder) {
                return $builder->where('folder_id', '=', 0)
                    ->orWhereNull('folder_id');
            });
        }

        $folder = $folder->first();

        if (! empty($folder)) {
            if (count($segments) > 1) {
                unset($segments[0]);

                $path = '/' . implode('/', $segments);

                return Filemanager::resolve($path, $folder);
            } else {
                return $folder;
            }
        } elseif (! empty($file = $file->first())) {
            return $file;
        }

        return null;
    }
}
