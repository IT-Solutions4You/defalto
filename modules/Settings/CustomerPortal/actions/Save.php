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

class Settings_CustomerPortal_Save_Action extends Settings_Vtiger_Index_Action
{
    public function process(Vtiger_Request $request)
    {
        $qualifiedModuleName = $request->getModule(false);
        $enableModules = $request->get('enableModules');
        $defaultAssignee = $request->get('defaultAssignee');
        $response = new Vtiger_Response();
        if ($defaultAssignee && $enableModules) {
            $moduleModel = Settings_CustomerPortal_Module_Model::getInstance($qualifiedModuleName);
            $moduleModel->set('enableModules', $enableModules);
            $moduleModel->set('defaultAssignee', $defaultAssignee);
            $moduleModel->set('moduleSequence', $request->get('portalModulesInfo'));
            $moduleModel->set('support_notification', $request->get('renewalPeriod'));
            $moduleModel->set('announcement', $request->get('announcement'));
            $moduleModel->set('shortcuts', $request->get('defaultShortcuts'));
            $moduleModel->set('moduleFieldsInfo', $request->get('moduleFieldsInfo'));
            $moduleModel->set('relatedModuleList', $request->get('relatedModuleList'));
            $moduleModel->set('widgets', $request->get('activeWidgets'));
            $moduleModel->set('recordsVisible', $request->get('recordsVisible'));
            $moduleModel->set('recordPermissions', $request->get('recordPermissions'));
            $moduleModel->save();
            $response->setResult(['success' => true]);
        } else {
            $response->setResult(['success' => false]);
        }
        $response->emit();
    }

    /**
     * @inheritDoc
     */
    public function validateRequest(Vtiger_Request $request): bool
    {
        return $request->validateWriteAccess();
    }
}