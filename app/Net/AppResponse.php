<?php
/**
 * @author      Raiza Rhamdan (Leonardo DaVchezt) <davchezt@gmail.com>
 * @copyright   Copyright (c), 2021 Raiza Rhamdan
 * @license		MIT public license
 */

namespace app\Net;

defined("__DAVCHEZT") or die("{ \"response\" : \"error 403\"}");

use flight\net\Response;

class AppResponse extends Response
{
    public function message()
    {
        $message = 'Unknown';
        if (array_key_exists($this->status, self::$codes)) {
            $message = self::$codes[$this->status];
        }

        return $message;
    }

    public function removeHeader($name)
    {
        if (array_key_exists($name, $this->headers))
        {
            unset($this->headers[$name]);
        }
    }
}