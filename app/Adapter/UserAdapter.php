<?php
/**
 * @author      Raiza Rhamdan (Leonardo DaVchezt) <davchezt@gmail.com>
 * @copyright   Copyright (c), 2021 Raiza Rhamdan
 * @license		MIT public license
 */

namespace app\Adapter;

defined("__DAVCHEZT") or die("{ \"response\" : \"error 403\"}");

use app\BaseAdapter;
use app\ModelInterface;

class UserAdapter extends BaseAdapter implements ModelInterface
{
    private $id = 0;

    public function __construct($ignoreId)
    {
        parent::__construct('user');

        $this->id = $ignoreId;
    }

    public function getById($id = 0) : array
    {
        $id = intval($id);
        $result = $this->orm()
            ->select_many('user.id', 'user.username', 'user.type', 'user.join_date')
            ->select_many('profile.name', 'profile.dob', 'profile.email', 'profile.gender', 'profile.address')
            ->left_outer_join('profile', array('profile.id_user', '=', 'user.id'))
            ->where_id_is($id)
            ->find_one();

        return $result ? $result->as_array() : [];
    }

    public function getAll() : array
    {
        $id = intval($this->id);
        $result = $this->orm()
            ->select_many('user.id', 'user.username', 'user.type', 'user.join_date')
            ->select_many('profile.name', 'profile.dob', 'profile.email', 'profile.gender', 'profile.address')
            ->left_outer_join('profile', array('profile.id_user', '=', 'user.id'))
            ->where_not_equal('user.id', $id)
            ->find_array();

        return $result;
    }

    public function getList($offset = 0, $limit = 30) : array
    {
        $id = intval($this->id);
        $result = $this->orm()
                ->select_many('user.id', 'user.username', 'user.type', 'user.join_date')
                ->select_many('profile.name', 'profile.dob', 'profile.email', 'profile.gender', 'profile.address')
                ->left_outer_join('profile', array('profile.id_user', '=', 'user.id'))
                ->where_not_equal('user.id', $id)
                ->where_gt('user.id', $offset)
                ->limit($limit)
                ->find_array();

        return $result;
    }

    public function getCount() : int
    {
        $id = intval($this->id);

        return $this->orm()->where_not_equal('id', $id)->count();
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
        // WARNING
        $query = "TRUNCATE `user`";
        if ($this->raw($query)) {
            // OK
        }
    }

    public function checkLogin($username, $password)
    {
        $id = -1;
        if ($this->checkUsername($username) === false) {
            return $id;
        }

        $user = $this->orm()->where('username', $username)->find_one();
        $pass = $user ? $user->get('password') : null;

        if ($password == $pass) {
            $id = (int)$user->get('id');
        }

        return $id;
    }

    public function checkUsername($username)
    {
        $id = intval($this->id);

        $count = $this->orm()->where('username', $username)->count();

        if ($count != 0) {
            return true;
        }

        return false;
    }

    public function checkEmail($email)
    {
        $id = intval($this->id);

        $count = $this->orm('profile')->where('email', $email)->count();

        if ($count != 0) {
            return true;
        }

        return false;
    }

    public function registerUser($username, $password, $name, $dob, $email, $gender, $address)
    {
        try {
            $user = $this->orm()->create();
            $user->id = null;
            $user->username = $username;
            $user->password = $password;
            $user->set('type', 0);
            $user->set_expr('join_date', 'NOW()');
            $user->save();

            $id = $user->id();

            if ($id || $user->is_dirty('id')) { // is_dirty only for update

                $profile = $this->orm('profile')->create();
                $profile->id_user = $id;
                $profile->name = $name;
                $profile->dob = $dob;
                $profile->email = $email;
                $profile->gender = $gender;
                $profile->address = $address;
                $profile->save();

                if ($profile->id() || $profile->is_dirty('id')) { // is_dirty only for update
                    return $id;
                }

                return -1;
            }
        } catch (\PDOException $ex) {
            // $ex->getMessage()
        }

        return -1;
    }
}
