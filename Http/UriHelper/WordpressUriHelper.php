<?php

namespace Http\UriHelper;

class WordpressUriHelper implements UriHelper
{
	private $urlSufix = '/';
	private $urlEnding = '/';

	public function prepareUriByPageName($pageName)
	{
		return $this->urlSufix . $pageName . $this->urlEnding;
	}
}