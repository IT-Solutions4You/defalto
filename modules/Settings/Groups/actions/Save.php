<?php
/*************************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * Modifications and additions by IT-Solutions4You (ITS4YOU) are Copyright (c) IT-Solutions4You s.r.o.
 *
 * These contributions are licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Settings_Groups_Save_Action extends Settings_Vtiger_Index_Action
{
    public function process(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();
        $qualifiedModuleName = $request->getModule(false);
        $recordId = $request->get('record');

        $moduleModel = Settings_Vtiger_Module_Model::getInstance($qualifiedModuleName);
        if (!empty($recordId)) {
            $recordModel = Settings_Groups_Record_Model::getInstance($recordId);
        } else {
            $recordModel = new Settings_Groups_Record_Model();
        }
        if ($recordModel) {
            $recordModel->set('groupname', decode_html($request->get('groupname')));
            $recordModel->set('description', $request->get('description'));
            $recordModel->set('group_members', $request->get('members'));
            $recordModel->save();
        }

        $redirectUrl = $recordModel->getDetailViewUrl();
        header("Location: $redirectUrl");
    }

    /**
     * @inheritDoc
     */
    public function validateRequest(Vtiger_Request $request): bool
    {
        return $request->validateWriteAccess();
    }
}