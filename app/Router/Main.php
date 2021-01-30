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
        $this->app->plugin()->trigger('before', [$this]); // Router_Main_init_before
        $this->app->route('/', [$this, 'mainHTML']);
        $this->app->route('/v1', [$this, 'mainJSON']);
        $this->app->plugin()->trigger('after', [$this]); // Router_Main_init_after
    }

    public function mainHTML()
    {
        $version = file_get_contents($this->app->request()->path() . '/vendor/mikecao/flight/VERSION');
        $content = ['version' => 'Flight Framework (' . $version . ')'];

        $this->app->plugin()->trigger('init', [$this, &$content]); // Router_Main_mainHTML_init

        $this->app->render('index', $content);
    }

    public function mainJSON()
    {
        $response = ['data' => 'API version 1.0'];
        $this->app->plugin()->trigger('init', [$this, &$response]); // Router_Main_mainJSON_init

        $this->app->json(['response' => $response]);
    }
}