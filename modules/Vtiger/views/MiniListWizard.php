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

class Vtiger_MiniListWizard_View extends Vtiger_Index_View
{
    public function requiresPermission(Vtiger_Request $request)
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
        $viewer = $this->getViewer($request);
        $moduleName = $request->getModule();

        $viewer->assign('MODULE_NAME', $moduleName);
        $viewer->assign('WIZARD_STEP', $request->get('step'));

        switch ($request->get('step')) {
            case 'step1':
                $modules = Vtiger_Module_Model::getSearchableModules();
                //Since comments is not treated as seperate module
                unset($modules['ModComments']);
                $viewer->assign('MODULES', $modules);
                break;
            case 'step2':
                $selectedModule = $request->get('selectedModule');
                $filters = CustomView_Record_Model::getAllByGroup($selectedModule, false);
                $viewer->assign('ALLFILTERS', $filters);
                break;
            case 'step3':
                $selectedModule = $request->get('selectedModule');
                $filterid = $request->get('filterid');

                $db = PearDatabase::getInstance();
                $generator = new EnhancedQueryGenerator($selectedModule, $currentUser);
                $generator->initForCustomViewById($filterid);

                $listviewController = new ListViewController($db, $currentUser, $generator);
                $moduleFields = $generator->getModuleFields();
                $fields = $generator->getFields();
                $headerFields = [];
                foreach ($fields as $fieldName) {
                    if (array_key_exists($fieldName, $moduleFields)) {
                        $fieldModel = $moduleFields[$fieldName];
                        if ($fieldModel->getPresence() == 1) {
                            continue;
                        }
                        $headerFields[$fieldName] = $fieldModel;
                    }
                }
                $viewer->assign('HEADER_FIELDS', $headerFields);
                $viewer->assign('LIST_VIEW_CONTROLLER', $listviewController);
                $viewer->assign('SELECTED_MODULE', $selectedModule);
                break;
        }

        $viewer->view('dashboards/MiniListWizard.tpl', $moduleName);
    }
}