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

require_once('libraries/magpierss/rss_fetch.inc');

class Rss_Save_Action extends Vtiger_Save_Action
{
    public function requiresPermission(\Vtiger_Request $request)
    {
        $permissions[] = ['module_parameter' => 'module', 'action' => 'DetailView'];

        return $permissions;
    }

    public function process(Vtiger_Request $request)
    {
        $response = new Vtiger_Response();
        $moduleName = $request->getModule();
        $url = $request->get('feedurl');
        $recordModel = Rss_Record_Model::getCleanInstance($moduleName);
        $result = $recordModel->validateRssUrl($url);

        if ($result) {
            $recordModel->set('url', $url);
            $recordModel->save();
            $response->setResult(
                ['success' => true, 'message' => vtranslate('JS_RSS_SUCCESSFULLY_SAVED', $moduleName), 'id' => $recordModel->getId(), 'title' => $recordModel->get('rsstitle')]
            );
        } else {
            $response->setResult(['success' => false, 'message' => vtranslate('JS_INVALID_RSS_URL', $moduleName)]);
        }

        $response->emit();
    }
}
