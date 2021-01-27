<?php
/**
 * @author      Raiza Rhamdan (Leonardo DaVchezt) <davchezt@gmail.com>
 * @copyright   Copyright (c), 2021 Raiza Rhamdan
 * @license		MIT public license
 */

defined("__DAVCHEZT") or die("{ \"response\" : \"error 403\"}");

spl_autoload_register(function ($className) {
    $className = str_replace('\\', DIRECTORY_SEPARATOR, $className);
    $file = $className . '.php';

    if (!file_exists($file)) {
        return false;
    }

    require $file;
});