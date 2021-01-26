<?php
/**
 * @author      Raiza Rhamdan (Leonardo DaVchezt) <davchezt@gmail.com>
 * @copyright   Copyright (c), 2014 Raiza Rhamdan
 * @license		MIT public license
 */

namespace app\Lib;

class R {
	private static $data = array();
	public static function get($key, $default = null)
	{
		if (self::has($key)) {
			return self::$data[$key];
		}

		return $default;
	}

	public static function prop($object, $key = false, $default = null)
	{
		if ($obj = self::get($object)) {
			return ($key != false ? $obj->{$key} : $obj);
		}

		return $default;
	}

	public static function set($key, $value)
	{
		self::$data[$key] = $value;
	}

	public static function has($key)
	{
		return isset(self::$data[$key]);
	}

	public static function __callStatic($method, $args = false)
	{
		if ($args and is_array($args)) {
			if (count($args) == 3) {
				return static::prop($method, $args[0])->{$args[1]}($args[2]);
			}
			else if (count($args) == 2) {
				return static::prop($method, $args[0])[$args[1]];
			}
			$args = $args[0];
		}
		
		return static::prop($method, $args);
	}
	
	public static function e($string)
	{
		static $flags;
		if (!isset($flags)) {
			$flags = ENT_QUOTES | (defined('ENT_SUBSTITUTE') ? ENT_SUBSTITUTE : 0);
		}
		
		return htmlspecialchars($string, $flags, 'UTF-8');		
	}
}