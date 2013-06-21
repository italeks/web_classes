<?php

namespace Http\UriHelper;

class UriHelperFactory
{
	public function getUriHelper()
	{
		return \Registry::get('skin')->isWordpress() ? new WordpressUriHelper() : new GenericUriHelper();
	}
}