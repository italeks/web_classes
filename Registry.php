<?php


class Registry
{
	private static $items = array();

	public static function get($key)
	{
		if (!isset(self::$items[$key])) {
			self::get('logger')->alert('Component {component} not exists in Registry', array('component' => $key));
			throw new Exception('Undefined key "' . $key . '"');
		}

		return self::$items[$key];
	}

	public static function set($key, $value) {

		if (isset(self::$items[$key])) {
			throw new Exception('Key "' . $key . '" already exists');
		}

		self::$items[$key] = $value;

		return null;
	}

}