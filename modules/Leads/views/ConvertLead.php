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

class Leads_ConvertLead_View extends Vtiger_Index_View
{
    public function requiresPermission(\Vtiger_Request $request)
    {
        $permissions = parent::requiresPermission($request);
        $permissions[] = ['module_parameter' => 'module', 'action' => 'DetailView', 'record_parameter' => 'record'];
        $permissions[] = ['module_parameter' => 'module', 'action' => 'ConvertLead', 'record_parameter' => 'record'];

        return $permissions;
    }

    function process(Vtiger_Request $request)
    {
        $currentUserPriviligeModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();

        $viewer = $this->getViewer($request);
        $recordId = $request->get('record');
        $moduleName = $request->getModule();

        $recordModel = Vtiger_Record_Model::getInstanceById($recordId);
        $imageDetails = $recordModel->getImageDetails();
        if (php7_count($imageDetails)) {
            $imageAttachmentId = $imageDetails[0]['id'];
            $viewer->assign('IMAGE_ATTACHMENT_ID', $imageAttachmentId);
        }
        $moduleModel = $recordModel->getModule();

        $viewer->assign('MODULE', $moduleName);
        $viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
        $viewer->assign('CURRENT_USER_PRIVILEGE', $currentUserPriviligeModel);
        $viewer->assign('RECORD', $recordModel);
        $viewer->assign('CONVERT_LEAD_FIELDS', $recordModel->getConvertLeadFields());

        $assignedToFieldModel = $moduleModel->getField('assigned_user_id');
        $assignedToFieldModel->set('fieldvalue', $recordModel->get('assigned_user_id'));
        $viewer->assign('ASSIGN_TO', $assignedToFieldModel);

        $potentialModuleModel = Vtiger_Module_Model::getInstance('Potentials');
        $accountField = Vtiger_Field_Model::getInstance('related_to', $potentialModuleModel);
        $contactField = Vtiger_Field_Model::getInstance('contact_id', $potentialModuleModel);
        $viewer->assign('ACCOUNT_FIELD_MODEL', $accountField);
        $viewer->assign('CONTACT_FIELD_MODEL', $contactField);

        $contactsModuleModel = Vtiger_Module_Model::getInstance('Contacts');
        $accountField = Vtiger_Field_Model::getInstance('account_id', $contactsModuleModel);
        $viewer->assign('CONTACT_ACCOUNT_FIELD_MODEL', $accountField);

        Core_Modifiers_Model::modifyForClass(get_class($this), 'process', $request->getModule(), $viewer, $request);

        $viewer->view('ConvertLead.tpl', $moduleName);
    }
}