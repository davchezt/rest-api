<?php
/**
 * @author      Raiza Rhamdan (Leonardo DaVchezt) <davchezt@gmail.com>
 * @copyright   Copyright (c), 2021 Raiza Rhamdan
 * @license		MIT public license
 */

namespace app;

defined("__DAVCHEZT") or die("{ \"response\" : \"error 403\"}");

class App
{
    protected $app = null;

    protected $id = 0;
    protected $token = null;
    protected $routers = [];
    protected $startTime;
    protected $config = [];

    // Rate limit settings (adjust as needed)
    protected $requestsLimit = 5; // Maximum number of requests allowed
    protected $timeWindow = 1; // Time window in seconds

    // Get the client IP address as the unique identifier
    protected $clientIP = null;

    public function __construct(AppEngine $app, $config)
    {
        $this->app = $app;
        $this->config = $config;

        $this->startTime = microtime(true);

        $this->clientIP = $this->app->request()->getVar('REMOTE_ADDR');
        if ($this->app->helper()->isRateLimited($this->clientIP, $this->requestsLimit, $this->timeWindow)) {
            $message = ['data' => 'Too many requests'];
            
            $this->app->response()->status(429);

            $response = $this->app->response();
            $status = $response->status();
            $message = $response->message();

            $data = [
                'status' => $status,
                'message' => $message,
                'timestamp' => $this->app->helper()->timeNow(),
                'response_time' => $this->getResponseTime() . ' sec'
            ];

            if ($this->config['app']['debug']) {
                $data['request'] = [
                    'method' => $this->app->request()->getMethod(),
                    'body' => json_decode($this->app->request()->getBody(), true),
                ];
            }

            $data['response'] = ['data' => 'Too many requests. Please try again after few sec.'];
            $json = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
            
            $this->app->response()
                ->header('Content-Type', 'application/json; charset=utf-8')
                ->write($json)
                ->send();

            exit;
        }

        $this->app->set('flight.config', $config);
        $this->app->set('flight.views.path', $this->app->request()->path() . '/resources/views');

        $this->app->plugin()->configure($this->app);
        $this->app->logger()->configure($this->app);
        $this->app->mailer()->configure($this->app);
        $this->app->jwt()->configure($this->app);

        $cnf = array(
            "baseDir" => $this->app->request()->path() . '/uploads',
            "uploadDir" => 'avatar',
            "imageWidth" => 400,
            "imageHeight" => 400,
            "watermarkImage" => 'copy.png',
            "jpegQuality" => 100
        );
        $this->app->image()->configure($cnf);

        $this->configureDatabase();
        $this->ckeckToken();

        $this->initRouter();
        $this->loadPlugins();
    }

