<?php
/**
 * @author      Raiza Rhamdan (Leonardo DaVchezt) <davchezt@gmail.com>
 * @copyright   Copyright (c), 2021 Raiza Rhamdan
 * @license		MIT public license
 */

namespace app;

use app\ModelInterface;

class Model
{
    protected $adapter;

    public function __construct() {}
	
	public function setAdapter(ModelInterface $adapter)
	{
		$this->adapter = $adapter;
	}

	public function getAdapter()
	{
		return $this->adapter;
	}

	public function getById($id = 0)
    {
        return $this->adapter->getById($id);
    }

    public function getAll()
    {
        return $this->adapter->getAll();
    }

    public function getList($start = 0, $limit = 30)
    {
        return $this->adapter->getList($start, $limit);
    }

    public function getCount()
    {
        return $this->adapter->getCount();
    }

    public function addData($param = array())
    {
        return $this->adapter->addData($param = array());
    }

    public function updateData($param = array())
    {
        return $this->adapter->updateData($param = array());
    }

    public function deleteData($param = array())
    {
        return $this->adapter->deleteData($param = array());
    }

    public function clearData()
    {
        $this->adapter->clearData();
    }
}