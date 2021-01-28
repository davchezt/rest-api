<?php
/**
 * @author      Raiza Rhamdan (Leonardo DaVchezt) <davchezt@gmail.com>
 * @copyright   Copyright (c), 2021 Raiza Rhamdan
 * @license		MIT public license
 */

namespace app;

defined("__DAVCHEZT") or die("{ \"response\" : \"error 403\"}");

use flight\Engine;

use app\Helper;
use app\Lib\R;
use app\Lib\JWTAuth;

class App
{
    protected $app = null;
    protected $id = 1;
    protected $token = null;
    protected $routers = [];

    public function __construct(Engine $app)
    {
        $this->app = $app;

        $this->app->register('request', 'app\Net\AppRequest');
        $this->app->register('response', 'app\Net\AppResponse');
        $this->app->set('flight.views.path', R::get('views'));

        $this->token = $this->app->request()->getToken();
        $header = JWTAuth::getHeader($this->token);
        if (count($header) > 0) {
            $this->id = $header['id'];
        }

        $this->initRouter();

        // echo JWTAuth::getToken('1', 'davchezt', '7 days'); exit;
        // echo JWTAuth::getToken('2', 'vchezt', '7 days'); exit;
    }

    public function initRouter()
    {
        $exts = Helper::listingDir(R::get('routers'));
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

    public function routeMap($pattern, $callback, $pass_route = false, $secure = false)
    {
        if ($secure) {
            if (JWTAuth::verifyToken($this->token)) {
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

    public function errorMap(/*\Exception */$ex)
    {
        $err = array(
            'error' => array(
                'code' => $ex->getCode(),
                'messsage' => $ex->getMessage() . ' at ' . $ex->getFile() . ':' . $ex->getLine(),
                'trace' => (R::get('config')['app']['debug']) ? $ex->getTrace() : 'disabled'
            )
        );
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
            'timestamp' => Helper::timeNow()
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
        
        $data = array_merge([
            'status' => $status,
            'message' => $message,
            'timestamp' => Helper::timeNow()
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

        $this->app->lastModified(Helper::timeNow(true, true));
        $this->loadRouters();
    }

    public function beforeJson()
    {
        if (R::get('config')['app']['debug']) {
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

        if (R::get('config')['app']['debug']) {
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

        // $this->app->approuter()->init();
        $this->app->start();
    }
}
