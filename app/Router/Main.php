<?php
/**
 * @author      Raiza Rhamdan (Leonardo DaVchezt) <davchezt@gmail.com>
 * @copyright   Copyright (c), 2021 Raiza Rhamdan
 * @license		MIT public license
 */

namespace app\Router;

defined("__DAVCHEZT") or die("{ \"response\" : \"error 403\"}");

use app\BaseRouter;
use app\Lib\R;

class Main extends BaseRouter
{
    public function init()
    {
        $this->app->route('/', function () {
            // $this->app->json(['response' => ['data' => 'API Main']]);

            $version = file_get_contents(R::get('path') . '/vendor/mikecao/flight/VERSION');
            $this->app->render('index', array('version' => 'Flight framework (' . $version . ')'));
        });
    }
}