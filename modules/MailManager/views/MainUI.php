<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

class MailManager_MainUI_View extends MailManager_Abstract_View
{

    /**
     * Process the request for displaying UI
     * This process is used after save mail config
     * Operation name is "mainui"
     * @param Vtiger_Request $request
     * @return MailManager_Response
     * @throws AppException
     * @global String $moduleName
     */
    public function process(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();
        $response = new MailManager_Response();
        $viewer = $this->getViewer($request);

        if ($this->hasMailboxModel()) {
            $connector = $this->getConnector();

            if ($connector->hasError()) {
                $viewer->assign('ERROR', $connector->lastError());
            } else {
                $folders = $connector->getFolders();
                $connector->updateFolders();
                $viewer->assign('FOLDERS', $folders);
            }

            $this->closeConnector();
        }

        $viewer->assign('MODULE', $moduleName);
        $content = $viewer->view('MainUI.tpl', $moduleName, true);
        $response->setResult(['mailbox' => $this->hasMailboxModel(), 'ui' => $content]);

        return $response;
    }

    public function validateRequest(Vtiger_Request $request)
    {
        return $request->validateReadAccess();
    }
}