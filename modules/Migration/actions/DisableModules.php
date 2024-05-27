<?php
/**
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (c) vtiger.
 * Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
 * All Rights Reserved.
 */

class Migration_DisableModules_Action extends Vtiger_Action_Controller {

    /**
     * @param Vtiger_Request $request
     *
     * @return bool
     * @throws AppException
     */
    public function checkPermission(Vtiger_Request $request)
    {
        parent::checkPermission($request);
        $currentUserModel = Users_Record_Model::getCurrentUserModel();

        if (!$currentUserModel->isAdminUser()) {
            throw new AppException(vtranslate('LBL_PERMISSION_DENIED', 'Vtiger'));
        }

        return true;
    }

    public function process(Vtiger_Request $request) {
		$modulesList = $request->get('modulesList');
		if ($modulesList) {
			$moduleManagerModel = new Settings_ModuleManager_Module_Model();
			foreach ($modulesList as $moduleName) {
				$moduleManagerModel->disableModule($moduleName);
			}
		}

		header('Location: migrate/index.php');
	}

}
