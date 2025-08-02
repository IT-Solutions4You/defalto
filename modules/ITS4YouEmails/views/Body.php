<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class ITS4YouEmails_Body_View extends Vtiger_Basic_View
{
    /**
     * @param Vtiger_Request $request
     * @param bool           $display
     *
     * @return void
     */
    public function preProcess(Vtiger_Request $request, $display = true)
    {
    }

    /**
     * @param Vtiger_Request $request
     *
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
     *
     * @return void
     */
    public function postProcess(Vtiger_Request $request)
    {
    }

    /**
     * @param Vtiger_Request $request
     *
     * @return true
     * @throws Exception
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
                throw new Exception(vtranslate('LBL_PERMISSION_DENIED'));
            }
        }

        return true;
    }

    /**
     * @param Vtiger_Request $request
     *
     * @return array
     */
    public function requiresPermission(Vtiger_Request $request)
    {
        $request->set('record_module', getSalesEntityType($request->get('record')));

        $permissions = [];
        $permissions[] = ['module_parameter' => 'record_module', 'action' => 'DetailView', 'record_parameter' => 'record'];

        return $permissions;
    }
}