<?php

include_once 'vendor/autoload.php';
include_once 'config.php';
include_once 'include/Webservices/Relation.php';
include_once 'vtlib/Vtiger/Module.php';
include_once 'includes/main/WebUI.php';

if (!session_id()) {
	session_start();
}

$authInstance = Core_Auth_Model::getInstance();
$authInstance->validateConfig();

if (empty($_SESSION['oauth2state'])) {
	$authInstance->redirectToProvider();
	exit('Redirected');
} elseif (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {
	unset($_SESSION['oauth2state']);
	unset($_SESSION['provider']);
	exit('Invalid state');
} elseif (empty($authInstance->getToken())) {
	$authInstance->retrieveToken();
	unset($_SESSION['oauth2state']);
}

$authInstance->viewAuthForm();