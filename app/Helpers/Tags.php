<?php

declare(strict_types=1);

namespace App\Helpers;

/**
 * Class Tags.
 *
 * This class is the helper for handling string HTML tag stripping.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class Tags
{
    /**
     * Strip javascript tags from HTML string.
     *
     * @param string $string
     *
     * @return array|string|string[]|null
     */
    public static function stripJavascript(string $string): string
    {
        return preg_replace('#<script(.*?)>(.*?)</script>#is', '', $string);
    }

    /**
     * Strip + tags from email address strings.
     *
     * @param string $email
     *
     * @return string
     */
    public static function stripEmailTags(string $email): string
    {
        $parts = explode('@', $email);

        if (count($parts) == 2) {
            $inbox  = $parts[0];
            $domain = $parts[1];

            $parts = explode('+', $inbox);

            if (count($parts) == 2) {
                return $parts[0] . '@' . $domain;
            }
        }

        return $email;
    }

    /**
     * Get string between two strings as delimiter.
     *
     * @param string $string
     * @param string $start
     * @param string $end
     *
     * @return string
     */
    public static function getStringBetween(string $string, string $start, string $end): string
    {
        $string = ' ' . $string . ' ';
        $ini    = strpos($string, $start);

        if ($ini == 0) {
            return false;
        }

        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;

        return substr($string, $ini, $len);
    }
}
