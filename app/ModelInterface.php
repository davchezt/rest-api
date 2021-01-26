<?php
/**
 * @author      Raiza Rhamdan (Leonardo DaVchezt) <davchezt@gmail.com>
 * @copyright   Copyright (c), 2014 Raiza Rhamdan
 * @license		MIT public license
 */

namespace app;

interface ModelInterface
{
    public function getById($id) : array;
    public function getAll() : array;
    public function getCount() : int;
}