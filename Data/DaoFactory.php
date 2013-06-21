<?php

namespace Data;

class DaoFactory
{
	public function __construct(){}

	public function getDao()
	{
		return new WebApi\Dao(new WebApi\CurlConnector());
	}
}