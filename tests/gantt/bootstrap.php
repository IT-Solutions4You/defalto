<?php
/** CLI-only bootstrap; never invoke the web request dispatcher or an installer. */
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

chdir(dirname(__DIR__, 2));
require_once 'vendor/autoload.php';
require_once 'config.php';
require_once 'include/Webservices/Relation.php';
require_once 'vtlib/Vtiger/Module.php';
require_once 'includes/main/WebUI.php';

$current_user = CRMEntity::getInstance('Users');
$current_user->retrieveCurrentUserInfoFromFile($adminId);
$current_language = $current_user->column_fields['language'] ?: 'en_us';
$adb = PearDatabase::getInstance();

if (!$current_user->id || $current_user->column_fields['is_admin'] !== 'on'
    || $current_user->column_fields['status'] !== 'Active') {
    throw new Exception('An active administrator is required via --user=ID.');
}
