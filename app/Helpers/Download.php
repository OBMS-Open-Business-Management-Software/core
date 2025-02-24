<?php

declare(strict_types=1);

namespace App\Helpers;

/**
 * Class Download
 *
 * This class is the helper for preparing downloads.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class Download
{
    private string $name;

    private string $data;

    private int $size;

    /**
     * Download constructor.
     *
     * @param string $fileName
     */
    public function __construct(string $fileName)
    {
        $this->name = sprintf('"%s"', addcslashes(basename($fileName), '"\\'));
    }

    /**
     * Get a new downloader instance which holds the target file
     * name.
     *
     * @param string $fileName
     *
     * @return Download
     */
    public static function prepare(string $fileName): Download
    {
        return new Download($fileName);
    }

    /**
     * Set the payload for the file download.
     *
     * @param string $data
     *
     * @return $this
     */
    public function data(string $data): Download
    {
        $this->data = $data;
        $this->size = strlen($data);

        return $this;
    }

    /**
     * Initiate a file download by setting the proper headers and appending
     * the payload itself to the response.
     */
    public function output()
    {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . $this->name);
        header('Content-Transfer-Encoding: binary');
        header('Connection: Keep-Alive');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . $this->size);

        exit($this->data);
    }
}
