<?php
/**
 * @author      Raiza Rhamdan (Leonardo DaVchezt) <davchezt@gmail.com>
 * @copyright   Copyright (c), 2021 Raiza Rhamdan
 * @license		MIT public license
 */

namespace app;

use flight\Engine;

abstract class BaseRouter
{
    protected $app = null;
    protected $id = 1;

    public function __construct(Engine $app, $userId)
    {
        $this->app = $app;
        $this->id = $userId;

        $this->app->register('model', 'app\Model', array());
    }

    abstract public function init();
}