<?php

declare(strict_types=1);

namespace App\Webdav;

use App\Helpers\Filemanager;
use App\Models\FileManager\File as FilemanagerFile;
use Exception;
use Sabre\DAV\Exception\NotFound;
use Sabre\DAV\File as Framework;

/**
 * Class File.
 */
class File extends Framework
{
    private string $myPath;

    /**
     * File constructor.
     *
     * @param $myPath
     */
    public function __construct($myPath)
    {
        $this->myPath = $myPath;
    }

    /**
     * Delete file.
     *
     * @throws NotFound
     */
    public function delete(): void
    {
        if (empty($fileOrFolder = Filemanager::resolve($this->myPath))) {
            throw new NotFound('The file with name: ' . $this->myPath . ' could not be found');
        }

        $fileOrFolder->delete();
    }

    /**
     * Create or update file.
     *
     * @param resource|string $data
     *
     * @throws NotFound
     */
    public function put($data): void
    {
        if (
            empty($fileOrFolder = Filemanager::resolve($this->myPath)) ||
            ! ($fileOrFolder instanceof FilemanagerFile)
        ) {
            throw new NotFound('The file with name: ' . $this->myPath . ' could not be found');
        }

        if (! empty($data)) {
            $data = stream_get_contents($data, -1);
        }

        $fileOrFolder->update([
            'data' => $data,
        ]);
    }

    /**
     * Get name of file.
     *
     * @return string
     */
    public function getName()
    {
        return basename($this->myPath);
    }

    /**
     * Get file key.
     *
     * @return string
     */
    public function getKey(): string
    {
        return $this->myPath;
    }

    /**
     * Get file.
     *
     * @throws NotFound
     *
     * @return string
     */
    public function get(): string
    {
        if (
            empty($fileOrFolder = Filemanager::resolve($this->myPath)) ||
            ! ($fileOrFolder instanceof FilemanagerFile)
        ) {
            throw new NotFound('The file with name: ' . $this->myPath . ' could not be found');
        }

        return $fileOrFolder->data;
    }

    /**
     * Get file size.
     *
     * @throws NotFound
     *
     * @return int
     */
    public function getSize(): int
    {
        if (
            empty($fileOrFolder = Filemanager::resolve($this->myPath)) ||
            ! ($fileOrFolder instanceof FilemanagerFile)
        ) {
            throw new NotFound('The file with name: ' . $this->myPath . ' could not be found');
        }

        return $fileOrFolder->size;
    }

    /**
     * Get file hash.
     *
     * @throws NotFound
     *
     * @return string
     */
    public function getETag()
    {
        if (
            empty($fileOrFolder = Filemanager::resolve($this->myPath)) ||
            ! ($fileOrFolder instanceof FilemanagerFile)
        ) {
            throw new NotFound('The file with name: ' . $this->myPath . ' could not be found');
        }

        return '"' . md5($fileOrFolder->data) . '"';
    }

    /**
     * Get file last modification timestamp.
     *
     * @throws NotFound
     *
     * @return int
     */
    public function getLastModified(): int
    {
        if (
            empty($fileOrFolder = Filemanager::resolve($this->myPath)) ||
            ! ($fileOrFolder instanceof FilemanagerFile)
        ) {
            throw new NotFound('The file with name: ' . $this->myPath . ' could not be found');
        }

        return ! empty($fileOrFolder->updated_at) ? (int) $fileOrFolder->updated_at->timestamp : (int) $fileOrFolder->created_at->timestamp;
    }

    /**
     * Set file name.
     *
     * @param string $name
     *
     * @throws Exception|NotFound
     */
    public function setName($name)
    {
        if (
            empty($fileOrFolder = Filemanager::resolve($this->myPath)) ||
            ! ($fileOrFolder instanceof FilemanagerFile)
        ) {
            throw new NotFound('The file with name: ' . $this->myPath . ' could not be found');
        }

        if (
            FilemanagerFile::where('name', '=', $name)
                ->where('folder_id', '=', $fileOrFolder->folder_id)
                ->where('id', '!=', $fileOrFolder->id)
                ->exists()
        ) {
            throw new Exception('The file with name: ' . $name . ' already exists in folder: ' . $this->myPath);
        }

        $fileOrFolder->update([
            'name' => $name,
        ]);
    }
}
