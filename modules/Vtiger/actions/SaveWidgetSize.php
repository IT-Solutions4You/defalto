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

class Vtiger_SaveWidgetSize_Action extends Vtiger_IndexAjax_View
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

        $id = $request->get('id');
        $tabId = $request->get('tabid');
        $size = Zend_Json::encode($request->get('size'));
        [$linkid, $widgetid] = explode('-', $id);

        if ($widgetid) {
            Vtiger_Widget_Model::updateWidgetSize($size, null, $widgetid, $currentUser->getId(), $tabId);
        } else {
            Vtiger_Widget_Model::updateWidgetSize($size, $linkid, null, $currentUser->getId(), $tabId);
        }

        $response = new Vtiger_Response();
        $response->setResult(['Save' => 'OK']);
        $response->emit();
    }
}