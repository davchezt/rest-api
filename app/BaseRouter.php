<?php
/**
 * @author      Raiza Rhamdan (Leonardo DaVchezt) <davchezt@gmail.com>
 * @copyright   Copyright (c), 2021 Raiza Rhamdan
 * @license		MIT public license
 */

namespace app;

defined("__DAVCHEZT") or die("{ \"response\" : \"error 403\"}");

use flight\Engine;

abstract class BaseRouter
{
    protected $app = null;
    protected $id = 1;

    public function __construct(Engine $app, $userId)
    {
        $this->app = $app;
        $this->id = $userId;

        $this->app->register('model', 'app\Model');
    }

    abstract public function init();
}
