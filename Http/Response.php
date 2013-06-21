<?php

namespace Http;

use Http\UriHelper\UriHelperFactory as UriHelperFactory;

class Response
{
	// current request object
	private $request;

	private $queryParams = array();

	private $statusCode;

	private $headers;

	private $content;

	private $urlSuffix;

	private $urlEnding;

	public function __construct(Request $request)
	{
		$this->request = $request;
	}

	public function setContent($content)
	{
		$this->content = $content;
	}

	public function setStatusCode($statusCode) {

		$statusCode = intval($statusCode);

		if ($statusCode < 100 || $statusCode >= 600) {
			throw new Exception('Wrong HTTP Status Code');
		}

		$this->statusCode = $statusCode;
	}

	public function addQueryParam($key, $value)
	{
		$this->queryParams[$key] = $value;
	}

	public function redirect($url)
	{
		if ($this->statusCode &&  $this->statusCode >= 300 && $this->statusCode < 400) {

			header('Location: ' . $url, true, $this->statusCode);
		} else {

			header('Location: ' . $url);
		}

		echo $content;
		exit;
	}

	public function redirectByPageName($pageName)
	{
		$uriHelperFactory = new UriHelperFactory();

		$uriHelper = $uriHelperFactory->getUriHelper();

		$uri = $uriHelper->prepareUriByPageName($pageName);

		$url = $this->request->getBaseUrl() . $uri;

		$this->redirect($url);
	}


}