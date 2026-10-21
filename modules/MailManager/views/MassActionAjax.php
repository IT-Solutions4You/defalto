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

class MailManager_MassActionAjax_View extends Vtiger_MassActionAjax_View
{
    /**
     * @inheritDoc
     */
    public function requiresPermission(Vtiger_Request $request): array
    {
        return [];
    }

    /**
     * The e-mail fields of the selected records for the compose dialog.
     *
     * @param Vtiger_Request $request
     *
     * @return array module => record id => e-mail => field model
     */
    protected function getEmailFieldsInfo(Vtiger_Request $request)
    {
        $linkToModule = $request->get('linktomodule');
        $sourceModules = MailManager_EmailFields_Model::getSourceModules(
            !empty($linkToModule),
            $request->get('selected_ids'),
            $request->getModule()
        );
        $data = MailManager_EmailFields_Model::getEmailFieldsInfo($sourceModules, function () use ($request) {
            return $this->getRecordsListFromRequest($request);
        });

        $viewer = $this->getViewer($request);
        $viewer->assign('RECORDS_COUNT', $data['records_count']);

        if ($data['recipient_pref_enabled']) {
            $viewer->assign('RECIPIENT_PREF_ENABLED', true);
        }

        $viewer->assign('EMAIL_FIELDS', $data['email_fields']);

        $viewer->assign('PREF_NEED_TO_UPDATE', $this->isPreferencesNeedToBeUpdated($request));

        return $data['email_fields_info'];
    }

    function showComposeEmailForm(Vtiger_Request $request)
    {
        $moduleName = 'MailManager';
        $sourceModule = $request->getModule();
        $cvId = $request->get('viewname');
        $selectedIds = $request->get('selected_ids');
        $excludedIds = $request->get('excluded_ids');
        $step = $request->get('step');
        $relatedLoad = $request->get('relatedLoad');

        $emailFieldsInfo = $this->getEmailFieldsInfo($request);
        $viewer = $this->getViewer($request);
        $viewer->assign('EMAIL_FIELDS_INFO', $emailFieldsInfo);
        $viewer->assign('MODULE', $moduleName);
        $viewer->assign('SOURCE_MODULE', $sourceModule);
        $viewer->assign('VIEWNAME', $cvId);
        $viewer->assign('SELECTED_IDS', $selectedIds);
        $viewer->assign('EXCLUDED_IDS', $excludedIds);
        $viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
        $viewer->assign('SELECTED_EMAIL_SOURCE_MODULE', $sourceModule);

        $searchKey = $request->get('search_key');
        $searchValue = $request->get('search_value');
        $operator = $request->get('operator');
        if (!empty($operator)) {
            $viewer->assign('OPERATOR', $operator);
            $viewer->assign('ALPHABET_VALUE', $searchValue);
            $viewer->assign('SEARCH_KEY', $searchKey);
        }

        $searchParams = $request->get('search_params');
        if (!empty($searchParams)) {
            $viewer->assign('SEARCH_PARAMS', $searchParams);
        }

        $to = $request->get('to');
        if (!$to) {
            $to = [];
        }
        $viewer->assign('TO', $to);

        $parentModule = $request->get('sourceModule');
        $parentRecord = $request->get('sourceRecord');
        if (!empty($parentModule)) {
            $viewer->assign('PARENT_MODULE', $parentModule);
            $viewer->assign('PARENT_RECORD', $parentRecord);
            $viewer->assign('RELATED_MODULE', $sourceModule);
        }
        if ($relatedLoad) {
            $viewer->assign('RELATED_LOAD', true);
        }

        if ($step == 'step1') {
            echo $viewer->view('SelectEmailFields.tpl', $request->getModule(), true);
            exit;
        }
    }
}