<?php
/**
 * @author      Raiza Rhamdan (Leonardo DaVchezt) <davchezt@gmail.com>
 * @copyright   Copyright (c), 2014 Raiza Rhamdan
 * @license		MIT public license
 */

namespace app\Adapter;

use app\ModelInterface;

class User implements ModelInterface
{
    public function getById($id) : array
    {
        $user = [
            'id' => 1,
            'username' => 'davchezt',
            'email' => 'davchezt@gamil.com'
        ];

        return $user;
    }

    public function getAll() : array
    {
        $users = [
            ['id' => 1, 'username' => 'davchezt', 'email' => 'davchezt@gamil.com'],
            ['id' => 2, 'username' => 'vchezt', 'email' => 'chezt.v@live.com'],
            ['id' => 3, 'username' => 'raiza', 'email' => 'raiza.rhamdan@gamil.com']
        ];

        return $users;
    }

    public function getCount() : int
    {
        $users = [
            ['id' => 1, 'username' => 'davchezt', 'email' => 'davchezt@gamil.com'],
            ['id' => 2, 'username' => 'vchezt', 'email' => 'chezt.v@live.com'],
            ['id' => 3, 'username' => 'raiza', 'email' => 'raiza.rhamdan@gamil.com']
        ];

        return count($users);
    }
}