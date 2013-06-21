<?php

namespace Http;

class Request
{

    //relative uri without domain and protocol
    private $requestUri;

    //full URL with protocol domain and port
    private $baseUrl;
    //GET
    public $query;
    //POST
    public $request;
    //COOKIE
    public $cookie;


    public function __construct()
    {
        $this->query = new ParameterBag($_GET);
        $this->request = new ParameterBag($_POST);
        $this->cookie = new ParameterBag($_COOKIE);

    }

    public function getBaseUrl()
    {
			return $this->getProtocol().$this->getHttpHost();
    }

    public function getProtocol()
    {
        return (!@$_SERVER['HTTPS'] || $_SERVER['HTTPS'] === 'off') ? 'http://' : 'https://' ;
    }

    public function getRequestUri()
    {
        if (null === $this->requestUri) {
            $this->requestUri = $this->prepareRequestUri();
        }

        return $this->requestUri;
    }

    protected function prepareRequestUri()
    {
        $requestUri = '';

        if (isset($_SERVER['HTTP_X_REWRITE_URL']) && false !== stripos(PHP_OS, 'WIN')) {

            $requestUri = $_SERVER['HTTP_X_REWRITE_URL'];

        } elseif (isset($_SERVER['REQUEST_URI'])) {
            $requestUri = $_SERVER['REQUEST_URI'];
        }


        return $requestUri;
    }

    public function getHttpHost()
    {
        $protocol = $this->getProtocol();
        $port   = $this->getPort();

			if (('http://' === $protocol && $port == 80) || ('https://' === $protocol && $port == 443)) {
            return $this->getHost();
        }

        return $this->getHost().':'.$port;
    }

    public function getPort()
    {
        return $_SERVER['SERVER_PORT'];
    }

    public function getHost()
    {

        if (!$host = @$_SERVER['HOST']) {
            if (!$host = @$_SERVER['SERVER_NAME']) {
                $host = @$_SERVER['SERVER_ADDR'];
            }
        }

        // host is lowercase as per RFC 952/2181
        return trim(strtolower($host));
    }

}