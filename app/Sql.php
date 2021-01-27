<?php
/**
 * @author      Raiza Rhamdan (Leonardo DaVchezt) <davchezt@gmail.com>
 * @copyright   Copyright (c), 2021 Raiza Rhamdan
 * @license		MIT public license
 */

namespace app;

defined("__DAVCHEZT") or die("{ \"response\" : \"error 403\"}");

use app\Lib\R;
use app\Lib\Database;

/* Kelas: SQL
 * Kelas bantuan untuk DB Class <db.php>
 */
class SQL {
    private static $dbc;
    public static $db;
    public static $cnf;
    public static function open()
    {
        self::$cnf = R::get('config');
        self::$dbc = new Database(
            self::$cnf['db']['dsn'],
            self::$cnf['db']['dbu'],
            self::$cnf['db']['dbp']
        );
        self::$dbc->open();
        self::$db = self::$dbc->db();
    }
    public static function close()
    {
        self::$dbc->close();
        self::$db = null;
    }
}