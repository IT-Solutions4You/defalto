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

class Vtiger_ShowWidget_View extends Vtiger_IndexAjax_View
{
    /**
     * @inheritDoc
     */
    public function requiresPermission(Vtiger_Request $request): array
    {
        $permissions = parent::requiresPermission($request);
        if ($request->get('module') != 'Dashboard') {
            $request->set('custom_module', 'Dashboard');
            $permissions[] = ['module_parameter' => 'custom_module', 'action' => 'DetailView'];
        } else {
            $permissions[] = ['module_parameter' => 'module', 'action' => 'DetailView'];
        }

        return $permissions;
    }

    function process(Vtiger_Request $request)
    {
        $currentUser = Users_Record_Model::getCurrentUserModel();

        $moduleName = $request->getModule();
        $componentName = $request->get('name');
        $linkId = $request->get('linkid');
        if (!empty($componentName)) {
            $className = Vtiger_Loader::getComponentClassName('Dashboard', $componentName, $moduleName);
            if (!empty($className)) {
                if (!empty($linkId)) {
                    $widget = new Vtiger_Widget_Model();
                    $widget->set('linkid', $linkId);
                    $widget->set('userid', $currentUser->getId());
                    $widget->set('filterid', $request->get('filterid', null));

                    $dasbBoardModel = Vtiger_DashBoard_Model::getInstance($moduleName);
                    $defaultTab = $dasbBoardModel->getUserDefaultTab($currentUser->getId());
                    $widget->set('tabid', $request->get('tab', $defaultTab['id']));

                    if ($request->has('data')) {
                        $widget->set('data', $request->get('data'));
                    }

                    $widget->add();

                    if ($request->get('widgetid')) {
                        $widget->set('id', $request->get('widgetid'));
                    }

                    $request->set('widgetid', $widget->get('id'));
                }

                //Date conversion from user format to database format
                $createdTime = $request->get('createdtime');
                //user format dates should be used in getSearchParams() api
                $request->set('dateFilter', $createdTime);
                $dates = [];

                if (!empty($createdTime)) {
                    $startDate = Vtiger_Date_UIType::getDBInsertedValue($createdTime['start']);
                    $dates['start'] = getValidDBInsertDateTimeValue($startDate . ' 00:00:00');
                    $endDate = Vtiger_Date_UIType::getDBInsertedValue($createdTime['end']);
                    $dates['end'] = getValidDBInsertDateTimeValue($endDate . ' 23:59:59');
                }
                $request->set('createdtime', $dates);

                $currentUserPrivilegeModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
                if ($currentUserPrivilegeModel->hasModulePermission(getTabid($moduleName)) && !Vtiger_Runtime::isRestricted('modules', $moduleName)) {
                    $classInstance = new $className();
                    $classInstance->process($request);
                } else {
                    throw new Exception(vtranslate('LBL_PERMISSION_DENIED'));
                }

                return;
            }
        }

        $response = new Vtiger_Response();
        $response->setResult(['success' => false, 'message' => vtranslate('NO_DATA')]);
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