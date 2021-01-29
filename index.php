<?php
/**
 * @author      Raiza Rhamdan (Leonardo DaVchezt) <davchezt@gmail.com>
 * @copyright   Copyright (c), 2021 Raiza Rhamdan
 * @license		MIT public license
 */

use app\App;
use flight\Engine;

date_default_timezone_set('Asia/Jakarta');
define('__DAVCHEZT', true);

require 'vendor/autoload.php';

$app = new App(new Engine, require 'config.php', realpath(dirname(__FILE__)));
$app->start();