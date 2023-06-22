<?php
/**
 * @author      Raiza Rhamdan (Leonardo DaVchezt) <davchezt@gmail.com>
 * @copyright   Copyright (c), 2021 Raiza Rhamdan
 * @license		MIT public license
 */

namespace app;

defined("__DAVCHEZT") or die("{ \"response\" : \"error 403\"}");

class Logger
{
    private static $app;
    private static $path;

    public static function configure(AppEngine $app)
    {
        self::$app = $app;
        self::$path = self::$app->request()->path()  . '/logs';

        if (!is_dir(self::$path)) {
            mkdir(self::$path);
        }
    }

    public static function path($path)
    {
        self::$path = $path;
    }

    public static function write($message, $file = 'log')
    {
        $now = self::$app->helper()->timeNow(true, true);
        $file = rtrim(self::$path) . DIRECTORY_SEPARATOR . $file . '_' . gmdate('Y_m_d', $now) . '.log';
        $contenet = '[' . gmdate('m/d/Y h:i:s A', $now) . '] ' . $message . PHP_EOL;
        
        return (bool) file_put_contents($file, $contenet, FILE_APPEND);
    }
}