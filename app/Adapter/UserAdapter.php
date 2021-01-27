<?php
/**
 * @author      Raiza Rhamdan (Leonardo DaVchezt) <davchezt@gmail.com>
 * @copyright   Copyright (c), 2021 Raiza Rhamdan
 * @license		MIT public license
 */

namespace app\Adapter;

defined("__DAVCHEZT") or die("{ \"response\" : \"error 403\"}");

use app\ModelInterface;
use app\SQL;

class UserAdapter implements ModelInterface
{
    public function __construct() {}

    public function getById($id = 0) : array
    {
        return [
            'id' => '1',
            'username' => 'davchezt',
            'email' => 'davchezt@domain.com'
        ];
    }

    public function getAll() : array
    {
        return [
            ['id' => '1', 'username' => 'davchezt', 'email' => 'davchezt@domain.com'],
            ['id' => '2', 'username' => 'davchezt2', 'email' => 'davchezt2@domain.com'],
            ['id' => '3', 'username' => 'davchezt3', 'email' => 'davchezt3@domain.com']
        ];
    }

    public function getList($offset = 0, $limit = 30) : array
    {
        $data = [
            ['id' => '1', 'username' => 'davchezt', 'email' => 'davchezt@domain.com'],
            ['id' => '2', 'username' => 'davchezt2', 'email' => 'davchezt2@domain.com'],
            ['id' => '3', 'username' => 'davchezt3', 'email' => 'davchezt3@domain.com']
        ];

        $arr = [];
        $limit = $limit > (count($data) - 1) ? count($data) : $limit;
        for($i = $offset; $i < $limit; $i++) {
            $arr[$i] = $data[$i];
        }

        return $arr;
    }

    public function getCount() : int
    {
        $data = [
            ['id' => '1', 'username' => 'davchezt', 'email' => 'davchezt@domain.com'],
            ['id' => '2', 'username' => 'davchezt2', 'email' => 'davchezt2@domain.com'],
            ['id' => '3', 'username' => 'davchezt3', 'email' => 'davchezt3@domain.com']
        ];

        return count($data);
    }

    public function addData($param = array()) : int
    {
        return -1;
    }

    public function updateData($param = array()) : int
    {
        return -1;
    }

    public function deleteData($param = array()) : int
    {
        return -1;
    }

    public function clearData() : void
    {
        // nothing
    }
}
