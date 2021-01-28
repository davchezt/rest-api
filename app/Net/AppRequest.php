<?php
/**
 * @author      Raiza Rhamdan (Leonardo DaVchezt) <davchezt@gmail.com>
 * @copyright   Copyright (c), 2021 Raiza Rhamdan
 * @license		MIT public license
 */

namespace app\Net;

defined("__DAVCHEZT") or die("{ \"response\" : \"error 403\"}");

use flight\net\Request;

class AppRequest extends Request
{
    /**
     * Initialize request properties.
     *
     * @param array $properties Array of request properties
     */
    public function init($properties = array())
    {
        // Set all the defined properties
        foreach ($properties as $name => $value) {
            $this->$name = $value;
        }

        // Get the requested URL without the base directory
        if ($this->base != '/' && strlen($this->base) > 0 && strpos($this->url, $this->base) === 0) {
            $this->url = preg_replace('#^' . preg_quote($this->base) . '(/index\.php)?#', '', $this->url);
            $parts = explode('/', trim(urldecode($this->url), '/'));
            if ($parts[0] == 'index.php') {
                array_shift($parts);
            }
            $this->url = implode('/', $parts);
            $this->url = '/' . trim($this->url, '/');
        }

        // Default url
        if (empty($this->url)) {
            $this->url = '/';
        }
        // Merge URL query parameters with $_GET
        else {
            $_GET += self::parseQuery($this->url);

            $this->query->setData($_GET);
        }

        // Check for JSON input
        if (strpos($this->type, 'application/json') === 0) {
            $body = $this->getBody();
            if ($body != '') {
                $data = json_decode($body, true);
                if ($data != null) {
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
    public static function getBody()
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
