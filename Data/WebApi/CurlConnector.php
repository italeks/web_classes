<?php

namespace Data\WebApi;

class CurlConnector implements ConnectorInterface
{
    const CONNECT_TIMEOUT = 3;

    public function send($data)
    {
        $settings = \Registry::get('settings');
        $url = $settings['BingoProxyURL'];

        $chanel = curl_init();

        curl_setopt($chanel, CURLOPT_URL, $url);
        curl_setopt($chanel, CURLOPT_POST, 1);
        curl_setopt($chanel, CURLOPT_POSTFIELDS, $data);
        curl_setopt($chanel, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($chanel, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($chanel, CURLOPT_SSL_VERIFYPEER, 0);

        curl_setopt($chanel,CURLOPT_CONNECTTIMEOUT, self::CONNECT_TIMEOUT);

        $responce = curl_exec ($chanel);

        $totalTime = curl_getinfo($chanel,CURLINFO_TOTAL_TIME);
        $connectTime = curl_getinfo($chanel,CURLINFO_CONNECT_TIME);

        curl_close($chanel);

        if ($responce === false) {
            \Registry::get('logger')->emergency('Failed to connect to WebApi {url}', array('url' => $url));
            throw new \Data\Exception\ConnectionException('Cant connect to WebApi');

        } elseif (!$responce || !$responceData = unserialize($responce)) {
            \Registry::get('logger')->alert('WebApi returned "{responce}"', array('responce' => $responce));
            throw new \Data\Exception\WrongResponceFormatException('Bad responce from WebApi');
        }

        return $responceData;

    }
}