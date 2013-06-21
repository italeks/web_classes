<?php

namespace Http\UriHelper;

class GenericUriHelper implements UriHelper
{
	private $urlSufix = '/skin/';
	private $urlEnding = '.php';

	public function prepareUriByPageName($pageName)
	{
		return $this->urlSufix . $pageName . $this->urlEning;
	}
}