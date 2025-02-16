<?php

namespace App\Helpers;

use Illuminate\Support\Collection;
use Throwable;

/**
 * Class IMAP.
 *
 * This class is the helper for handling IMAP inboxes.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class IMAP
{
    /**
     * Get IMAP inbox content. The contents can also be deleted by setting
     * $delete to true.
     *
     * @param string $host
     * @param string $username
     * @param string $password
     * @param int $port
     * @param string $protocol
     * @param false $validate_certificate
     * @param string $folder
     * @param bool $delete
     *
     * @return Collection
     */
    public static function getInbox(string $host, string $username, string $password, int $port = 143, string $protocol = 'tls', bool $validate_certificate = false, string $folder = 'INBOX', bool $delete = false): Collection
    {
        try {
            if (!empty($protocol) && $protocol !== "none") {
                $options = "";
                if ($validate_certificate === false) {
                    $options = $options . "/novalidate-cert";
                }

                $connection = "{" . $host . ":" . $port . "/imap/" . $protocol . $options . "}" . $folder;
            } else {
                $connection = "{" . $host . ":" . $port . "}" . $folder;
            }

            $imap = imap_open($connection, $username, $password);
            if ($imap) {
                $MC = imap_check($imap);

                $result = imap_fetch_overview($imap, "1:{$MC->Nmsgs}", 0);

                if (!empty($result)) {
                    $results = [];
                    foreach ($result as $overview) {
                        if (!$overview->deleted) {
                            $header = imap_headerinfo($imap, $overview->msgno);
                            $message = IMAP::getBody($overview->uid, $imap);
                            $structure = imap_fetchstructure($imap, $overview->msgno);

                            $files = [];
                            if (isset($structure->parts) && count($structure->parts)) {
                                for ($i = 0; $i < count($structure->parts); $i++) {
                                    $files[$i] = [
                                        'is_attachment' => false,
                                        'filename' => '',
                                        'name' => '',
                                        'attachment' => ''
                                    ];

                                    if ($structure->parts[$i]->ifdparameters) {
                                        foreach ($structure->parts[$i]->dparameters as $object) {
                                            if (strtolower($object->attribute) == 'filename') {
                                                $files[$i]['is_attachment'] = true;
                                                $files[$i]['filename'] = $object->value;
                                            }
                                        }
                                    }

                                    if ($structure->parts[$i]->ifparameters) {
                                        foreach ($structure->parts[$i]->parameters as $object) {
                                            if (strtolower($object->attribute) == 'name') {
                                                $files[$i]['is_attachment'] = true;
                                                $files[$i]['name'] = $object->value;
                                            }
                                        }
                                    }

                                    if ($files[$i]['is_attachment']) {
                                        $files[$i]['attachment'] = imap_fetchbody($imap, $overview->msgno, $i + 1);
                                        if ($structure->parts[$i]->encoding == 3) {
                                            $files[$i]['attachment'] = base64_decode($files[$i]['attachment']);
                                        } elseif ($structure->parts[$i]->encoding == 4) {
                                            $files[$i]['attachment'] = quoted_printable_decode($files[$i]['attachment']);
                                        }
                                    }
                                }
                            }

                            $results[] = (object) [
                                "subject" => $overview->subject,
                                "from" => $header->sender[0]->mailbox . "@" . $header->sender[0]->host,
                                "from_name" => $header->sender[0]->personal,
                                "to" => $header->to[0]->mailbox . "@" . $header->to[0]->host,
                                "message" => Tags::stripJavascript($message),
                                "attachments" => $files
                            ];

                            if ($delete) {
                                imap_delete($imap, $overview->msgno);
                            }
                        }
                    }
                    imap_expunge($imap);
                    imap_close($imap);
                } else {
                    $results = [];
                }
            } else {
                $results = [];
            }
        } catch (Throwable $throwable) {
            $results = [];
        }

        return collect($results);
    }

    /**
     * Get IMAP message body.
     *
     * @param $uid
     * @param $imap
     *
     * @return false|string
     */
    private static function getBody($uid, $imap)
    {
        $body = IMAP::getPart($imap, $uid, "TEXT/HTML");

        if ($body == '') {
            $body = IMAP::getPart($imap, $uid, "TEXT/PLAIN");
        }

        return $body;
    }

    /**
     * Get IMAP message part.
     *
     * @param $imap
     * @param $uid
     * @param $mimetype
     * @param false $structure
     * @param false $partNumber
     *
     * @return false|string
     */
    private static function getPart($imap, $uid, $mimetype, $structure = false, $partNumber = false)
    {
        if (! $structure) {
            $structure = imap_fetchstructure($imap, $uid, FT_UID);
        }

        if ($structure) {
            if ($mimetype == IMAP::getMimeType($structure)) {
                if (!$partNumber) {
                    $partNumber = 1;
                }

                $text = imap_fetchbody($imap, $uid, $partNumber, FT_UID);

                switch ($structure->encoding) {
                    case 3:
                        return imap_base64($text);
                    case 4:
                        return imap_qprint($text);
                    default:
                        return $text;
                }
            }

            if ($structure->type == 1) {
                foreach ($structure->parts as $index => $subStruct) {
                    $prefix = "";

                    if ($partNumber) {
                        $prefix = $partNumber . ".";
                    }

                    $data = IMAP::getPart($imap, $uid, $mimetype, $subStruct, $prefix . ($index + 1));

                    if ($data) {
                        return $data;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Get IMAP message attachment mime type.
     *
     * @param $structure
     *
     * @return string
     */
    private static function getMimeType($structure)
    {
        $primaryMimetype = [
            'TEXT',
            'MULTIPART',
            'MESSAGE',
            'APPLICATION',
            'AUDIO',
            'IMAGE',
            'VIDEO',
            'OTHER',
        ];

        if ($structure->subtype) {
            return $primaryMimetype[(int) $structure->type] . '/' . $structure->subtype;
        }

        return 'TEXT/PLAIN';
    }
}
