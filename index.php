<?php
/**
 * @author      Raiza Rhamdan (Leonardo DaVchezt) <davchezt@gmail.com>
 * @copyright   Copyright (c), 2021 Raiza Rhamdan
 * @license		MIT public license
 */

use app\App;
use app\Lib\R;
use app\Helper;

date_default_timezone_set('Asia/Jakarta');
define('__DAVCHEZT', true);

define('__PATH', realpath(dirname(__FILE__)));
define('__APP', __PATH . '/App');
define('__RT', __APP . '/Router');
define('__VIEW', __PATH . '/resource/view');
define('__LOG', __PATH . '/logs');

require 'vendor/autoload.php';
require 'app/autoload.php';

R::set('config', require 'config.php');
R::set('path', __PATH);
R::set('routers', __RT);
R::set('views', __VIEW);
R::set('logs', __LOG);

$app = new App(new flight\Engine);
$app->start();