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

class MailManager_Folder_View extends MailManager_Abstract_View
{
    /**
     * Process the request for Folder opertions
     *
     * @param Vtiger_Request $request
     *
     * @return MailManager_Response
     * @throws Exception
     * @global <type>        $maxEntriesPerPage
     */
    public function process(Vtiger_Request $request)
    {
        $this->exposeMethod('getFoldersList');
        $this->exposeMethod('open');

        $response = new MailManager_Response();
        $mode = $this->getOperationArg($request);

        if (!empty($mode) && $this->isMethodExposed($mode)) {
            $response = $this->invokeExposedMethod($mode, $request, $response);
        }

        return $response;
    }

    public function getFoldersList(Vtiger_Request $request, $response)
    {
        $moduleName = $request->getModule();
        $viewer = $this->getViewer($request);

        if ($this->hasMailboxModel()) {
            /** @var MailManager_Connector_Connector $connector */
            $connector = $this->getConnector();

            if ($connector->isConnected()) {
                $connector->updateFolders();
                $viewer->assign('FOLDERS', $connector->getFolders());
            } elseif ($connector->hasError()) {
                $error = $connector->lastError();
                $response->isJSON(true);
                $response->setError(101, $error);
            }

            $this->closeConnector();
        }

        $viewer->assign('MODULE', $request->getModule());
        $response->setResult($viewer->view('FolderList.tpl', $moduleName, true));

        return $response;
    }

    /**
     * @throws Exception
     */
    public function open(Vtiger_Request $request, $response)
    {
        $currentUserModel = Users_Record_Model::getCurrentUserModel();
        $maxEntriesPerPage = vglobal('list_max_entries_per_page');
        $moduleName = $request->getModule();
        $query = $request->get('q');
        $folderName = $request->get('_folder');
        $type = $request->get('type');
        /**
         * @var MailManager_Connector_Connector $connector
         * @var MailManager_Folder_Model        $folder
         */
        $connector = $this->getConnector();
        $folder = $connector->getFolder($folderName);
        $page = intval($request->get('_page', 1));

        if (empty($query)) {
            $connector->retrieveFolderMails($folder, $page, $maxEntriesPerPage);
        } else {
            $connector->retrieveSearchMails($connector->formatQueryFromRequest($query, $type), $folder, $page, $maxEntriesPerPage);
        }

        $viewer = $this->getViewer($request);
        $viewer->assign('TYPE', $type);
        $viewer->assign('QUERY', $query);
        $viewer->assign('FOLDER', $folder);
        $viewer->assign('FOLDER_LIST', $connector->getFolderList());
        $viewer->assign('SEARCHOPTIONS', MailManager_Folder_Model::getSearchOptions());
        $viewer->assign('JS_DATEFORMAT', parse_calendardate());
        $viewer->assign('USER_DATE_FORMAT', $currentUserModel->get('date_format'));
        $viewer->assign('MODULE', $moduleName);
        $response->setResult($viewer->view('FolderOpen.tpl', $moduleName, true));

        return $response;
    }

    /**
     * @inheritDoc
     */
    public function validateRequest(Vtiger_Request $request): bool
    {
        return $request->validateWriteAccess();
    }
}