<?php
/*
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

include_once 'vendor/autoload.php';
include_once 'config.php';
include_once 'include/Webservices/Relation.php';
include_once 'vtlib/Vtiger/Module.php';
include_once 'includes/main/WebUI.php';

if (!session_id()) {
    session_start();
}

$authInstance = Core_Auth_Model::getInstance();
$authInstance->retrieveAuthClientId();
$authInstance->validateConfig();
$authInstance->authorizationProcess();
$authInstance->viewAuthForm();