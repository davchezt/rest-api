<?php
/**
 * @author      Raiza Rhamdan (Leonardo DaVchezt) <davchezt@gmail.com>
 * @copyright   Copyright (c), 2021 Raiza Rhamdan
 * @license		MIT public license
 */

namespace app;

defined("__DAVCHEZT") or die("{ \"response\" : \"error 403\"}");

abstract class BaseRouter
{
    protected $app;
    protected $id;
    protected $baseurl;

    public function __construct(AppEngine $app, $userId)
    {
        $this->app = $app;
        $this->id = $userId;
        $this->baseurl = $this->app->request()->scheme() . '://' . $this->app->request()->host();

        $this->app->register('model', 'app\Model', [$this->app]);
    }

    abstract public function init();
}
