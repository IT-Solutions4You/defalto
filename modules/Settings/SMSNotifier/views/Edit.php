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

class Settings_SMSNotifier_Edit_View extends Settings_Vtiger_IndexAjax_View
{
    public function process(Vtiger_Request $request)
    {
        $recordId = $request->get('record');
        $qualifiedModuleName = $request->getModule(false);

        if ($recordId) {
            $recordModel = Settings_SMSNotifier_Record_Model::getInstanceById($recordId, $qualifiedModuleName);
        } else {
            $recordModel = Settings_SMSNotifier_Record_Model::getCleanInstance($qualifiedModuleName);
        }

        $viewer = $this->getViewer($request);
        $viewer->assign('RECORD_ID', $recordId);
        $viewer->assign('RECORD_MODEL', $recordModel);
        $viewer->assign('EDITABLE_FIELDS', $recordModel->getEditableFields());
        $viewer->assign('PROVIDERS_FIELD_MODELS', Settings_SMSNotifier_ProviderField_Model::getAll());
        $viewer->assign('QUALIFIED_MODULE_NAME', $qualifiedModuleName);
        $viewer->assign('PROVIDERS', $recordModel->getModule()->getAllProviders());

        Core_Modifiers_Model::modifyForClass(get_class($this), 'process', $request->getModule(), $viewer, $request);

        $viewer->view('EditView.tpl', $qualifiedModuleName);
    }
}