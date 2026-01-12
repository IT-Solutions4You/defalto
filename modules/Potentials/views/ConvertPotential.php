<?php
/**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *********************************************************************************/
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * Modifications and additions by IT-Solutions4You (ITS4YOU) are Copyright (c) IT-Solutions4You s.r.o.
 *
 * These contributions are licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Potentials_ConvertPotential_View extends Vtiger_Index_View
{
    /**
     * @inheritDoc
     */
    public function requiresPermission(Vtiger_Request $request): array
    {
        $permissions = parent::requiresPermission($request);
        $permissions[] = ['module_parameter' => 'module', 'action' => 'DetailView', 'record_parameter' => 'record'];
        $permissions[] = ['module_parameter' => 'custom_module', 'action' => 'CreateView'];
        $request->set('custom_module', 'Project');

        return $permissions;
    }

    function process(Vtiger_Request $request)
    {
        $currentUserPriviligeModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();

        $viewer = $this->getViewer($request);
        $recordId = $request->get('record');
        $moduleName = $request->getModule();

        $recordModel = Vtiger_Record_Model::getInstanceById($recordId);
        $moduleModel = $recordModel->getModule();

        $viewer->assign('MODULE', $moduleName);
        $viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
        $viewer->assign('CURRENT_USER_PRIVILEGE', $currentUserPriviligeModel);
        $viewer->assign('RECORD', $recordModel);
        $viewer->assign('CONVERT_POTENTIAL_FIELDS', $recordModel->getConvertPotentialFields());

        $assignedToFieldModel = $moduleModel->getField('assigned_user_id');
        $assignedToFieldModel->set('fieldvalue', $recordModel->get('assigned_user_id'));
        $viewer->assign('ASSIGN_TO', $assignedToFieldModel);

        Core_Modifiers_Model::modifyForClass(get_class($this), 'process', $request->getModule(), $viewer, $request);

        $viewer->view('ConvertPotential.tpl', $moduleName);
    }
}