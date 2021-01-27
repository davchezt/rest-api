<?php
/**
 * @author      Raiza Rhamdan (Leonardo DaVchezt) <davchezt@gmail.com>
 * @copyright   Copyright (c), 2021 Raiza Rhamdan
 * @license		MIT public license
 */

namespace app\Lib;

defined("__DAVCHEZT") or die("{ \"response\" : \"error 403\"}");

class Database {
	private $db, $dsn, $user, $password;

	public function __construct($dsn, $user, $password)
	{
		$this->dsn = $dsn;
		$this->user = $user;
		$this->password = $password;
	}

	public function __destruct()
	{
		if($this->db)
			$this->close();
	}

	public function open()
	{
		try {
            $this->db = new \PDO($this->dsn, $this->user, $this->password);
            if ($this->db) {
                $this->db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                $this->db->query("SET NAMES utf8mb4");
            }
		} catch(\PDOException $e) {
			throw new \Exception(sprintf('Database Error: %s', $e->getMessage()));
		}
	}

	public function close()
	{
		$this->db = NULL;
	}

	public function db()
	{
		return $this->db;
	}
}