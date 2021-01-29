<?php
/**
 * @author      Raiza Rhamdan (Leonardo DaVchezt) <davchezt@gmail.com>
 * @copyright   Copyright (c), 2021 Raiza Rhamdan
 * @license		MIT public license
 */

namespace app;

defined("__DAVCHEZT") or die("{ \"response\" : \"error 403\"}");

use flight\Engine;

class BaseAdapter
{
    protected $app;
    private $table;

    public function __construct($tableName)
    {
        $this->table = $tableName;
    }

    public function setup(Engine $app)
    {
        $this->app = $app;
    }

    protected function orm($tableName = '')
    {
        return $this->app->db()->for_table($tableName ? $tableName : $this->table);
    }

    protected function raw($query)
    {
        return $this->app->db()->raw_execute($query);
    }
}