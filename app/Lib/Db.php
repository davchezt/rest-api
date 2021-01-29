<?php
/**
 * @author      Raiza Rhamdan (Leonardo DaVchezt) <davchezt@gmail.com>
 * @copyright   Copyright (c), 2021 Raiza Rhamdan
 * @license		MIT public license
 */

namespace app\Lib;

defined("__DAVCHEZT") or die("{ \"response\" : \"error 403\"}");

class Db extends \ORM
{
    public function __construct($table_name = '', $data = array(), $connection_name = self::DEFAULT_CONNECTION)
    {
        parent::__construct($table_name, $data, $connection_name);
    }
}
