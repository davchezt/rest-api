<?php
/**
 * @author      Raiza Rhamdan (Leonardo DaVchezt) <davchezt@gmail.com>
 * @copyright   Copyright (c), 2021 Raiza Rhamdan
 * @license		MIT public license
 */

namespace app\Plugin;

defined("__DAVCHEZT") or die("{ \"response\" : \"error 403\"}");

use app\BasePlugin;

class Example extends BasePlugin
{
    public function listen() {}

    public function handler_App_beforeStart_before()
    {
        $this->app->response()->header('Access-Control-Allow-Origin', '*');
        $this->app->response()->header('Access-Control-Allow-Credentials', 'true');
        $this->app->response()->header('Access-Control-Allow-Headers', 'Authorization, Origin, X-Requested-With, Content-Type, Accept');
    
        if ($this->app->request()->method === 'OPTIONS') {
            $this->app->response()->header('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE');
        }
    }

    public function handler_App_beforeStart_after()
    {
        // remove comment to enable hook
        /*$this->app->response()->removeHeader('Connection');*/
    }

    public function handler_App_jsonMap_before(&$data, &$code, &$encode, &$charset, &$option)
    {
        // remove comment to enable hook
        /*$charset = 'utf-8';*/
    }

    public function handler_App_jsonMap_init(&$data)
    {
        // remove comment to enable hook
        /*unset($data['response_time']);*/
    }

    public function handler_App_jsonMap_after(&$json, &$data, &$code, &$encode, &$charset, &$option)
    {
        // remove comment to enable hook
        /*unset($data['timestamp']);
        $json = ($encode) ? json_encode($data, $option) : $data;*/
    }

    public function handler_Router_User_registerUser_init($router, &$username, &$password, &$name, &$dob, &$email, &$gender, &$address, &$lastInsertId, &$token)
    {
        if ($token != null)
        {
            $subject = "Registration Complite!";
            $html = "";
            $text = "Congragulation your registration complited, currently no need to response this message or doing vervication email, enjoy and have fun";
            // $this->app->mailer()->send($email, $name, $subject, $html, $text);
        }
    }
}