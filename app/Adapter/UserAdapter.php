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
    private $id = 0;

    public function __construct($ignoreId)
    {
        $this->id = $ignoreId;
    }

    public function getById($id = 0) : array
    {
        SQL::open();
        $dbo = SQL::$db->prepare("SELECT `user`.`id`, `user`.`username`, `user`.`jwt`, `user`.`type`, `user`.`active`, `user`.`join_date`, `verify`.`code`, `profile`.`name`, `profile`.`dob`, `profile`.`email`, `profile`.`gender`, `profile`.`address` FROM `user` LEFT JOIN `profile` ON (`profile`.`id_user` = `user`.`id`) LEFT JOIN `verify` ON (`verify`.`id_user` = `user`.`id`) WHERE `user`.`id` = :id LIMIT 1");
        $dbo->bindValue(':id', $id, \PDO::PARAM_INT);
        $dbo->execute();
        $user = $dbo->fetch(\PDO::FETCH_OBJ);
        SQL::close();
      
        return $user ? (array)$user : [];
    }

    public function getAll() : array
    {
        $type = '0';
        SQL::open();
        $dbo = SQL::$db->prepare("SELECT `user`.`id`, `user`.`username`, `user`.`type`, `user`.`join_date` , `profile`.`name`, `profile`.`dob`, `profile`.`email`, `profile`.`gender`, `profile`.`address` FROM `user` LEFT JOIN `profile` ON (`profile`.`id_user` = `user`.`id`) WHERE `user`.`type` = :type AND `user`.`id` <> :id ORDER BY `id`");
        $dbo->bindValue(':id', $this->id, \PDO::PARAM_INT);
        $dbo->bindParam(':type', $type, \PDO::PARAM_STR, 12);
        $dbo->execute();
        $users = $dbo->fetchAll(\PDO::FETCH_ASSOC);
        SQL::close();

        return (array)$users;
    }

    public function getList($offset = 0, $limit = 30) : array
    {
        SQL::open();
        $dbo = SQL::$db->prepare("SELECT `user`.`id`, `user`.`username`, `user`.`type`, `user`.`join_date` , `profile`.`name`, `profile`.`dob`, `profile`.`email`, `profile`.`gender`, `profile`.`address` FROM `user` LEFT JOIN `profile` ON (`profile`.`id_user` = `user`.`id`) WHERE `user`.`id` <> :id ORDER BY `id` ASC LIMIT :offset, :limit");
        $dbo->bindValue(':id', $this->id, \PDO::PARAM_INT);
        $dbo->bindParam(':offset', $offset, \PDO::PARAM_INT);
        $dbo->bindParam(':limit', $limit, \PDO::PARAM_INT);
        $dbo->execute();
        $users = $dbo->fetchAll(\PDO::FETCH_ASSOC);
        SQL::close();

        return (array)$users;
    }

    public function getCount() : int
    {
        $id = $this->id;
        $type = 0;

        SQL::open();
        $dbq = SQL::$db->query("SELECT COUNT(*) FROM `user` WHERE `user`.`type` = '{$type}' AND `user`.`id` <> '{$id}'");
        $count = $dbq->fetchColumn();
        SQL::close();

        return $count;
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
