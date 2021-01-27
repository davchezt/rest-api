<?php
/**
 * @author      Raiza Rhamdan (Leonardo DaVchezt) <davchezt@gmail.com>
 * @copyright   Copyright (c), 2021 Raiza Rhamdan
 * @license		MIT public license
 */

namespace app\Router;

use flight\Engine;

use app\BaseRouter;

class Main extends BaseRouter
{
    public function init()
    {
        $this->app->route('/', function () {
            $this->app->json(['response' => ['data' => 'API Main']]);
        });
    }
}