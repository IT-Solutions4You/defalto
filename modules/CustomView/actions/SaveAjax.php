<?php
/************************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * Modifications and additions by IT-Solutions4You (ITS4YOU) are Copyright (c) IT-Solutions4You s.r.o.
 *
 * These contributions are licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class CustomView_SaveAjax_Action extends CustomView_Save_Action
{
    function __construct()
    {
        parent::__construct();
        $this->exposeMethod('updateColumns');
    }

    public function process(Vtiger_Request $request)
    {
        $response = new Vtiger_Response();
        $cvId = $request->get('record');
        if (!$cvId) {
            $response->setError('Filter Not specified');
            $response->emit();

            return;
        }

        $mode = $request->get('mode');
        if (!empty($mode)) {
            $this->invokeExposedMethod($mode, $request);

            return;
        }

        $customViewModel = CustomView_Record_Model::getInstanceById($cvId);
        $customViewModel->set('setdefault', $request->get('setdefault'));
        $customViewModel->save(true);
        $response->setResult(['id' => $cvId, 'isdefault' => $customViewModel->get('setdefault')]);
        $response->emit();
    }

    /**
     * Function to updated selected Custom view columns
     *
     * @param Vtiger_Request $request
     */
    public function updateColumns(Vtiger_Request $request)
    {
        $cvid = $request->get('record');
        $customViewModel = CustomView_Record_Model::getInstanceById($cvid);
        $response = new Vtiger_Response();
        if ($customViewModel) {
            $selectedColumns = $request->get('columnslist');
            $customViewModel->deleteSelectedFields();
            $customViewModel->saveSelectedFields($selectedColumns);
            /**
             * We are setting list_headers in session when we manage columns.
             * we should clear this from session in order to apply view
             */
            $listViewSessionKey = $customViewModel->getModule()->getName() . '_' . $cvid;
            Vtiger_ListView_Model::deleteParamsSession($listViewSessionKey, 'list_headers');
            $response->setResult(
                [
                    'message' => vtranslate('List columns saved successfully', $request->getModule()),
                    'listviewurl' => $customViewModel->getModule()->getListViewUrl() . '&viewname=' . $cvid
                ]
            );
        } else {
            $response->setError(vtranslate('Filter does not exist', $request->getModule()));
        }
        $response->emit();
    }
}