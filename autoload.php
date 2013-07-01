<?php

define('DS', DIRECTORY_SEPARATOR);

spl_autoload_register(function ($className) {

	$filePath = str_replace('\\', DS, $className);

	if (file_exists(dirname(__FILE__) . DS . $filePath . '.php')) {

		require_once dirname(__FILE__) . DS . $filePath . '.php' ;
	}
});

