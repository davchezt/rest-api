<?php
/**
 * @author      Raiza Rhamdan (Leonardo DaVchezt) <davchezt@gmail.com>
 * @copyright   Copyright (c), 2021 Raiza Rhamdan
 * @license		MIT public license
 */

use app\Router;
use app\Lib\R;

require 'app/autoload.php';
require 'vendor/autoload.php';

R::set('config', require 'config.php');

$app = new Router(new flight\Engine);
$app->start();
