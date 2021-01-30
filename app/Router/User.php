<?php
/**
 * @author      Raiza Rhamdan (Leonardo DaVchezt) <davchezt@gmail.com>
 * @copyright   Copyright (c), 2021 Raiza Rhamdan
 * @license		MIT public license
 */

namespace app\Router;

defined("__DAVCHEZT") or die("{ \"response\" : \"error 403\"}");

use flight\Engine;
use Form\Validator;

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
        $validator = new Validator([
            'username' => ['required', 'trim', 'min_length' => 5, 'max_length' => 32],
            'password' => ['required', 'trim', 'min_length' => 5, 'max_length' => 32]
        ]);

        if ($validator->validate($body)) {
            $body = $validator->getValues(); // returns an array of sanitized values
            
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

            $this->app->json(['response' => $response]);
        } else {
            $validator->getErrors(); // contains the errors
            $validator->getValues(); // can be used to repopulate the form

            $response = [
                'error' => $validator->getErrors(),
                'value' => $validator->getValues()
            ];

            $this->app->json(['response' => $response]);
        }
    }

    public function registerUser()
    {
        $body = json_decode($this->app->request()->getBody(), true);
        $validator = new Validator([
            'username' => ['required', 'trim', 'min_length' => 5, 'max_length' => 32],
            'password' => ['required', 'trim', 'min_length' => 5, 'max_length' => 32],
            'repassword' => ['required', 'trim', 'min_length' => 5, 'max_length' => 32],
            'name' => ['required', 'trim', 'min_length' => 5, 'max_length' => 32],
            'place' => ['required', 'trim', 'min_length' => 5, 'max_length' => 32],
            'day' => ['required', 'numeric', 'min_length' => 1, 'max_length' => 2],
            'month' => ['required', 'numeric', 'min_length' => 1, 'max_length' => 2],
            'year' => ['required', 'numeric', 'min_length' => 4, 'max_length' => 4],
            'email' => ['required', 'email', 'min_length' => 5, 'max_length' => 32],
            'gender' => ['required', 'numeric', 'min_length' => 1, 'max_length' => 1],
            'address' => ['required', 'trim', 'min_length' => 10, 'max_length' => 500],
        ]);

        if ($validator->validate($body)) {
            $body = $validator->getValues(); // returns an array of sanitized values

            list($username, $password, $repassword, $name, $place, $day, $month, $year, $email, $gender, $address) = array_values($body);

            $error = [];
            if ($password != $repassword) {
                $error['password'] = ['match' => false];
            }
            if ($this->app->model()->checkUsername($username)) {
                $error['username'] = ['exists' => true];
            }
            if ($this->app->model()->checkEmail($email)) {
                $error['email'] = ['exists' => true];
            }

            $response = [
                'error' => $error,
                'value' => $validator->getValues()
            ];

            if ($error) {
                $this->app->json(['response' =>  ['data' => $response]]);
    
                return;
            }

            $email = filter_var($email, FILTER_SANITIZE_EMAIL);
            $address = strip_tags(html_entity_decode($address));
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
        } else {
            $validator->getErrors(); // contains the errors
            $validator->getValues(); // can be used to repopulate the form

            $response = [
                'error' => $validator->getErrors(),
                'value' => $validator->getValues()
            ];

            $this->app->json(['response' => $response]);
        }
    }
}
