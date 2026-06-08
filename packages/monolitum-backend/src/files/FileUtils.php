<?php

namespace monolitum\backend\files;

class FileUtils
{

    /**
     * https://stackoverflow.com/questions/15188033/human-readable-file-size
     * @param $bytes
     * @param $dec
     * @return string
     */
    public static function human_filesize($bytes, $dec = 2): string {

//        $size   = array('B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
//        $factor = floor((strlen($bytes) - 1) / 3);
//        if ($factor == 0) $dec = 0;
//
//        return sprintf("%.{$dec}f %s", $bytes / (1024 ** $factor), $size[$factor]);

        $i = floor(log($bytes) / log(1024));

        $sizes = array('B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');

        return sprintf('%.02F', $bytes / pow(1024, $i)) * 1 . ' ' . $sizes[$i];

    }

    /**
     * Returns a file size limit in bytes based on the PHP upload_max_filesize and post_max_size
     * https://stackoverflow.com/questions/13076480/php-get-actual-maximum-upload-size
     * @return float|int
     */
    public static function file_upload_max_size(): float|int
    {
        static $max_size = -1;

        if ($max_size < 0) {
            // Start with post_max_size.
            $post_max_size = self::parse_size(ini_get('post_max_size'));
            if ($post_max_size > 0) {
                $max_size = $post_max_size;
            }

            // If upload_max_size is less, then reduce. Except if upload_max_size is
            // zero, which indicates no limit.
            $upload_max = self::parse_size(ini_get('upload_max_filesize'));
            if ($upload_max > 0 && $upload_max < $max_size) {
                $max_size = $upload_max;
            }
        }
        return $max_size;
    }

    private static function parse_size($size) {
        $unit = preg_replace('/[^bkmgtpezy]/i', '', $size); // Remove the non-unit characters from the size.
        $size = preg_replace('/[^0-9\.]/', '', $size); // Remove the non-numeric characters from the size.
        if ($unit) {
            // Find the position of the unit in the ordered string which is the power of magnitude to multiply a kilobyte by.
            return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
        }
        else {
            return round($size);
        }
    }

}
