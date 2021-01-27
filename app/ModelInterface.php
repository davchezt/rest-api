<?php
/**
 * @author      Raiza Rhamdan (Leonardo DaVchezt) <davchezt@gmail.com>
 * @copyright   Copyright (c), 2021 Raiza Rhamdan
 * @license		MIT public license
 */

namespace app;

defined("__DAVCHEZT") or die("{ \"response\" : \"error 403\"}");

interface ModelInterface
{
    public function getById($id = 0) : array;
    public function getAll() : array;
    public function getList($start = 0, $limit = 30) : array;
    public function getCount() : int;

    public function addData($param = array()) : int;
    public function updateData($param = array()) : int;
    public function deleteData($param = array()) : int;

    public function clearData() : void;
}