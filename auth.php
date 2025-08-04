<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
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
$authInstance->retrieveLoggedUser();

try {
    $authInstance->retrieveAuthClientId();
    $authInstance->validateConfig();
    $authInstance->authorizationProcess();
} catch (Exception $e) {
    $authInstance->setAuthorizationMessage($e->getMessage());
}

$authInstance->viewAuthForm();