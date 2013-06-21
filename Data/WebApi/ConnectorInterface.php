<?php

namespace Data\WebApi;

interface ConnectorInterface
{
	public function send($data);
}