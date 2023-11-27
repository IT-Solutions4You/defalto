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
    /**
     * @param Vtiger_Request $request
     * @param bool $display
     * @return void
     */
    public function preProcess(Vtiger_Request $request, $display = true)
    {
    }

    /**
     * @param Vtiger_Request $request
     * @return void
     */
    public function process(Vtiger_Request $request)
    {
        $field = $request->get('field', 'body');
        $recordModel = Vtiger_Record_Model::getInstanceById($request->get('record'), $request->get('record_module'));
        $body = decode_html($recordModel->get($field));

        echo ITS4YouEmails_Utils_Helper::updateShorUrlData($body);
    }

    /**
     * @param Vtiger_Request $request
     * @return void
     */
    public function postProcess(Vtiger_Request $request)
    {
    }

    /**
     * @param Vtiger_Request $request
     * @return true
     * @throws AppException
     */
    public function checkPermission(Vtiger_Request $request)
    {
        $permissions = $this->requiresPermission($request);

        foreach ($permissions as $permission) {
            if (array_key_exists('module_parameter', $permission)) {
                if ($request->has($permission['module_parameter']) && !empty($request->get($permission['module_parameter']))) {
                    $moduleParameter = $request->get($permission['module_parameter']);
                } elseif ($request->has('record') && !empty($request->get('record'))) {
                    $moduleParameter = getSalesEntityType($request->get('record'));
                }
            } else {
                $moduleParameter = 'module';
            }

            if (array_key_exists('record_parameter', $permission)) {
                $recordParameter = $request->get($permission['record_parameter']);
            } else {
                $recordParameter = '';
            }

            if (!Users_Privileges_Model::isPermitted($moduleParameter, $permission['action'], $recordParameter)) {
                throw new AppException(vtranslate('LBL_PERMISSION_DENIED'));
            }
        }

        return true;
    }

    /**
     * @param Vtiger_Request $request
     * @return array
     */
    public function requiresPermission(Vtiger_Request $request)
    {
        $request->set('record_module', getSalesEntityType($request->get('record')));

        $permissions = [];
        $permissions[] = array('module_parameter' => 'record_module', 'action' => 'DetailView', 'record_parameter' => 'record');

        return $permissions;
    }
}