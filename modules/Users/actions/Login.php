<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

class Users_Login_Action extends Vtiger_Action_Controller {

	function loginRequired() {
		return false;
	}

	function checkPermission(Vtiger_Request $request) {
		return true;
	}

    /**
     * @throws Exception
     */
    function process(Vtiger_Request $request) {
		$username = $request->get('username');
		$password = $request->getRaw('password');

		$user = CRMEntity::getInstance('Users');
		$user->column_fields['user_name'] = $username;

		if ($user->doLogin($password)) {
			session_regenerate_id(true); // to overcome session id reuse.

			$userid = $user->retrieve_user_id($username);
			Vtiger_Session::set('AUTHUSERID', $userid);

			// For Backward compatability
			// TODO Remove when switch-to-old look is not needed
			$_SESSION['authenticated_user_id'] = $userid;
			$_SESSION['app_unique_key'] = vglobal('application_unique_key');
			$_SESSION['authenticated_user_language'] = vglobal('default_language');

			//Enabled session variable for KCFINDER 
			$_SESSION['KCFINDER'] = array(); 
			$_SESSION['KCFINDER']['disabled'] = false;
            $_SESSION['KCFINDER']['uploadURL'] = '../../../test/upload';
            $_SESSION['KCFINDER']['uploadDir'] = __DIR__ . '/../../../test/upload';
			$_SESSION['KCFINDER']['deniedExts'] = implode(" ", vglobal('upload_badext'));
			// End

			//Track the login History
            /** @var Users_Module_Model $moduleModel */
			$moduleModel = Users_Module_Model::getInstance('Users');
            $moduleModel->saveLoginHistory($username);
			//End

            if (isset($_SESSION['return_params'])) {
                $return_params = $_SESSION['return_params'];
                header('Location: index.php?' . urldecode($return_params));
                exit;
            }

            if ($moduleModel->isFirstLoginHistory($username)) {
                header('Location: index.php?module=Tour&view=Index');
                exit;
            }


            header ('Location: index.php');
			exit();
		} else {
			header ('Location: index.php?module=Users&parent=Settings&view=Login&error=login');
			exit;
		}
	}

}
