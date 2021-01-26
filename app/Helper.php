<?php
/**
 * @author      Raiza Rhamdan (Leonardo DaVchezt) <davchezt@gmail.com>
 * @copyright   Copyright (c), 2014 Raiza Rhamdan
 * @license		MIT public license
 */

namespace app;

class Helper
{
    public static function timeNow($unix = false, $time = false)
    {
        if ($unix && $time) {
            return time() + (+7 * 60 * 60);
        }
        
        $userTimezone = new \DateTimeZone('Asia/Jakarta');
        $now = new \DateTime("now", $userTimezone);
    
        if ($unix) {
            return $now->format('U');
        }

        return $now->format("Y-m-d H:i:s \G\M\TO (T)");
    }
}