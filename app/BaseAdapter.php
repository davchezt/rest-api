<?php
/**
 * @author      Raiza Rhamdan (Leonardo DaVchezt) <davchezt@gmail.com>
 * @copyright   Copyright (c), 2021 Raiza Rhamdan
 * @license		MIT public license
 */

namespace app;

defined("__DAVCHEZT") or die("{ \"response\" : \"error 403\"}");

class BaseAdapter
{
    private $table;

    public function __construct($tableName = '')
    {
        if ($tableName) {
            $this->table = \ORM::for_table($tableName);
        }
    }

    protected function orm()
    {
        return $this->table;
    }
}