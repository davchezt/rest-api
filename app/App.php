<?php
/**
 * @author      Raiza Rhamdan (Leonardo DaVchezt) <davchezt@gmail.com>
 * @copyright   Copyright (c), 2021 Raiza Rhamdan
 * @license		MIT public license
 */

namespace app;

defined("__DAVCHEZT") or die("{ \"response\" : \"error 403\"}");

use flight\Engine;

class App
{
    protected $app = null;
    protected $id = 1;
    protected $token = null;
    protected $routers = [];
    protected $startTime;
    protected $config = [];

    public function __construct(Engine $app, $config, $path)
    {
        $this->app = $app;

        $config['app']['path'] = $path;
        $this->app->set('flight.config', $config);
        $this->config = $config;

        $this->app->register('request', 'app\Net\AppRequest', array($path));
        $this->app->register('response', 'app\Net\AppResponse');
        $this->app->register('helper', 'app\Helper');
        $this->app->register('logger', 'app\Logger');
        $this->app->register('mailer', 'app\Lib\Mailer');
        $this->app->register('jwt', 'app\Lib\JWTAuth');
        $this->app->register('db', 'app\Lib\Db');

        $this->app->set('flight.views.path', $this->app->request()->path() . '/resources/views');

        $this->app->logger()->configure($this->app);
        $this->app->mailer()->configure($this->app);
        $this->app->jwt()->configure($this->app);

        $this->configureDatabase();
        $this->initRouter();

        $this->startTime = microtime(true);
        $this->token = $this->app->request()->getToken();

        $header = $this->app->jwt()->getHeader($this->token);
        if (count($header) > 0) {
            $this->id = $header['id'];
        }
    }

