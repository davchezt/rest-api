<?php
/**
 * @author      Raiza Rhamdan (Leonardo DaVchezt) <davchezt@gmail.com>
 * @copyright   Copyright (c), 2021 Raiza Rhamdan
 * @license		MIT public license
 */

namespace app;

defined("__DAVCHEZT") or die("{ \"response\" : \"error 403\"}");

use app\Helper;

class Logger
{
    private static $dir;

    public static function configure($dir)
    {
        self::$dir = $dir;
    }

    public static function write($message, $file = 'log')
    {
        $now = Helper::timeNow(true, true);
        $file = rtrim(self::$dir) . DIRECTORY_SEPARATOR . $file . '_' . gmdate('Y_m_d', $now) . '.log';
        $contenet = '[' . gmdate('m/d/Y h:i:s A', $now) . '] ' . $message . PHP_EOL;
        
        // echo $file;
        return (bool) file_put_contents($file, $contenet, FILE_APPEND);
    }
}