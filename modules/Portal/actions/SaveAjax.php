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

class Portal_SaveAjax_Action extends Vtiger_SaveAjax_Action
{
    public function requiresPermission(\Vtiger_Request $request)
    {
        $permissions[] = ['module_parameter' => 'module', 'action' => 'DetailView', 'record_parameter' => 'record'];

        return $permissions;
    }

    public function process(Vtiger_Request $request)
    {
        $module = $request->getModule();
        $recordId = $request->get('record');
        $bookmarkName = $request->get('bookmarkName');
        $bookmarkUrl = $request->get('bookmarkUrl');

        Portal_Module_Model::savePortalRecord($recordId, $bookmarkName, $bookmarkUrl);

        $response = new Vtiger_Response();
        $result = ['message' => vtranslate('LBL_BOOKMARK_SAVED_SUCCESSFULLY', $module)];
        $response->setResult($result);
        $response->emit();
    }
}