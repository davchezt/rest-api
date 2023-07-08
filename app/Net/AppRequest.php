<?php
/**
 * @author      Raiza Rhamdan (Leonardo DaVchezt) <davchezt@gmail.com>
 * @copyright   Copyright (c), 2021 Raiza Rhamdan
 * @license		MIT public license
 */

namespace app\Net;

defined("__DAVCHEZT") or die("{ \"response\" : \"error 403\"}");

use flight\net\Request;
use flight\util\Collection;

class AppRequest extends Request
{
    public $path;

    public function __construct($path)
    {
        $this->path = $path;

        $config = array(
            'url' => str_replace('@', '%40', self::getVar('REQUEST_URI', '/')),
            'base' => str_replace(array('\\',' '), array('/','%20'), dirname(self::getVar('SCRIPT_NAME'))),
            'path' => str_replace(array('\\',' '), array('/','%20'), $path),
            'method' => self::getMethod(),
            'referrer' => self::getVar('HTTP_REFERER'),
            'ip' => self::getVar('REMOTE_ADDR'),
            'ajax' => self::getVar('HTTP_X_REQUESTED_WITH') == 'XMLHttpRequest',
            'scheme' => self::getScheme(),
            'user_agent' => self::getVar('HTTP_USER_AGENT'),
            'type' => self::getVar('CONTENT_TYPE'),
            'length' => (int)self::getVar('CONTENT_LENGTH', 0),
            'query' => new Collection($_GET),
            'data' => new Collection($_POST),
            'cookies' => new Collection($_COOKIE),
            'files' => new Collection($_FILES),
            'secure' => self::getScheme() == 'https',
            'accept' => self::getVar('HTTP_ACCEPT'),
            'proxy_ip' => self::getProxyIpAddress(),
            'host' => self::getVar('HTTP_HOST'),
        );

        parent::__construct($config);
    }

    public function __call($method, $args = false)
    {
        $reflect = new \ReflectionClass($this);
        $props = $reflect->getProperties(\ReflectionProperty::IS_PUBLIC);

        foreach ($props as $prop) {
            if ($method == $prop->getName()) {
                return $this->$method;
            }
        }
    }

    /**
     * Initialize request properties.
     *
     * @param array $properties Array of request properties
     */
    public function init(array $properties = [])
    {
        // Set all the defined properties
        foreach ($properties as $name => $value) {
            $this->$name = $value;
        }

        // Get the requested URL without the base directory
        if ('/' !== $this->base && '' !== $this->base && 0 === strpos($this->url, $this->base)) {
            $this->url = substr($this->url, \strlen($this->base));
        }

        $scriptname = self::getVar('SCRIPT_NAME');
        $this->url = str_replace($scriptname, '', $this->url);

        // Default url
        if (empty($this->url)) {
            $this->url = '/';
        } else {
            // Merge URL query parameters with $_GET
            $_GET = array_merge($_GET, self::parseQuery($this->url));

            $this->query->setData($_GET);
        }

        // Check for JSON input
        if (0 === strpos($this->type, 'application/json')) {
            $body = self::getBody();
            if ('' !== $body && null !== $body) {
                $data = json_decode($body, true);
                if (is_array($data)) {
                    $this->data->setData($data);
                }
            }
        }
    }

    /**
     * Get Authorization header.
     *
     * @return string Authorization header
     */
    private static function getAuthorizationHeader()
    {
        $headers = null;
        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER["Authorization"]);
        } elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
        } elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
  
            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            }
        }
  
        return $headers;
    }

    /**
     * Get Bearer token.
     *
     * @return string token
     */
    public static function getToken()
    {
        $headers = self::getAuthorizationHeader();
        if (!empty($headers)) {
            if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
                return $matches[1];
            }
        }
    
        return null;
    }

    /**
     * Gets the body of the request.
     *
     * @return string Raw HTTP request body
     */
    public static function getBody(): ?string
    {
        static $body;

        if (!is_null($body)) {
            return $body;
        }

        $method = self::getMethod();

        if ($method == 'POST' || $method == 'PUT' || $method == 'DELETE' || $method == 'PATCH') {
            $body = ($stream = fopen('php://input', 'r')) !== false ? stream_get_contents($stream) : null;
            if ($stream !== false) {
                fclose($stream);
            }
        }

        return $body;
    }
}
