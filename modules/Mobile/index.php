<?php
/***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

/**
 * Portions created by IT-Solutions4You s.r.o. are Copyright (C) IT-Solutions4You s.r.o.
 */

ini_set('error_reporting', '6135');
ini_set('display_warnings', 'On');
header('Content-Type: text/html;charset=utf-8');

chdir(__DIR__ . '/../../');

/**
 * URL Verfication - Required to overcome Apache mis-configuration and leading to shared setup mode.
 */
require_once 'config.php';
if (file_exists('config_override.php')) {
    include_once 'config_override.php';
}

include_once __DIR__ . '/api/Request.php';
include_once __DIR__ . '/api/Response.php';
include_once __DIR__ . '/api/Session.php';

include_once __DIR__ . '/api/ws/Controller.php';

include_once __DIR__ . '/Mobile.php';
include_once __DIR__ . '/html/Viewer.php';
require_once 'includes/main/WebUI.php';

class Mobile_Index_Controller
{
    /**
     * @param Mobile_API_Request $request
     */
    public static function process(Mobile_API_Request $request): void
    {
        $sessionid = Vtiger_Session::detectId();
        Mobile_API_Session::init($sessionid);

        try {
            $module = $request->get('module');
            $view = $request->get('view');

            if (empty($module)) {
                $module = 'Vtiger';
            }
            if (empty($view)) {
                $view = 'Home';
            }

            $requireLogin = true;

            if ($module === 'Users' && $view === 'Login') {
                $requireLogin = false;
            }

            if (preg_match("/[^a-zA-Z0-9_-]/", $module) ||
                preg_match("/[^a-zA-Z0-9_-]/", $view)) {
                throw new Exception("Invalid access");
            }

            if ($requireLogin && !Mobile_API_Session::get('_authenticated_user_id')) {
                $module = 'Users';
                $view = 'Login';
            }

            $viewer = new Mobile_HTML_Viewer();
            $html = $viewer->process($module, $view);
            $viewer->assign('MODULE', $module);

            if ($html) {
                echo $html;
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
}

/** Take care of stripping the slashes */
function stripslashes_recursive($value)
{
    return is_array($value) ? array_map('stripslashes_recursive', $value) : stripslashes($value);
}

if (get_magic_quotes_gpc()) {
    $_REQUEST = stripslashes_recursive($_REQUEST);
}
/** END **/

if (!defined('MOBILE_INDEX_CONTROLLER_AVOID_TRIGGER')) {
    Mobile_Index_Controller::process(new Mobile_API_Request($_REQUEST));
}