<?php
/**
 * @author      Raiza Rhamdan (Leonardo DaVchezt) <davchezt@gmail.com>
 * @copyright   Copyright (c), 2021 Raiza Rhamdan
 * @license		MIT public license
 */

namespace app;

defined("__DAVCHEZT") or die("{ \"response\" : \"error 403\"}");

use app\Plugin;

class Pluggable
{
    public $className;

    public function __construct()
    {
        $this->className = get_class($this);
    }

    public function trigger($event, $parameters = array())
    {
        array_unshift($parameters, $this);

        $return = array();

        if ($this->className) {
            $return = Plugin::trigger($this->className . '_' . $event, $parameters);
        }

        $return = array_merge($return, Plugin::trigger($event, $parameters));

        return $return;
    }
}
