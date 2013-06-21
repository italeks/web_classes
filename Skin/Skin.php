<?php

namespace Skin;


class Skin
{
	private $params = array();

	public function __construct(){}

	public function isWordpress()
	{
		return defined('WP_USE_THEMES');
	}

	public function isAffiliateSite()
	{
		return stristr($_SERVER["SERVER_NAME"], "affiliate");
	}


	public function get($key)
	{

		if (isset($this->params[$key])) {
			return $this->params[$key];
		}

		return null;
	}

	public function set($key, $value)
	{
		$this->params[$key] = $value;
	}

	public function has($key)
	{
		return array_key_exists($key, $this->params);
	}

	public function all()
	{
		return $this->params;
	}

}
