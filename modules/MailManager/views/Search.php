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

class MailManager_Search_View extends MailManager_Relation_View
{
    /**
     * Processes the request for search Operation
     *
     * @param Vtiger_Request $request
     *
     * @return boolean
     * @global <type>        $currentUserModel
     */
    public function process(Vtiger_Request $request)
    {
        $response = new MailManager_Response(true);
        $viewer = $this->getViewer($request);
        if ('popupui' == $this->getOperationArg($request)) {
            $viewer->view('Search.Popupui.tpl', 'MailManager');
            $response = false;
        } elseif ('email' == $this->getOperationArg($request)) {
            $searchTerm = $request->get('q');
            if (empty($searchTerm)) {
                $searchTerm = '%@';
            } // To avoid empty value of email to be filtered.
            else {
                $searchTerm = "%$searchTerm%";
            }

            $filteredResult = MailManager::lookupMailInVtiger($searchTerm, Users_Record_Model::getCurrentUserModel());

            MailManager_Utils_Helper::emitJSON($filteredResult);
            $response = false;
        }

        return $response;
    }
}