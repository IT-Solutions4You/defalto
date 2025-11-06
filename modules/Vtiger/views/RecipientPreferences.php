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

class Vtiger_RecipientPreferences_View extends Vtiger_MassActionAjax_View
{
    public function process(Vtiger_Request $request)
    {
        $sourceModule = $request->getModule();
        $emailFieldsInfo = $this->getEmailFieldsInfo($sourceModule);
        $viewer = $this->getViewer($request);
        $viewer->assign('EMAIL_FIELDS_LIST', $emailFieldsInfo);
        $viewer->assign('MODULE', $request->getModule());
        $viewer->assign('SOURCE_MODULE', $sourceModule);

        Core_Modifiers_Model::modifyForClass(get_class($this), 'process', $request->getModule(), $viewer, $request);

        echo $viewer->view('RecipientPreferences.tpl', $request->getModule(), true);
    }

    protected function getEmailFieldsInfo($moduleName)
    {
        $emailFieldsInfo = [];
        $emailFieldsList = [];
        $recipientPrefModel = Vtiger_RecipientPreference_Model::getInstance($moduleName);
        if ($recipientPrefModel) {
            $prefs = $recipientPrefModel->getPreferences();
        }
        $sourceModuleModel = Vtiger_Module_Model::getInstance($moduleName);
        $emailFields = $sourceModuleModel->getFieldsByType('email');
        $emailFieldsPref = $prefs[$sourceModuleModel->getId()];

        foreach ($emailFields as $field) {
            if ($field->isViewable()) {
                if ($emailFieldsPref && in_array($field->getId(), $emailFieldsPref)) {
                    $field->set('isPreferred', true);
                }
                $emailFieldsList[$field->getName()] = $field;
            }
        }

        if (!empty($emailFieldsList)) {
            $emailFieldsInfo[$sourceModuleModel->getId()] = $emailFieldsList;
        }

        return $emailFieldsInfo;
    }
}