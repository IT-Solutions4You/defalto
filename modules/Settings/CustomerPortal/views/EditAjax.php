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

class Settings_CustomerPortal_EditAjax_View extends Settings_Vtiger_IndexAjax_View
{
    public function process(Vtiger_Request $request)
    {
        $sourceModule = $request->get('targetModule');
        $viewer = $this->getViewer($request);
        $qualifiedName = $request->getModule(false);
        $customerModuleModel = Settings_CustomerPortal_Module_Model::getInstance($qualifiedName);
        $customerModuleModel->set('sourceModule', $sourceModule);
        if ($sourceModule !== 'Dashboard') {
            $moduleModel = Vtiger_Module_Model::getInstance($sourceModule);
            $allFields = $moduleModel->getFields();
            $fieldModels = [];
            foreach ($allFields as $key => $value) {
                if (isFieldActive($sourceModule, $value->name) && $value->isViewableInDetailView()) {
                    $fieldModels[$key]['fieldname'] = $value->name;
                    $fieldModels[$key]['iseditable'] = Settings_CustomerPortal_Module_Model::isFieldCustomerPortalEditable($value->isEditable(), $value, $sourceModule);
                    $fieldModels[$key]['fieldlabel'] = vtranslate($value->label, $sourceModule);
                    $fieldModels[$key]['hasdefaultvalue'] = $value->hasDefaultValue();
                    $fieldModels[$key]['mandatory'] = $value->isMandatory();
                }
            }
            $selectedFields = $customerModuleModel->getSelectedFields($moduleModel->getId());
            $decodeSelectedFields = Zend_Json::decode(decode_html($selectedFields), true);
            //Marking all previously selected non-editable fields as non-editable for Portal.
            foreach ($decodeSelectedFields as $field => $val) {
                if ($fieldModels[$field]['iseditable'] && $decodeSelectedFields[$field]) {
                    $decodeSelectedFields[$field] = 1;
                } else {
                    $decodeSelectedFields[$field] = 0;
                }
            }
            $relatedModules = $customerModuleModel->getRelatedModules($sourceModule);
            $recordVisible = $customerModuleModel->getRecordVisiblity($moduleModel->getId());
            $recordPermissions = $customerModuleModel->getRecordPermissions(getTabid($sourceModule));
            $viewer->assign('ALLFIELDS', $fieldModels);
            $viewer->assign('RELATED_MODULES', $relatedModules);
            $viewer->assign('SELECTED_FIELDS', $decodeSelectedFields);
            $viewer->assign('RECORD_VISIBLE', $recordVisible);
            $viewer->assign('QUALIFIED_MODULE', $qualifiedName);
            $viewer->assign('MODULE', $sourceModule);
            $viewer->assign('RECORD_PERMISSIONS', $recordPermissions);

            Core_Modifiers_Model::modifyForClass(get_class($this), 'process', $request->getModule(), $viewer, $request);

            $viewer->view('PortalFields.tpl', $qualifiedName);
        } elseif ($sourceModule == 'Dashboard') {
            Core_Modifiers_Model::modifyForClass(get_class($this), 'process', $request->getModule(), $viewer, $request);

            $viewer->view('CustomerPortalDashboard.tpl', $qualifiedName);
        }
    }
}