    private function configureDatabase()
    {
        $this->app->db()->configure($this->config['db']['dsn']);
        $this->app->db()->configure('username', $this->config['db']['dbu']);
        $this->app->db()->configure('password', $this->config['db']['dbp']);
        $this->app->db()->configure('error_mode', \PDO::ERRMODE_EXCEPTION);
        $this->app->db()->configure('driver_options', [\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4']);

        $this->app->db()->configure('return_result_sets', true);
        $this->app->db()->configure('logging', $this->config['app']['log']);
        $this->app->db()->configure('logger', [$this, 'logQuery']);
        $this->app->db()->configure('caching', true);
        $this->app->db()->configure('caching_auto_clear', true);

        $this->app->plugin()->trigger('init'); // App_configureDatabase_init
    }

    private function ckeckToken()
    {
        $token = $this->app->request()->getToken();
        if ($token === null) return;
        
        $header = $this->app->jwt()->getHeader($token);

        $this->app->plugin()->trigger('before', [$this, &$token, &$header]); // App_ckeckToken_before
        if (is_array($header) && count($header) > 0) {
            list($id, $username) = array_values($header);
            $id = intval($id);

            $count = $this->app->db()
                ->for_table('user')
                ->where('username', $username)
                ->where('id', $id)
                ->count();

            if ($count != 0) {
                $this->updateToken($id, $token);
            }
        }

        $this->app->plugin()->trigger('after', [$this, &$token, &$header]); // App_ckeckToken_after
    }

    public function updateToken($id, $token)
    {
        if (!empty($token) && is_int($id) && $id > 0)
        {
            $this->id = $id;
            $this->token = $token;
        }
    }

    private function logQuery($log_string, $query_time)
    {
        $message = 'ORM ' . $log_string . ' in ' . $query_time;
        $this->app->plugin()->trigger('log', [$this, &$message, $log_string, $query_time]); // App_logQuery_log

        $this->app->logger()->write($message, 'orm');
    }

    private function initRouter()
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

    private function loadPlugins()
    {
        $dir = $this->app->request()->path() . '/App/Plugin';
        $exts = $this->app->helper()->listingDir($dir);
        if (count($exts) != 0) {
            foreach ($exts['files'] as $ext) {
                if (file_exists($ext['location'] . '/' . $ext['file'])) {
                    $name = pathinfo($ext['file'], PATHINFO_FILENAME);

                    $this->app->plugin()->register(ucfirst($name));
                }
            }
        }

        $this->app->plugin()->loadPlugins();
    }

    public function addRouter($name, $class)
    {
        $this->routers[$name] = [$class, [$this->app, $this->id]];
    }

    private function registerRouter($name)
    {
        if (isset($this->routers[$name])) {
            list($class, $params) = $this->routers[$name];
            $this->app->register($name, $class, $params);

            return true;
        }

        return false;
    }

    private function loadRouters()
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
        $this->app->plugin()->trigger('before', [$this, $pattern, $callback, $pass_route, $secure]); // App_routeMap_before
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
        $this->app->plugin()->trigger('after', [$this, $pattern, $callback, $pass_route, $secure]); // App_routeMap_after
    }

    public function notFoundMap()
    {
        $response = ['data' => 'Error 404'];
        $this->app->plugin()->trigger('init', [$this, &$response]); // App_notFoundMap_init

        $this->app->json(['response' => $response], 404);
    }

    public function afterNotFound()
    {
        if ($this->config['app']['log']) {
            $message = $this->app->request()->getMethod() . ': ' . $this->app->request()->url() . ' -- ' . $this->app->response()->status() . ' [' . $this->app->request()->ip() . ']';
            $this->app->plugin()->trigger('log', [$this, &$message]); // App_afterNotFound_log

            $this->app->logger()->write($message, 'notfound');
        }
    }

    public function errorMap(/*\Exception */$ex)
    {
        $this->app->plugin()->trigger('before', [$this, $ex]); // App_errorMap_before
        $err = [
            'error' => [
                'code' => $ex->getCode(),
                'messsage' => $ex->getMessage() . ' at ' . $ex->getFile() . ':' . $ex->getLine(),
                'trace' => ($this->config['app']['debug']) ? $ex->getTrace() : 'disabled'
            ]
        ];

        $this->app->plugin()->trigger('init', [$this, &$err]); // App_errorMap_init

        if ($this->config['app']['log']) {
            $message = 'ERROR ' . $ex->getCode() . ': ' . $ex->getMessage() . ' at ' . $ex->getFile() . ':' . $ex->getLine() . ' [' . $this->app->request()->ip . ']';
            $this->app->plugin()->trigger('log', [$this, &$message]); // App_errorMap_log

            $this->app->logger()->write($message, 'error');
        }

        $this->app->json(['response' => $err], 500);
    }

    public function jsonMap($data, $code = null, $encode = true, $charset = 'utf-8', $option = JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)
    {
        if ($this->app->request()->query['jsonp']) {
            return $this->app->jsonp($data, 'jsonp', $code, $encode, $charset, $option);
        }

        $this->app->plugin()->trigger('before', [&$data, &$code, &$encode, &$charset, &$option]); // App_jsonMap_before
        
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

        $this->app->plugin()->trigger('init', [&$data]); // App_jsonMap_init

        $json = ($encode) ? json_encode($data, $option) : $data;

        $this->app->plugin()->trigger('after', [&$json, &$data, &$code, &$encode, &$charset, &$option]); // App_jsonMap_after

        $this->app->response()
            ->header('Content-Type', 'application/json; charset='.$charset)
            ->write($json)
            ->send();
    }

    public function jsonpMap($data, $param = 'jsonp', $code = null, $encode = true, $charset = 'utf-8', $option = JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)
    {
        $this->app->plugin()->trigger('before', [&$data, &$param, &$code, &$encode, &$charset, &$option]); // App_jsonpMap_before

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

        $this->app->plugin()->trigger('init', [&$data, &$callback]); // App_jsonpMap_init

        $json = ($encode) ? json_encode($data, $option) : $data;

        $this->app->plugin()->trigger('after', [&$json, &$param, &$data, &$code, &$encode, &$charset, &$option]); // App_jsonpMap_after

        $this->app->response()
            ->header('Content-Type', 'application/json; charset='.$charset)
            ->write($callback.'('.$json.');')
            ->send();
    }

    public function beforeStart()
    {
        $this->app->plugin()->trigger('before'); // App_beforeStart_before
        $this->app->response()->header('Cache-Control', 'no-store, no-cache, must-revalidate'); // HTTP 1.1
        $this->app->response()->header('Pragma', 'no-cache'); // HTTP 1.0
        $this->app->response()->header('Vary', 'Accept-Encoding, User-Agent');
        $this->app->response()->header('X-Powered-By', 'davchezt/rest-api');
        $this->app->response()->header('Connection', 'close');
        $this->app->lastModified($this->app->helper()->timeNow(true, true));
        $this->app->plugin()->trigger('after'); // App_beforeStart_after

        $this->loadRouters();
    }

    public function afetrStart()
    {
        if ($this->config['app']['log'] && $this->app->response()->status() != 404) {
            $message = $this->app->request()->getMethod() . ': ' . $this->app->request()->url() . ' -- ' . $this->app->response()->status() . ' [' . $this->app->request()->ip() . ']';
            $this->app->plugin()->trigger('log', [$this, &$message]); // App_afetrStart_log
            
            $this->app->logger()->write($message, 'route');
        }
    }

    public function beforeJson(&$params, &$output)
    {
        if ($this->config['app']['debug']) {
            $params[0] = [
                'request' => [
                    'method' => $this->app->request()->getMethod(),
                    'body' => json_decode($this->app->request()->getBody(), true),
                ],
                'response' => $params[0]['response']
            ];

            $this->app->plugin()->trigger('init', [$this, &$params, &$output]); // App_beforeJson_init
        }
    }

    public function notAuthorizedMap($message)
    {
        $this->app->plugin()->trigger('init', [$this, &$message]); // App_notAuthorizedMap_init

        return $message;
    }

    public function beforeNotAuthorized(&$params, &$output)
    {
        $params[0] = ['message' => $params[0], 'detail' => 'Token is invalid or expired'];

        if ($this->config['app']['debug']) {
            $params[0] = array_merge($params[0], ['token' => $this->token]);
        }

        $this->app->plugin()->trigger('init', [$this, &$params, &$output]); // App_beforeNotAuthorized_init
    }

    public function afterNotAuthorized(&$params, &$output)
    {
        $this->app->plugin()->trigger('init', [$this, &$params, &$output]); // App_afterNotAuthorized_init
        $this->app->json(['response' => $params[0]], 401);
    }

    public function start()
    {
        $this->app->plugin()->trigger('before', [$this]); // App_start_before

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

        $this->app->plugin()->trigger('after', [$this]); // App_start_after

        $this->app->start();
    }
}
