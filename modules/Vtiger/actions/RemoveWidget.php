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

class Vtiger_RemoveWidget_Action extends Vtiger_IndexAjax_View
{
    public function requiresPermission(Vtiger_Request $request)
    {
        if ($request->get('module') != 'Dashboard') {
            $request->set('custom_module', 'Dashboard');
            $permissions[] = ['module_parameter' => 'custom_module', 'action' => 'DetailView'];
        } else {
            $permissions[] = ['module_parameter' => 'module', 'action' => 'DetailView'];
        }

        return $permissions;
    }

    public function process(Vtiger_Request $request)
    {
        $currentUser = Users_Record_Model::getCurrentUserModel();
        $linkId = $request->get('linkid');
        $response = new Vtiger_Response();

        if ($request->has('widgetid')) {
            $widget = Vtiger_Widget_Model::getInstanceWithWidgetId($request->get('widgetid'), $currentUser->getId());
        } else {
            $widget = Vtiger_Widget_Model::getInstance($linkId, $currentUser->getId());
        }

        if (!$widget->isDefault()) {
            $widget->remove();
            $response->setResult(['linkid' => $linkId, 'name' => $widget->getName(), 'url' => $widget->getUrl(), 'title' => vtranslate($widget->getTitle(), $request->getModule())]
            );
        } else {
            $response->setError(vtranslate('LBL_CAN_NOT_REMOVE_DEFAULT_WIDGET', $moduleName));
        }
        $response->emit();
    }

    public function validateRequest(Vtiger_Request $request)
    {
        $request->validateWriteAccess();
    }
}