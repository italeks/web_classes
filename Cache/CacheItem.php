<?php

namespace Cache;


class CacheItem implements CacheItemInterface
{

	private $key, $value, $ttl;

	public function __construct($key, $value, $ttl)
	{
		$this->key = $key;
		$this->value = $value;
		$this->ttl = $ttl;
	}

	public function getValue()
	{
		return $this->value;
	}

	public function getKey()
	{
		return $this->key;
	}

	public function isValid()
	{
		return $this->value !== null && ($this->ttl - time()) > 0;
	}

}
