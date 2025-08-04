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

class Settings_PickListDependency_Index_Action extends Settings_Vtiger_Basic_Action
{
    function __construct()
    {
        parent::__construct();
        $this->exposeMethod('checkCyclicDependency');
    }

    public function checkCyclicDependency(Vtiger_Request $request)
    {
        $module = $request->get('sourceModule');
        $moduleModel = Vtiger_Module_Model::getInstance($module);
        $sourceField = $request->get('sourcefield');
        $targetField = $request->get('targetfield');
        $result = Vtiger_DependencyPicklist::checkCyclicDependency($module, $sourceField, $targetField);
        if ($result) {
            $currentSourceField = Vtiger_DependencyPicklist::getPicklistSourceField($module, $sourceField, $targetField);
            $currentSourceFieldModel = Vtiger_Field_Model::getInstance($currentSourceField, $moduleModel);
            $targetFieldModel = Vtiger_Field_Model::getInstance($targetField, $moduleModel);
            $errorMessage = vtranslate('LBL_CYCLIC_DEPENDENCY_ERROR', $request->getModule(false));
            $message = sprintf(
                $errorMessage,
                '"' . vtranslate($currentSourceFieldModel->get('label'), $module) . '"',
                '"' . vtranslate($targetFieldModel->get('label'), $module) . '"'
            );
        }
        $response = new Vtiger_Response();
        $response->setResult(['result' => $result, 'message' => $message]);
        $response->emit();
    }
}