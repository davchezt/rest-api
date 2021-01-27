<?php
/**
 * @author      Raiza Rhamdan (Leonardo DaVchezt) <davchezt@gmail.com>
 * @copyright   Copyright (c), 2021 Raiza Rhamdan
 * @license		MIT public license
 */

namespace app\Router;

use flight\Engine;

use app\Adapter\UserAdapter;
use app\BaseRouter;

class User extends BaseRouter
{
    public function __construct(Engine $app, $userId)
    {
        parent::__construct($app, $userId);
    }

    public function init()
    {
        $this->app->route('GET /user', function () { // http://localhost/rest/flight/user/?jsonp=console.log
            $user = new UserAdapter($this->id);
            $this->app->model()->setAdapter($user);

            $users = $this->app->model()->getAll();
            $response = [
                'users' => $users,
                'count' => count($users)
            ];

            $this->app->json(['response' => $response]);
        }, false, true);

        $this->app->route('GET /user/@id:[0-9]{1,10}', function ($id) {
            $user = new UserAdapter($this->id);
            $this->app->model()->setAdapter($user);
        
            $userData = $this->app->model()->getById($id);
            unset($userData['jwt']);
            unset($userData['active']);
            unset($userData['code']);

            $response = ['user' => $userData];
            $this->app->json(['response' => $response]);
        }, false, true);

        $this->app->route('GET|POST /user/@offset:[0-9]+/@limit:[0-9]+', function ($offset, $limit) {
            $user = new UserAdapter($this->id);
            $this->app->model()->setAdapter($user);
        
            $users = $this->app->model()->getList($offset, $limit);
            $response = [
                'users' => $users,
                'count' => count($users)
            ];
            $this->app->json(['response' => $response]);
        }, false, true);
    }
}