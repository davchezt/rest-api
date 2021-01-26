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

use app\Adapter\User;

class Router
{
    private $app = null;

    public function __construct(Engine $app)
    {
        $this->app = $app;
        $this->app->register('model', 'app\Model', array());
    }

    public function before()
    {
        $app = $this->app;
        $app->before('start', function () use ($app) {
            $app->response()->header('Access-Control-Allow-Origin', '*');
            $app->response()->header('Access-Control-Allow-Credentials', 'true');
            $app->response()->header('Access-Control-Allow-Headers', 'Authorization, Origin, X-Requested-With, Content-Type, Accept');
        
            if ($app->request()->method === 'OPTIONS') {
                $app->response()->header('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE');
            }
        
            $app->response()->header('X-Powered-By', 'Leonardo DaVchezt');
        });
        
        $app->before('json', function (&$params, &$output) use ($app) {
            if (R::get('config')['app']['debug']) {
                $params[0] = [
                    // 'headers' => $app->response()->headers(),
                    'request' => [
                        'method' => $app->request()->getMethod(),
                        // 'header' => $app->request()->getHeaders(),
                        'token' => $app->request()->getToken(),
                        'body' => json_decode($app->request()->getBody(), true),
                    ],
                    'response' => $params[0]['response']
                ];
            }
        });

        $app->before('jsonp', function (&$params, &$output) use ($app) {
            if (R::get('config')['app']['debug']) {
                $params[0] = [
                // 'headers' => $app->response()->headers(),
                'request' => [
                    'method' => $app->request()->getMethod(),
                    // 'header' => $app->request()->getHeaders(),
                    'token' => $app->request()->getToken(),
                    'body' => json_decode($app->request()->getBody(), true),
                ],
                'response' => $params[0]['response']
            ];
            }
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

            if ($this->app->request()->query['jsonp']) {
                $this->app->jsonp($response);
            } else {
                $this->app->json($response);
            }
        });

        $this->app->route('GET /user/@id:[0-9]{1,10}', function ($id) {
            $user = new User();
            $this->app->model()->setAdapter($user);
        
            $this->app->json(['response' => $this->app->model()->getById($id)]);
        });

        $this->app->route('GET /user/count', function () {
            $user = new User();
            $this->app->model()->setAdapter($user);
        
            $this->app->json(['response' => $this->app->model()->getCount()]);
        });
    }
    
    public function map()
    {
        $app = $this->app;
        $app->map('notFound', function () use ($app) {
            $app->json(['response' => 'Error 404'], 404);
        });
        
        $app->map('error', function (Exception $ex) use ($app) {
            $err = array(
                'error' => array(
                    'code' => $ex->getCode(),
                    'messsage' => $ex->getMessage() . ' at ' . $ex->getFile() . ':' . $ex->getLine(),
                    'trace' => $ex->getTrace()
                )
            );
            $app->json(['response' => $err], 500);
        });

        $app->map('json', function ($data, $code = null, $encode = true, $charset = 'utf-8', $option = JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) use ($app) {
            $code = ($code) ? $code : $app->response()->status();
            $app->response()
                ->status($code);
            
            $response = $app->response();
            $status = $response->status();
            $message = $response::$codes;
            
            $data = array_merge([
                'status' => $status,
                'message' => $message[$status],
                'timestamp' => Helper::timeNow()
            ], $data);
            $json = ($encode) ? json_encode($data, $option) : $data;
            $app->response()
                ->header('Content-Type', 'application/json; charset='.$charset)
                ->write($json)
                ->send();
        });

        $app->map('jsonp', function ($data, $param = 'jsonp', $code = null, $encode = true, $charset = 'utf-8', $option = JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) use ($app) {
            $code = ($code) ? $code : $app->response()->status();
            $app->response()
                ->status($code);
            
            $response = $app->response();
            $status = $response->status();
            $message = $response::$codes;
            
            $data = array_merge([
                'status' => $status,
                'message' => $message[$status],
                'timestamp' => Helper::timeNow()
            ], $data);

            $callback = $app->request()->query[$param];
            $json = ($encode) ? json_encode($data, $option) : $data;

            $app->response()
                ->header('Content-Type', 'application/json; charset='.$charset)
                ->write($callback.'('.$json.');')
                ->send();
        });
    }

    public function start()
    {
        $this->before();
        $this->init();
        $this->map();

        $this->app->start();
    }
}