    public function configureDatabase()
    {
        $this->app->db()->configure($this->config['db']['dsn']);
        $this->app->db()->configure('username', $this->config['db']['dbu']);
        $this->app->db()->configure('password', $this->config['db']['dbp']);
        $this->app->db()->configure('error_mode', \PDO::ERRMODE_EXCEPTION);
        $this->app->db()->configure('driver_options', array(\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'));

        $this->app->db()->configure('return_result_sets', true);
        $this->app->db()->configure('logging', $this->config['app']['log']);
        $this->app->db()->configure('logger', [$this, 'logQuery']);
        $this->app->db()->configure('caching', true);
        $this->app->db()->configure('caching_auto_clear', true);
    }

    public function logQuery($log_string, $query_time)
    {
        $message = 'ORM ' . $log_string . ' in ' . $query_time;
        $this->app->logger()->write($message, 'orm');
    }

    public function initRouter()
    {
        $dir = $this->app->request()->path() . '/App/Router';
        $exts = $this->app->helper()->listingDir($dir);
        if (count($exts) != 0) {
            foreach ($exts['files'] as $ext) {
                if (file_exists($ext['location'] . '/' . $ext['file'])) {
                    $name = pathinfo($ext['file'], PATHINFO_FILENAME);
                    $routername = 'router' . ucfirst($name);
                    $paramname = 'app\\Router\\' . ucfirst($name);

                    $this->addRouter($routername, $paramname);
                }
            }
        }
    }

    public function addRouter($name, $class)
    {
        $this->routers[$name] = array($class, array($this->app, $this->id));
    }

    public function registerRouter($name)
    {
        if (isset($this->routers[$name])) {
            list($class, $params) = $this->routers[$name];
            $this->app->register($name, $class, $params);

            return true;
        }

        return false;
    }

    public function loadRouters()
    {
        foreach ($this->routers as $key => $val) {
            if ($this->registerRouter($key)) {
                $this->app->$key()->init();
            }
        }
    }

    protected function getResponseTime()
    {
        $endTime = microtime(true);

        return round($endTime - $this->startTime, 3);
    }

    public function routeMap($pattern, $callback, $pass_route = false, $secure = false)
    {
        if ($secure) {
            if ($this->app->jwt()->verifyToken($this->token)) {
                $this->app->router()->map($pattern, $callback, $pass_route);
            } else {
                $this->app->router()->map($pattern, function () {
                    $this->app->notAuthorized('Authorization failed');
                }, $pass_route);
            }
        } else {
            $this->app->router()->map($pattern, $callback, $pass_route);
        }
    }

    public function notFoundMap()
    {
        $this->app->json(['response' => 'Error 404'], 404);
    }

    public function afterNotFound()
    {
        if ($this->config['app']['log']) {
            $message = $this->app->request()->getMethod() . ': ' . $this->app->request()->url() . ' -- ' . $this->app->response()->status() . ' [' . $this->app->request()->ip() . ']';
            $this->app->logger()->write($message, 'notfound');
        }
    }

    public function errorMap(/*\Exception */$ex)
    {
        $err = array(
            'error' => array(
                'code' => $ex->getCode(),
                'messsage' => $ex->getMessage() . ' at ' . $ex->getFile() . ':' . $ex->getLine(),
                'trace' => ($this->config['app']['debug']) ? $ex->getTrace() : 'disabled'
            )
        );

        if ($this->config['app']['log']) {
            $message = 'ERROR ' . $ex->getCode() . ': ' . $ex->getMessage() . ' at ' . $ex->getFile() . ':' . $ex->getLine() . ' [' . $this->app->request()->ip . ']';
            $this->app->logger()->write($message, 'error');
        }
        $this->app->json(['response' => $err], 500);
    }

    public function jsonMap($data, $code = null, $encode = true, $charset = 'utf-8', $option = JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)
    {
        if ($this->app->request()->query['jsonp']) {
            return $this->app->jsonp($data, 'jsonp', $code, $encode, $charset, $option);
        }
        
        $code = ($code) ? $code : $this->app->response()->status();
        $this->app->response()->status($code);
        
        $response = $this->app->response();
        $status = $response->status();
        $message = $response->message();
        
        $data = array_merge([
            'status' => $status,
            'message' => $message,
            'timestamp' => $this->app->helper()->timeNow(),
            'response_time' => $this->getResponseTime() . ' sec'
        ], $data);

        $json = ($encode) ? json_encode($data, $option) : $data;

        $this->app->response()
            ->header('Content-Type', 'application/json; charset='.$charset)
            ->write($json)
            ->send();
    }

    public function jsonpMap($data, $param = 'jsonp', $code = null, $encode = true, $charset = 'utf-8', $option = JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)
    {
        $code = ($code) ? $code : $this->app->response()->status();
        $this->app->response()->status($code);
        
        $response = $this->app->response();
        $status = $response->status();
        $message = $response->message();
        
        $endTime = microtime(true);
        $requstTime = round($endTime - $this->startTime, 5);
        $data = array_merge([
            'status' => $status,
            'message' => $message,
            'timestamp' => $this->app->helper()->timeNow(),
            'response_time' => $this->getResponseTime() . ' sec'
        ], $data);

        $callback = $this->app->request()->query[$param];
        $json = ($encode) ? json_encode($data, $option) : $data;

        $this->app->response()
            ->header('Content-Type', 'application/json; charset='.$charset)
            ->write($callback.'('.$json.');')
            ->send();
    }

    public function beforeStart()
    {
        $this->app->response()->header('Access-Control-Allow-Origin', '*');
        $this->app->response()->header('Access-Control-Allow-Credentials', 'true');
        $this->app->response()->header('Access-Control-Allow-Headers', 'Authorization, Origin, X-Requested-With, Content-Type, Accept');
    
        if ($this->app->request()->method === 'OPTIONS') {
            $this->app->response()->header('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE');
        }
    
        $this->app->response()->header('Cache-Control', 'no-store, no-cache, must-revalidate'); // HTTP 1.1
        $this->app->response()->header('Pragma', 'no-cache'); // HTTP 1.0
        $this->app->response()->header('Vary', 'Accept-Encoding, User-Agent');
        $this->app->response()->header('X-Powered-By', 'davchezt/rest-api');

        header_remove('Connection');
        // $this->app->response()->header('Connection', 'close');

        $this->app->lastModified($this->app->helper()->timeNow(true, true));

        $this->loadRouters();
    }

    public function afetrStart()
    {
        if ($this->config['app']['log'] && $this->app->response()->status() != 404) {
            $message = $this->app->request()->getMethod() . ': ' . $this->app->request()->url() . ' -- ' . $this->app->response()->status() . ' [' . $this->app->request()->ip() . ']';
            $this->app->logger()->write($message, 'route');
        }
    }

    public function beforeJson()
    {
        if ($this->config['app']['debug']) {
            $params[0] = [
                'request' => [
                    'method' => $this->app->request()->getMethod(),
                    'body' => json_decode($this->app->request()->getBody(), true),
                ],
                'response' => $params[0]['response']
            ];
        }
    }

    public function notAuthorizedMap($message)
    {
        return $message;
    }

    public function beforeNotAuthorized(&$params, &$output)
    {
        $params[0] = ['message' => $params[0], 'detail' => 'Token is invalid or expired'];

        if ($this->config['app']['debug']) {
            $params[0] = array_merge($params[0], array('token' => $this->token));
        }
    }

    public function afterNotAuthorized(&$params, &$output)
    {
        $this->app->json(['response' => $params[0]], 401);
    }

    public function start()
    {
        // MAP HOOK
        $this->app->map('route', [$this, 'routeMap']);
        $this->app->map('notFound', [$this, 'notFoundMap']);
        $this->app->map('error', [$this, 'errorMap']);
        $this->app->map('json', [$this, 'jsonMap']);
        $this->app->map('jsonp', [$this, 'jsonpMap']);
        $this->app->map('notAuthorized', [$this, 'notAuthorizedMap']);

        // BEFORE HOOK
        $this->app->before('start', [$this, 'beforeStart']);
        $this->app->before('json', [$this, 'beforeJson']);
        $this->app->before('jsonp', [$this, 'beforeJson']);
        $this->app->before('notAuthorized', [$this, 'beforeNotAuthorized']);

        // AFTER HOOK
        $this->app->after('notAuthorized', [$this, 'afterNotAuthorized']);
        $this->app->after('notFound', [$this, 'afterNotFound']);
        $this->app->after('start', [$this, 'afetrStart']);

        $this->app->start();
    }
}
