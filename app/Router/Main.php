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
        $this->app->route('/', [$this, 'mainHTML']);
        $this->app->route('/v1', [$this, 'mainJSON']);
    }

    public function mainHTML()
    {
        $version = file_get_contents($this->app->request()->path() . '/vendor/mikecao/flight/VERSION');
        $this->app->render('index', array('version' => 'Flight Framework (' . $version . ')'));
    }

    public function mainJSON()
    {
        $this->app->json(['response' => ['data' => 'API version 1.0']]);
    }
}