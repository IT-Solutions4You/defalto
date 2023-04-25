<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Vtiger_SaveSharingRecord_Action extends Vtiger_Save_Action
{
    /**
     * @param Vtiger_Request $request
     */
    public function process(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();
        $recordId = $request->get('record');

        $recordModel = new Vtiger_SharingRecord_Model();

        if ($recordModel) {
            $recordModel->set('record', $request->get('record'));
            $recordModel->set('memberViewList', $request->get('memberViewList'));
            $recordModel->set('memberEditList', $request->get('memberEditList'));
            $recordModel->save();
        }

        $redirectUrl = $recordModel->getDetailViewUrl($moduleName, $recordId);

        header("Location: $redirectUrl");
    }

    /**
     * @param Vtiger_Request $request
     *
     * @return bool|void
     * @throws Exception
     */
    public function validateRequest(Vtiger_Request $request)
    {
        $request->validateWriteAccess();
    }
}