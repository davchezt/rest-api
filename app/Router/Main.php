<?php
/**
 * @author      Raiza Rhamdan (Leonardo DaVchezt) <davchezt@gmail.com>
 * @copyright   Copyright (c), 2021 Raiza Rhamdan
 * @license		MIT public license
 */

namespace app\Router;

defined("__DAVCHEZT") or die("{ \"response\" : \"error 403\"}");

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