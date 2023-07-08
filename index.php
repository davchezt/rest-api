<?php

/**
 * @author      Raiza Rhamdan (Leonardo DaVchezt) <davchezt@gmail.com>
 * @copyright   Copyright (c), 2021 Raiza Rhamdan
 * @license		MIT public license
 */

if (version_compare(phpversion(), '7.2.0', '<')) die('php >= 7.2.0 is required');

if (!file_exists('vendor/autoload.php')) {
    die('please run composer install first!');
} elseif (!file_exists('config.php')) {
    die('no config file found, please copy config.example.php to config.php');
}

$temp = realpath(dirname(__FILE__)) . '/temp';
if (!is_dir($temp)) {
    mkdir($temp);
}

session_start();
ini_set('output_buffering', 'On');
ini_set('output_compression', 'On');
ini_set('output_compression_level', '6');

use app\App;
use app\AppEngine;
use flight\Engine;

date_default_timezone_set('Asia/Jakarta');
define('__DAVCHEZT', true);

require 'vendor/autoload.php';

$app = new App(new AppEngine(realpath(dirname(__FILE__))), require 'config.php');
$app->start();
