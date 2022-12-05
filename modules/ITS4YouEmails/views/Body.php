<?php
/*********************************************************************************
 * The content of this file is subject to the ITS4YouEmails license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/

class ITS4YouEmails_Body_View extends Vtiger_Basic_View
{
    public function requiresPermission(Vtiger_Request $request)
    {
        $request->set('record_module', getSalesEntityType($request->get('record')));

        $permissions = [];
        $permissions[] = array('module_parameter' => 'record_module', 'action' => 'DetailView', 'record_parameter' => 'record');

        return $permissions;
    }

    public function preProcess(Vtiger_Request $request, $display = true)
    {
    }

    public function process(Vtiger_Request $request)
    {
        $field = $request->get('field', 'body');
        $recordModel = Vtiger_Record_Model::getInstanceById($request->get('record'), $request->get('record_module'));

        echo decode_html($recordModel->get($field));
    }

    public function postProcess(Vtiger_Request $request)
    {
    }
}