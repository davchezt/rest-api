<?php
/**
 * @author      Raiza Rhamdan (Leonardo DaVchezt) <davchezt@gmail.com>
 * @copyright   Copyright (c), 2021 Raiza Rhamdan
 * @license		MIT public license
 */

namespace app\Router;

defined("__DAVCHEZT") or die("{ \"response\" : \"error 403\"}");

use flight\Engine;

use app\BaseRouter;
use app\Adapter\UserAdapter;

class User extends BaseRouter
{
    public function __construct(Engine $app, $userId)
    {
        parent::__construct($app, $userId);

        $user = new UserAdapter($this->id);
        $this->app->model()->setAdapter($user);
    }

    public function init()
    {
        $this->app->route('GET /v1/user', [$this, 'listUser'], false, true);
        $this->app->route('GET /v1/user/me', [$this, 'userData'], false, true);
        $this->app->route('POST /v1/user/token', [$this, 'generateToken']);
        $this->app->route('POST /v1/user/register', [$this, 'registerUser']);
        $this->app->route('GET /v1/user/@id:[0-9]{1,}', [$this, 'getUserById'], false, true);
        $this->app->route('GET /v1/user/@offset:[0-9]{1,}/@limit:[0-9]{1,}', [$this, 'listUserLimit'], false, true);
    }

    public function userData()
    {
        $this->getUserById($this->id);
    }

    public function getUserById($id)
    {
        $userData = $this->app->model()->getById($id);

        $response = ['data' => $userData];
        $this->app->json(['response' => $response]);
    }

    public function listUser()
    {
        $users = $this->app->model()->getAll();
        $response = [
            'data' => $users,
            'count' => count($users)
        ];

        $this->app->json(['response' => $response]);
    }

    public function listUserLimit($offset, $limit)
    {
        $users = $this->app->model()->getList($offset, $limit);
        $response = [
            'data' => $users,
            'count' => count($users)
        ];
        $this->app->json(['response' => $response]);
    }

    public function generateToken()
    {
        $body = json_decode($this->app->request()->getBody(), true);
        list($username, $password) = array_values($body);

        $password = md5($this->app->get('flight.config')['app']['hash'] . '.' . $password);
        // $logdin = $this->app->model()->getAdapter()->checkLogin($username, $password); // using adapter
        
        $logdin = $this->app->model()->checkLogin($username, $password); // magic __call
        $token = ($logdin != -1) ? $this->app->jwt()->getToken(strval($logdin), $username, '7 days') : null;

        $response = [
            'data' => [
                'id' => $logdin,
                'username' => $username,
                'token' => $token
            ]
        ];

        $this->app->json(['response' =>  $response]);
    }

    public function registerUser()
    {
        $body = json_decode($this->app->request()->getBody(), true);
        list($username, $password, $repassword, $name, $place, $day, $month, $year, $email, $gender, $address) = array_values($body);

        $error = null;
        $address = strip_tags(html_entity_decode($address));
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);

        if (!preg_match('#^\w{5,}$#', $username)) {
            $error = 'Invalid username, make sure it has alphanumeric & longer than or equals 5 chars';
        } elseif ($password != $repassword) {
            $error = 'Password not match';
        } elseif (!preg_match('#^\d{1,2}+$#', intval($day))) {
            $error = 'Invalid day for ' . $day;
        } elseif (!preg_match('#^\d{1,2}+$#', intval($month))) {
            $error = 'Invalid month for ' . $month;
        } elseif (!preg_match('#^\d{4}+$#', intval($year))) {
            $error = 'Invalid year for ' . $year;
        } elseif (strlen($address) < 16) {
            $error = 'Invalid address, make sure it has longer than or equals 16 chars';
        } elseif (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $error = 'Invalid email format for ' . $email;
        } elseif ($this->app->model()->checkUsername($username)) {
            $error = 'Username "' . $username . '" already exists';
        } elseif ($this->app->model()->checkEmail($email)) {
            $error = 'Email "' . $email . '" is registered';
        }

        if ($error) {
            $this->app->json(['response' =>  ['data' => $error]]);

            return;
        }

        $password = md5($this->app->get('flight.config')['app']['hash'] . '.' . $password);
        $dob = $place . ', ' . $day . '-' . $month . '-' . $year;
        $lastInsertId = $this->app->model()->registerUser($username, $password, $name, $dob, $email, $gender, $address);
        $token = ($lastInsertId != -1) ? $this->app->jwt()->getToken(strval($lastInsertId), $username, '7 days') : null;

        $response = [
            'data' => [
                'id' => $lastInsertId,
                'username' => $username,
                'token' => $token
            ]
        ];
        $this->app->json(['response' => $response]);
    }
}