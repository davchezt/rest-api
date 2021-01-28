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
use app\SQL;
use ORM;

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
            ->limit(1)
            ->find_array();

        return $result ? $result : [];

        /*SQL::open();
        $dbo = SQL::$db->prepare("SELECT `user`.`id`, `user`.`username`, `user`.`type`, `user`.`join_date`, `profile`.`name`, `profile`.`dob`, `profile`.`email`, `profile`.`gender`, `profile`.`address` FROM `user` LEFT JOIN `profile` ON (`profile`.`id_user` = `user`.`id`) WHERE `user`.`id` = :id LIMIT 1");
        $dbo->bindValue(':id', $id, \PDO::PARAM_INT);
        $dbo->execute();
        $user = $dbo->fetch(\PDO::FETCH_OBJ);
        SQL::close();
      
        return $user ? (array)$user : [];*/
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

        /*SQL::open();
        $dbo = SQL::$db->prepare("SELECT `user`.`id`, `user`.`username`, `user`.`type`, `user`.`join_date`, `profile`.`name`, `profile`.`dob`, `profile`.`email`, `profile`.`gender`, `profile`.`address` FROM `user` LEFT JOIN `profile` ON (`profile`.`id_user` = `user`.`id`) WHERE `user`.`id` <> :id ORDER BY `id`");
        $dbo->bindValue(':id', $this->id, \PDO::PARAM_INT);
        $dbo->execute();
        $users = $dbo->fetchAll(\PDO::FETCH_ASSOC);
        SQL::close();

        return (array)$users;*/
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

        /*SQL::open();
        $dbo = SQL::$db->prepare("SELECT `user`.`id`, `user`.`username`, `user`.`type`, `user`.`join_date`, `profile`.`name`, `profile`.`dob`, `profile`.`email`, `profile`.`gender`, `profile`.`address` FROM `user` LEFT JOIN `profile` ON (`profile`.`id_user` = `user`.`id`) WHERE `user`.`id` <> :id AND `user`.`id` > :offset ORDER BY `id` ASC LIMIT :limit");
        $dbo->bindValue(':id', $this->id, \PDO::PARAM_INT);
        $dbo->bindParam(':offset', $offset, \PDO::PARAM_INT);
        $dbo->bindParam(':limit', $limit, \PDO::PARAM_INT);
        $dbo->execute();
        $users = $dbo->fetchAll(\PDO::FETCH_ASSOC);
        SQL::close();

        return (array)$users;*/
    }

    public function getCount() : int
    {
        $id = $this->id;
        $type = 1;

        return (int)$this->orm()
            ->where_not_equal('id', $id)
            ->count();
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

    public function checkLogin($username, $password)
    {
        $id = -1;
        if ($this->checkUsername($username) === false) {
            return $id;
        }

        SQL::open();
        $dbp = SQL::$db->query("SELECT `password` FROM `user` WHERE `username` = '{$username}'");
        $pass = $dbp->fetchColumn();

        if ($password == $pass) {
            $userId = SQL::$db->query("SELECT `id` FROM `user` WHERE `username` = '{$username}'");
            $id = (int)$userId->fetchColumn();
        }
        SQL::close();

        return $id;
    }

    public function checkUsername($username)
    {
        SQL::open();
        $dbc = SQL::$db->query("SELECT COUNT(*) FROM `user` WHERE `username` = '{$username}'");
        $count = (int)$dbc->fetchColumn();
        SQL::close();

        if ($count != 0) {
            return true;
        }

        return false;
    }

    public function checkEmail($email)
    {
        SQL::open();
        $dbc = SQL::$db->query("SELECT COUNT(*) FROM `profile` WHERE `email` = '{$email}'");
        $count = (int)$dbc->fetchColumn();
        SQL::close();

        if ($count != 0) {
            return true;
        }

        return false;
    }

    public function registerUser($username, $password, $name, $dob, $email, $gender, $address)
    {
        try {
            SQL::open();
            $anu = SQL::$db->prepare("INSERT INTO `user` (`id`, `username`, `password`, `type`, `join_date`) VALUES(null, :username, :password, '0', NOW())");
            $anu->bindParam(':username', $username, \PDO::PARAM_STR, 12);
            $anu->bindParam(':password', $password, \PDO::PARAM_STR, 12);
            $anu->execute();
            $id = SQL::$db->lastInsertId();
            SQL::close();

            if ($id) {
                SQL::open();
                $anu = SQL::$db->prepare("INSERT INTO `profile` (`id`, `id_user`, `name`, `dob`, `email`, `gender`, `address`) VALUES (NULL, :uid, :name, :dob, :email, :gender, :address)");
                $anu->bindParam(':uid', $id, \PDO::PARAM_INT);
                $anu->bindParam(':name', $name, \PDO::PARAM_STR, 12);
                $anu->bindParam(':dob', $dob, \PDO::PARAM_STR, 12);
                $anu->bindParam(':email', $email, \PDO::PARAM_STR, 12);
                $anu->bindParam(':gender', $gender, \PDO::PARAM_STR, 12);
                $anu->bindParam(':address', $address, \PDO::PARAM_STR, 12);
                $anu->execute();
                SQL::close();

                return $id;
            }
        } catch (\PDOException $ex) {
            // $ex->getMessage()
        }

        return -1;
    }
}
