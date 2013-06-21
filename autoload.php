<?php

define('DS', DIRECTORY_SEPARATOR);

require_once $CONFIG['BbrIncFolderPath'] . 'CommonData' . DS . 'Enums.php';
require_once $CONFIG['BbrIncFolderPath'] . 'CommonData' . DS . 'DataModels.php';
require_once $CONFIG['BbrIncFolderPath'] . "networkSettings.php" ;

/*
	generic files
*/
require_once $CONFIG['BbrIncFolderPath'] . 'request.php' ;
require_once $CONFIG['BbrIncFolderPath'] . 'dbClassStoneDirect.php';
require_once $CONFIG['BbrIncFolderPath'] . 'profanityCheck.php';
require_once $CONFIG['BbrIncFolderPath'] . 'NetAcuity' . DS . 'NetAcuity.php';

/*
	classes to work with SOAP Services (Modular)
*/

require_once $CONFIG['BbrIncFolderPath'] . 'WebServices' . DS . 'Controllers' . DS . 'wsController.php';




// it looks not relevant
if (stripos($_SERVER["SERVER_NAME"],"AFFILIATE.") == 0) {
	require_once($CONFIG["BbrIncFolderPath"]."dbClassStone.php");
}

spl_autoload_register(function ($className) {

	$filePath = str_replace('\\', DS, $className);

	if (file_exists(dirname(__FILE__) . DS . $filePath . '.php')) {

		require_once dirname(__FILE__) . DS . $filePath . '.php' ;
	}
});

