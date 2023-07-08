<?php
/**
 * @author      Raiza Rhamdan (Leonardo DaVchezt) <davchezt@gmail.com>
 * @copyright   Copyright (c), 2021 Raiza Rhamdan
 * @license		MIT public license
 */

namespace app;

defined("__DAVCHEZT") or die("{ \"response\" : \"error 403\"}");

use flight\Engine;

/**
 * The Engine class contains the core functionality of the framework.
 * It is responsible for loading an HTTP request, running the assigned services,
 * and generating an HTTP response.
 *
 * Core methods
 * @method void start() Starts engine
 * @method void stop() Stops framework and outputs current response
 * @method void halt(int $code = 200, string $message = '') Stops processing and returns a given response.
 *
 *
 * Routing
 * @method void route(string $pattern, callable $callback, bool $pass_route = false) Routes a URL to a callback function.
 * @method \flight\net\Router router() Gets router
 *
 * Views
 * @method void render(string $file, array $data = null, string $key = null) Renders template
 * @method \flight\template\View view() Gets current view
 *
 * Request-response
 * @method \app\Net\AppRequest request() Gets current request
 * @method \app\Net\AppResponse response() Gets current response
 * @method void error(\Exception $e) Sends an HTTP 500 response for any errors.
 * @method void notFound() Sends an HTTP 404 response when a URL is not found.
 * @method void redirect(string $url, int $code = 303)  Redirects the current request to another URL.
 * @method void json(mixed $data, int $code = 200, bool $encode = true, string $charset = 'utf-8', int $option = 0) Sends a JSON response.
 * @method void jsonp(mixed $data, string $param = 'jsonp', int $code = 200, bool $encode = true, string $charset = 'utf-8', int $option = 0) Sends a JSONP response.
 *
 * HTTP caching
 * @method void etag($id, string $type = 'strong') Handles ETag HTTP caching.
 * @method void lastModified(int $time) Handles last modified HTTP caching.
 */

class AppEngine extends Engine
{
    protected $appPath;

    public function __construct($path)
    {
        $this->appPath = $path;

        parent::__construct();
    }

    public function init(): void
    {
        parent::init();

        $this->loader->unregister('request');
        $this->loader->unregister('response');

        $this->loader->register('request', 'app\Net\AppRequest', [$this->appPath]);
        $this->loader->register('response', 'app\Net\AppResponse');

        $this->loader->register('helper', 'app\Helper');
        $this->loader->register('plugin', 'app\Plugin');
        $this->loader->register('logger', 'app\Logger');
        $this->loader->register('mailer', 'app\Lib\Mailer');
        $this->loader->register('jwt', 'app\Lib\JWTAuth');
        $this->loader->register('db', 'app\Lib\Db');

        $cnf = array(
            "baseDir" => $this->request()->path() . '/uploads',
            "uploadDir" => 'avatar',
            "imageWidth" => 400,
            "imageHeight" => 400,
            "watermarkImage" => 'copy.png',
            "jpegQuality" => 100
        );
        $this->loader->register('image', 'app\Lib\Image', [$cnf]);
    }
}