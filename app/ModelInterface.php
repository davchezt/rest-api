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
}