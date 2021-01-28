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

R::set('app.config', require 'config.php');
R::set('app.path.base', __PATH);
R::set('app.path.routers', __RT);
R::set('app.path.views', __VIEW);
R::set('app.path.logs', __LOG);

$app = new App(new flight\Engine);
$app->start();