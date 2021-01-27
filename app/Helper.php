<?php
/**
 * @author      Raiza Rhamdan (Leonardo DaVchezt) <davchezt@gmail.com>
 * @copyright   Copyright (c), 2021 Raiza Rhamdan
 * @license		MIT public license
 */

namespace app;

defined("__DAVCHEZT") or die("{ \"response\" : \"error 403\"}");

class Helper
{
    public static function timeNow($unix = false, $time = false, $format = null)
    {
        if ($unix && $time) {
            return time() + (+7 * 60 * 60);
        }
        
        $userTimezone = new \DateTimeZone("Asia/Jakarta");
        $now = new \DateTime("now", $userTimezone);
    
        if ($unix) {
            return $now->format('U');
        }

        if ($format) {
            return $now->format($format);
        }

        return $now->format("Y-m-d H:i:s \G\M\TO (T)");
    }

    public static function dateFuture($modify, $format = "Y-m-d H:i:s")
    {
        $userTimezone = new \DateTimeZone("Asia/Jakarta");
        $date = new \DateTime("now", $userTimezone);
        $date->modify("+" . $modify);

        return $date->format($format);
    }

    public static function datePast($modify, $format = "Y-m-d H:i:s")
    {
        $userTimezone = new \DateTimeZone("Asia/Jakarta");
        $date = new \DateTime("now", $userTimezone);
        $date->modify("-" . $modify);

        return $date->format($format);
    }

    public static function listingDir($path)
    {
        if (empty($path)) $path = ".";
    
        $fileList = $directoryList = array();
        $ignoreList = array(".", "..", ".htaccess");
        if (is_dir($path)) {
            $directoryHandle  = opendir($path);
            while (false !== ($file = readdir($directoryHandle)))
            {
                if (in_array($file, $ignoreList)) continue;
                if (is_dir($path . "/" . $file)) {
                    $directoryList["dirs"][] = array(
                        "file" => $file,
                        "location" => $path,
                        "type" => "dir"
                    );
                }
                else {
                    $fileList["files"][] = array(
                        "file" => $file,
                        "location" => $path,
                        "type" => "file"
                    );
                }
            }
            closedir($directoryHandle);
        }
        natcasesort($directoryList);
        natcasesort($fileList);
        $finalList = array_merge($directoryList, $fileList);
    
        return $finalList;
    }
}
