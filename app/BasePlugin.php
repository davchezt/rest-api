<?php
/**
 * @author      Raiza Rhamdan (Leonardo DaVchezt) <davchezt@gmail.com>
 * @copyright   Copyright (c), 2021 Raiza Rhamdan
 * @license		MIT public license
 */

namespace app;

defined("__DAVCHEZT") or die("{ \"response\" : \"error 403\"}");

use flight\Engine;

abstract class BasePlugin
{
    protected $app;

    public function __construct(Engine $app)
    {
        $this->app = $app;
    }

    abstract public function listen();
}