<?php
/**
 * @author      Raiza Rhamdan (Leonardo DaVchezt) <davchezt@gmail.com>
 * @copyright   Copyright (c), 2014 Raiza Rhamdan
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

	public function getById($id)
    {
        return $this->adapter->getById($id);
    }

    public function getAll()
    {
        return $this->adapter->getAll();
    }

    public function getCount()
    {
        return $this->adapter->getCount();
    }
}