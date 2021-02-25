<?php
/**
 * @author      Raiza Rhamdan (Leonardo DaVchezt) <davchezt@gmail.com>
 * @copyright   Copyright (c), 2021 Raiza Rhamdan
 * @license		MIT public license
 */

namespace app;

defined("__DAVCHEZT") or die("{ \"response\" : \"error 403\"}");

class Model
{
    protected $app;
    protected $adapter;

    public function __construct(AppEngine $app)
    {
        $this->app = $app;
    }

    public function __call($method, $args)
    {
        if (method_exists($this->adapter, $method)) {
            return call_user_func_array([$this->adapter, $method], $args);
        }
    }
    
    public function setAdapter(BaseAdapter $adapter)
    {
        $this->adapter = $adapter;
        $this->adapter->setup($this->app);
    }

    public function getAdapter()
    {
        return $this->adapter;
    }
}
