<?php

declare(strict_types=1);

namespace App\Webdav;

use App\Helpers\Filemanager;
use App\Models\FileManager\File;
use App\Models\FileManager\Folder;
use App\Webdav\Directory as WebdavDirectory;
use App\Webdav\File as WebdavFile;
use Exception;
use finfo;
use Illuminate\Support\Facades\Auth;
use Sabre\DAV\Collection;
use Sabre\DAV\Exception\NotFound;

/**
 * Class Directory.
 */
class Directory extends Collection
{
    private string $myPath;

    /**
     * WebDAV_Directory constructor.
     *
     * @param string $myPath
     */
    public function __construct(string $myPath = '')
    {
        $this->myPath = $myPath;
    }

    /**
     * Get child elements.
     *
     * @return array
     */
    public function getChildren(): array
    {
        $fileOrFolder = Filemanager::resolve($this->myPath);

        if ($fileOrFolder instanceof Folder) {
            return $fileOrFolder->children->transform(function (Folder $folder) {
                return $this->getChild($folder->name);
            })->merge($fileOrFolder->files->transform(function (File $file) {
                return $this->getChild($file->name);
            }))->toArray();
        }

        return [];
    }

    /**
     * Get child element.
     *
     * @param string $name
     *
     * @throws NotFound
     *
     * @return WebdavDirectory|WebdavFile
     */
    public function getChild($name)
    {
        $path = ! empty($this->myPath) ? $this->myPath . '/' . $name : $name;

        if (empty($fileOrFolder = Filemanager::resolve($path))) {
            throw new NotFound('The file with name: ' . $name . ' could not be found');
        }

        if ($fileOrFolder instanceof Folder) {
            return new WebdavDirectory($path);
        } elseif ($fileOrFolder instanceof File) {
            return new WebdavFile($path);
        }

        throw new NotFound('The file or folder with name: ' . $name . ' could not be found');
    }

    /**
     * Check if child exists.
     *
     * @param string $name
     *
     * @return bool
     */
    public function childExists($name): bool
    {
        $path = ! empty($this->myPath) ? $this->myPath . '/' . $name : $name;

        return empty(Filemanager::resolve($path));
    }

    /**
     * Get folder name.
     *
     * @return string
     */
    public function getName(): string
    {
        return basename($this->myPath);
    }

    /**
     * Create file in folder.
     *
     * @param string $name
     * @param mixed  $data
     *
     * @throws Exception|NotFound
     */
    public function createFile($name, $data = null): void
    {
        $fileOrFolder = Filemanager::resolve($this->myPath);

        if (! empty($data)) {
            $data     = stream_get_contents($data, -1);
            $fileInfo = new finfo(FILEINFO_MIME_TYPE);
            $mimeType = $fileInfo->buffer($data);
        } else {
            $mimeType = 'application/octet-stream';
        }

        if (! ($fileOrFolder instanceof Folder)) {
            throw new NotFound('The folder with name: ' . $this->myPath . ' could not be found');
        }

        if ($fileOrFolder->files->where('name', '=', $name)) {
            throw new Exception('The file with name: ' . $name . ' already exists in folder: ' . $this->myPath);
        }

        File::create([
            'user_id'   => ! empty($request->private) ? Auth::id() : null,
            'folder_id' => $fileOrFolder->id ?? null,
            'name'      => $name,
            'data'      => $data,
            'mime'      => $mimeType,
            'size'      => strlen($data) ?? 0,
        ]);
    }

    /**
     * Create folder in folder.
     *
     * @param string $name
     */
    public function createDirectory($name): void
    {
        $fileOrFolder = Filemanager::resolve($this->myPath);

        Folder::updateOrCreate([
            'name'      => $name,
            'parent_id' => $fileOrFolder->id ?? 0,
        ]);
    }

    /**
     * Delete folder.
     *
     * @throws NotFound
     */
    public function delete(): void
    {
        if (
            empty($fileOrFolder = Filemanager::resolve($this->myPath)) ||
            ! ($fileOrFolder instanceof Folder)
        ) {
            throw new NotFound('The folder with name: ' . $this->myPath . ' could not be found');
        }

        $fileOrFolder->recursiveDelete();
    }

    /**
     * Set folder name.
     *
     * @param string $name
     *
     * @throws Exception|NotFound
     */
    public function setName($name): void
    {
        if (
            empty($fileOrFolder = Filemanager::resolve($this->myPath)) ||
            ! ($fileOrFolder instanceof Folder)
        ) {
            throw new NotFound('The folder with name: ' . $this->myPath . ' could not be found');
        }

        if (
            Folder::where('name', '=', $name)
                ->where('parent_id', '=', $fileOrFolder->parent_id)
                ->where('id', '!=', $fileOrFolder->id)
                ->exists()
        ) {
            throw new Exception('The folder with name: ' . $name . ' already exists in folder: ' . $this->myPath);
        }

        $fileOrFolder->update([
            'name' => $name,
        ]);
    }
}
