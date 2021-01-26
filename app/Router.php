<?php
/**
 * @author      Raiza Rhamdan (Leonardo DaVchezt) <davchezt@gmail.com>
 * @copyright   Copyright (c), 2014 Raiza Rhamdan
 * @license		MIT public license
 */

namespace app;

use flight\Engine;

use app\Greeting;
use app\Helper;
use app\Lib\R;
use app\Lib\JWTAuth;

use app\Adapter\User;

class Router
{
    protected $app = null;

    public function __construct(Engine $app)
    {
        $this->app = $app;
        $this->app->register('model', 'app\Model', array());
    }

    public function map()
    {
        $this->app->map('route', function ($pattern, $callback, $pass_route = false, $secure = false) {
            if ($secure) {
                $token = $this->app->request()->getToken(); //  generate -> JWTAuth::getToken('1', 'davchezt')
                if (JWTAuth::verifyToken($token)) {
                    $this->app->router()->map($pattern, $callback, $pass_route);
                } else {
                    $this->app->router()->map($pattern, function () {
                        $this->app->notAuthorized('Unauthorized');
                    }, $pass_route);
                }
            } else {
                $this->app->router()->map($pattern, $callback, $pass_route);
            }
        });

        $this->app->map('notFound', function () {
            $this->app->json(['response' => 'Error 404'], 404);
        });
        
        $this->app->map('error', function (Exception $ex) {
            $err = array(
                'error' => array(
                    'code' => $ex->getCode(),
                    'messsage' => $ex->getMessage() . ' at ' . $ex->getFile() . ':' . $ex->getLine(),
                    'trace' => $ex->getTrace()
                )
            );
            $this->app->json(['response' => $err], 500);
        });

        $this->app->map('json', function ($data, $code = null, $encode = true, $charset = 'utf-8', $option = JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) {
            if ($this->app->request()->query['jsonp']) {
                return $this->app->jsonp($data, 'jsonp', $code, $encode, $charset, $option);
            }
            
            $code = ($code) ? $code : $this->app->response()->status();
            $this->app->response()
                ->status($code);
            
            $response = $this->app->response();
            $status = $response->status();
            $message = $response::$codes;
            
            $data = array_merge([
                'status' => $status,
                'message' => $message[$status],
                'timestamp' => Helper::timeNow()
            ], $data);
            $json = ($encode) ? json_encode($data, $option) : $data;
            $this->app->response()
                ->header('Content-Type', 'application/json; charset='.$charset)
                ->write($json)
                ->send();
        });

        $this->app->map('jsonp', function ($data, $param = 'jsonp', $code = null, $encode = true, $charset = 'utf-8', $option = JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) {
            $code = ($code) ? $code : $this->app->response()->status();
            $this->app->response()
                ->status($code);
            
            $response = $this->app->response();
            $status = $response->status();
            $message = $response::$codes;
            
            $data = array_merge([
                'status' => $status,
                'message' => $message[$status],
                'timestamp' => Helper::timeNow()
            ], $data);

            $callback = $this->app->request()->query[$param];
            $json = ($encode) ? json_encode($data, $option) : $data;

            $this->app->response()
                ->header('Content-Type', 'application/json; charset='.$charset)
                ->write($callback.'('.$json.');')
                ->send();
        });

        $this->app->map('notAuthorized', function ($message/* = 'Unauthorized'*/) {
            return $message;
        });
    }

    public function before()
    {
        $this->app->before('start', function () {
            $this->app->response()->header('Access-Control-Allow-Origin', '*');
            $this->app->response()->header('Access-Control-Allow-Credentials', 'true');
            $this->app->response()->header('Access-Control-Allow-Headers', 'Authorization, Origin, X-Requested-With, Content-Type, Accept');
        
            if ($this->app->request()->method === 'OPTIONS') {
                $this->app->response()->header('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE');
            }
        
            $this->app->response()->header('X-Powered-By', 'Leonardo DaVchezt');
        });
        
        $this->app->before('json', function (&$params, &$output) {
            if (R::get('config')['app']['debug']) {
                $params[0] = [
                    // 'headers' => $app->response()->headers(),
                    'request' => [
                        'method' => $this->app->request()->getMethod(),
                        // 'header' => $app->request()->getHeaders(),
                        // 'token' => $this->app->request()->getToken(),
                        'body' => json_decode($this->app->request()->getBody(), true),
                    ],
                    'response' => $params[0]['response']
                ];
            }
        });

        $this->app->before('jsonp', function (&$params, &$output) {
            if (R::get('config')['app']['debug']) {
                $params[0] = [
                // 'headers' => $app->response()->headers(),
                'request' => [
                    'method' => $this->app->request()->getMethod(),
                    // 'header' => $app->request()->getHeaders(),
                    // 'token' => $this->app->request()->getToken(),
                    'body' => json_decode($this->app->request()->getBody(), true),
                ],
                'response' => $params[0]['response']
            ];
            }
        });

        $this->app->before('notAuthorized', function (&$params, &$output) {
            $params[0] = ['response' => 'Error 401 - ' . $params[0]];

            if (R::get('config')['app']['debug']) {
                $token = $this->app->request()->getToken();
                $params[0] = array_merge($params[0], array('token' => $token));
            }
        });
    }

    public function after()
    {
        $this->app->after('notAuthorized', function (&$params, &$output) {
            $output = null;

            $this->app->json(['response' => $params[0]], 401);
        });
    }

    public function init()
    {
        $this->app->route('/', function () {
            $this->app->json(['response' => 'API Main']);
        });
        
        $greeting = new Greeting($this->app);
        $this->app->route('GET /hello', [$greeting, 'hello']);

        $this->app->route('GET /user', function () { // http://localhost/rest/flight/user/?jsonp=console.log
            $user = new User();
            $this->app->model()->setAdapter($user);

            $response = ['response' => $this->app->model()->getAll()];

            $this->app->json(['response' => $this->app->model()->getAll()]);
        }, false, true);

        $this->app->route('GET /user/@id:[0-9]{1,10}', function ($id) {
            $user = new User();
            $this->app->model()->setAdapter($user);
        
            $this->app->json(['response' => $this->app->model()->getById($id)]);
        }, false, false);

        $this->app->route('GET /user/count', function () {
            $user = new User();
            $this->app->model()->setAdapter($user);
        
            $this->app->json(['response' => $this->app->model()->getCount()]);
        }, false, false);
    }
    
    public function start()
    {
        $this->map();
        $this->before();
        $this->after();

        $this->init();

        $this->app->start();
    }
}
