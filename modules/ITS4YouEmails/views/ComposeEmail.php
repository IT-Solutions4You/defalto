<?php
/*********************************************************************************
 * The content of this file is subject to the ITS4YouEmails license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/

class ITS4YouEmails_ComposeEmail_View extends Vtiger_ComposeEmail_View
{
    /** @var bool|EMAILMaker_EMAILContent_Model */
    public $EMAILContentModel = false;
    /**
     * @var bool
     */
    public $isPDFActive = false;
    /**
     * @var bool
     */
    public $emailListView = null;
    public $sourceRecordIds = [];
    public $recordId;

    public function checkPermission(Vtiger_Request $request)
    {
    }

    /**
     * @throws Exception
     */
    public function process(Vtiger_Request $request)
    {
        $this->composeMailData($request);

        $viewer = $this->getViewer($request);
        $viewer->view('ComposeEmailForm.tpl', $request->getModule());
    }

    /**
     * @throws Exception
     */
    public function composeMailData($request)
    {
        parent::composeMailData($request);

        $moduleName = $request->getModule();
        $qualifiedModuleName = $request->getModule(false);

        $emailTemplateIds = $this->getEmailTemplateIds($request);
        $emailTemplateLanguage = $request->get('email_template_language');

        $viewer = $this->getViewer($request);
        $viewer->assign('EMAIL_TEMPLATE_LANGUAGE', $emailTemplateLanguage);
        $viewer->assign('EMAIL_TEMPLATE_IDS', $emailTemplateIds);
        $viewer->assign('MODULE', $moduleName);
        $viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
        $viewer->assign('SOURCEMODULE', $this->getSourceModule($request));
        $viewer->assign('IS_MERGE_TEMPLATES', $request->get('is_merge_templates'));

        if (!$request->isEmpty('pdf_template_ids')) {
            $this->retrievePDFTemplates($request);
        }

        if ('RetrieveEmails' === $request->get('mode')) {
            $this->retrieveEmails($request);
        }

        $this->retrieveEmailAddresses($request);
        $this->retrieveEmailContent($request);
        $this->retrieveAttachments($request);
        $this->retrieveFromEmails($request);
        $this->retrieveSMTPRecords($request);
        $this->retrieveRecordDocumentsUrl($request);
        $this->retrieveDocumentsUrl($request);
    }

    public function retrieveRecordDocumentsUrl(Vtiger_Request $request)
    {
        $records = $this->getRecordsListFromRequest($request);
        $recordDocumentsUrl = '';

        if (1 === count((array)$records)) {
            $recordDocumentsUrl = 'module=ITS4YouEmails&view=Documents&mode=recordDocuments&record=' . $records[0];
        }

        $viewer = $this->getViewer($request);
        $viewer->assign('RECORD_DOCUMENTS_URL', $recordDocumentsUrl);
    }
    public function retrieveDocumentsUrl(Vtiger_Request $request)
    {
        $viewer = $this->getViewer($request);
        $viewer->assign('DOCUMENTS_URL', 'view=Popup&module=Documents&src_module=ITS4YouEmails&src_field=composeEmail');
    }

    public function getRecordsListFromRequest(Vtiger_Request $request, $model = false)
    {
        $moduleName = $request->get('module');
        $cvId = $request->get('viewname');
        $selectedIds = $request->get('selected_ids');
        $excludedIds = $request->get('excluded_ids');
        $tagParams = $request->get('tag_params');
        $tag = $request->get('tag');
        $listViewSessionKey = $moduleName . '_' . $cvId;

        if (!empty($tag)) {
            $listViewSessionKey .= '_' . $tag;
        }

        $orderParams = Vtiger_ListView_Model::getSortParamsSession($listViewSessionKey);

        if (!empty($tag) && empty($tagParams)) {
            $tagParams = $orderParams['tag_params'];
        }

        if (empty($tagParams)) {
            $tagParams = array();
        }

        if (!is_array($tagParams)) {
            $tagParams = array($tagParams);
        }

        if (!empty($selectedIds) && $selectedIds != 'all') {
            if (count((array)$selectedIds) > 0) {
                return $selectedIds;
            }
        }

        $searchParams = $request->get('search_params');

        if (empty($searchParams) && !is_array($searchParams)) {
            $searchParams = array();
        }

        $searchAndTagParams = array_merge($searchParams, $tagParams);
        $sourceRecord = $request->get('sourceRecord');
        $sourceModule = $request->get('sourceModule');

        if ($sourceRecord && $sourceModule) {
            $sourceRecordModel = Vtiger_Record_Model::getInstanceById($sourceRecord, $sourceModule);

            return $sourceRecordModel->getSelectedIdsList($request->get('parentModule'), $excludedIds);
        }

        $customViewModel = CustomView_Record_Model::getInstanceById($cvId);

        if ($customViewModel) {
            $searchKey = $request->get('search_key');
            $searchValue = $request->get('search_value');
            $operator = $request->get('operator');

            if (!empty($operator)) {
                $customViewModel->set('operator', $operator);
                $customViewModel->set('search_key', $searchKey);
                $customViewModel->set('search_value', $searchValue);
            }

            $customViewModel->set('search_params', $searchAndTagParams);

            return $customViewModel->getRecordIds($excludedIds);
        }

        return array();
    }

    /**
     * @throws Exception
     */
    public function retrieveSMTPRecords(Vtiger_Request $request)
    {
        $records = array();

        if (vtlib_isModuleActive('ITS4YouSMTP') && getTabid('ITS4YouSMTP')) {
            /** @var ITS4YouSMTP_Module_Model $moduleModel */
            $moduleModel = Vtiger_Module_Model::getInstance('ITS4YouSMTP');
            $records = $moduleModel->getRecords();
        }

        $viewer = $this->getViewer($request);
        $viewer->assign('SMTP_RECORDS', $records);
    }

    /**
     * @throws Exception
     */
    public function retrieveEmails(Vtiger_Request $request)
    {
        $sourceIds = $this->getSourceRecords($request);
        $recordId = $this->getRecordId($request);

        if (count($sourceIds)) {
            $this->retrieveEmailsForFields($request);
        }

        if (!empty($recordId)) {
            $this->retrieveEmailsForDetail($request);
        }
    }

    /**
     * @param Vtiger_Request $request
     * @throws Exception
     */
    public function retrieveEmailsForFields(Vtiger_Request $request)
    {
        $moduleName = $this->getSourceModule($request);
        $inventoryRecordId = $this->getRecordId($request);
        $recordModel = Vtiger_Record_Model::getInstanceById($inventoryRecordId, $moduleName);
        $inventoryModule = $recordModel->getModule();
        $inventoryFields = $inventoryModule->getFields();
        $toEmailConsiderableFields = array('contact_id', 'account_id', 'vendor_id');
        $db = PearDatabase::getInstance();
        $field_lists = array();

        foreach ($toEmailConsiderableFields as $fieldName) {
            if (!array_key_exists($fieldName, $inventoryFields)) {
                continue;
            }

            $fieldModel = $inventoryFields[$fieldName];

            if (!$fieldModel->isViewable()) {
                continue;
            }

            $fieldValue = $recordModel->get($fieldName);

            if (empty($fieldValue)) {
                continue;
            }

            $referenceModule = Vtiger_Functions::getCRMRecordType($fieldValue);
            $referenceModuleModel = Vtiger_Module_Model::getInstance($referenceModule);

            if (!$referenceModuleModel) {
                continue;
            }

            if (!empty($fieldValue) && isRecordExists($fieldValue)) {
                $referenceRecordModel = Vtiger_Record_Model::getInstanceById($fieldValue, $referenceModule);

                if ($referenceRecordModel->get('emailoptout')) {
                    continue;
                }
            }

            $emailFields = $referenceModuleModel->getFieldsByType('email');

            if (count($emailFields)) {
                continue;
            }

            $current_user = Users_Record_Model::getCurrentUserModel();
            $queryGenerator = new QueryGenerator($referenceModule, $current_user);
            $queryGenerator->setFields(array_keys($emailFields));
            $query = $queryGenerator->getQuery();
            $query .= ' AND crmid = ' . $fieldValue;
            $result = $db->pquery($query, array());

            if ($db->num_rows($result)) {
                continue;
            }

            foreach ($emailFields as $fieldName => $emailFieldModel) {
                $emailValue = $db->query_result($result, 0, $fieldName);

                if (!empty($emailValue)) {
                    $field_lists[] = implode('|', [$fieldValue, $fieldName, getSalesEntityType($fieldValue)]);
                    break 2;
                }
            }
        }

        if ($request->isEmpty('field_lists')) {
            $request->set('field_lists', $field_lists);
        }
    }

    /**
     * @param Vtiger_Request $request
     * @throws Exception
     */
    public function retrieveEmailsForDetail(Vtiger_Request $request)
    {
        $recordId = $this->getRecordId($request);
        $recordModule = $this->getSourceModule($request);
        $recordModel = Vtiger_Record_Model::getInstanceById($recordId, $recordModule);

        if ($recordModel) {
            $recordField = PDFMaker_Module_Model::getEmailFieldFromRecord($recordModel);

            if (empty($recordEmail)) {
                /** @var Vtiger_Field_Model $field */
                foreach ($recordModel->getModule()->getFieldsByType('reference') as $field) {
                    $refFieldName = $field->get('name');
                    $refRecordId = $recordModel->get($refFieldName);

                    if ($recordModel->isEmpty($refFieldName)) {
                        continue;
                    }

                    foreach ($field->getReferenceList() as $refModuleName) {
                        $refRecordModel = Vtiger_Record_Model::getInstanceById($refRecordId, $refModuleName);
                        $refRecordField = PDFMaker_Module_Model::getEmailFieldFromRecord($refRecordModel);

                        if (!empty($refRecordField)) {
                            $recordId = $refRecordId;
                            $recordField = $refRecordField;
                            $recordModule = $refModuleName;
                            break 2;
                        }
                    }
                }
            }

            if ($request->isEmpty('field_lists')) {
                $request->set('field_lists',
                    [
                        implode('|', [$recordId, $recordField, $recordModule])
                    ]
                );
            }
        }
    }

    public function getEmailTemplateIds(Vtiger_Request $request)
    {
        return $request->get('email_template_ids');
    }

    public function getSourceModule(Vtiger_Request $request)
    {
        if ($request->has('cid') && !$request->isEmpty('cid')) {
            return $request->get('parentModule');
        }

        return $request->get('sourceModule');
    }

    /**
     * @throws Exception
     */
    public function retrievePDFTemplates(Vtiger_Request $request)
    {
        $PDFTemplateIds = array_filter(explode(';', $request->get('pdf_template_ids')));

        if (count($PDFTemplateIds) > 0) {
            $this->isPDFActive = true;
            $PDFLanguage = $request->get('pdf_template_language', $request->get('email_template_language'));
            $PDFTemplatesList = ITS4YouEmails_Utils_Helper::validatePDFTemplates($PDFTemplateIds, [
                'records' => $this->getSourceRecords($request),
                'module' => $this->getSourceModule($request),
                'language' => $PDFLanguage,
            ]);
            $PDFTemplateIds = implode(';', array_keys($PDFTemplatesList));

            $viewer = $this->getViewer($request);
            $viewer->assign('PDF_TEMPLATE_IDS', $PDFTemplateIds);
            $viewer->assign('PDF_TEMPLATES', $PDFTemplatesList);
            $viewer->assign('PDF_TEMPLATE_LANGUAGE', $PDFLanguage);
        }
    }

    public function retrieveEmailAddresses(Vtiger_Request $request)
    {
        $sourceIds = $this->getSourceRecords($request);
        $recordId = $this->getRecordId($request);
        $isMoreSources = count($sourceIds) > 1;
        $isSingleRecord = !$isMoreSources;
        $sourceModule = $request->get('sourceModule');
        $selectedIds = $request->get('selected_ids');
        $to = $toMailInfo = $sourceNames = [];

        $viewer = $this->getViewer($request);
        $viewer->assign('SOURCERECORD', $isSingleRecord ? $recordId : null);

        $allFieldLists['to'] = $request->get('field_lists');
        $allFieldLists['cc'] = $request->get('field_lists_cc');
        $allFieldLists['bcc'] = $request->get('field_lists_bcc');
        $selectedSourceId = null;
        $sourceModules = ['Accounts', 'Contacts', 'Leads'];

        if ((in_array($sourceModule, $sourceModules) && !$isSingleRecord && !$this->isPDFActive && 'all' !== $selectedIds) || ($this->isEmailListView($request) && !$this->isPDFActive)) {
            $noGrouping = true;
            $isSingleRecord = true;
            $selectedSourceId = '0';
            $viewer->assign('SOURCE_IDS', ['0']);
            $sourceNames[0] = '';
        } else {
            $noGrouping = false;
            $viewer->assign('SOURCE_IDS', $sourceIds);
        }

        if (count($sourceIds) > 0) {
            foreach ($sourceIds as $sourceId) {
                $groupId = $noGrouping ? '0' : $sourceId;

                foreach ($allFieldLists as $fieldListType => $fieldLists) {
                    if (!isset($toMailInfo[$fieldListType][$groupId])) {
                        $toMailInfo[$fieldListType][$groupId] = array();
                    }
                    if (!isset($allMailNamesList[$fieldListType][$groupId])) {
                        $allMailNamesList[$fieldListType][$groupId] = array();
                    }
                    if (null === $selectedSourceId) {
                        $selectedSourceId = $sourceId;
                    }

                    $recordSourceModel = Vtiger_Record_Model::getInstanceById($sourceId, getSalesEntityType($sourceId));

                    if (!$noGrouping) {
                        $sourceNames[$sourceId] = $recordSourceModel->getName();
                    }

                    if (!empty($fieldLists)) {
                        foreach ($fieldLists as $fieldListEmailId) {
                            list($fieldListId, $fieldListField, $fieldListModule) = explode('|', $fieldListEmailId);

                            if ($fieldListId == $sourceId || empty($fieldListId)) {
                                $recordModel = $recordSourceModel;
                                $fieldListEmailId = $sourceId . '|' . $fieldListField . '|' . $fieldListModule;
                                $fieldListRecordId = $sourceId;
                            } else {
                                if (!$isSingleRecord || ($this->isEmailListView($request) && count($sourceIds) > 1) || $isMoreSources) {
                                    $parent_id = $recordSourceModel->get($fieldListId);

                                    if (!$parent_id) {
                                        continue;
                                    }

                                    $fieldListEmailId = $parent_id . '|' . $fieldListField . '|' . $fieldListModule;
                                    $fieldListRecordId = $parent_id;
                                } else {
                                    $fieldListRecordId = $fieldListId;
                                }

                                if ('Users' === $fieldListModule) {
                                    $recordModel = Users_Record_Model::getInstanceById($fieldListRecordId, 'Users');
                                } else {
                                    $fieldListRecordModule = getSalesEntityType($fieldListRecordId);

                                    if (empty($fieldListRecordModule) || empty($fieldListRecordId) || !isRecordExists($fieldListRecordId)) {
                                        continue;
                                    }

                                    $recordModel = Vtiger_Record_Model::getInstanceById($fieldListRecordId, $fieldListRecordModule);
                                }
                            }

                            $emailName = $recordModel->getName();
                            $emailRecordId = $recordModel->getId();
                            $emailAddress = $recordModel->get($fieldListField);
                            $emailOptOutValue = $recordModel->get('emailoptout');

                            if (!empty($emailAddress) && (!$emailOptOutValue || !$isMoreSources)) {
                                $emailAddressId = $fieldListRecordId . '|' . $emailAddress . '|' . $fieldListModule;
                                $toEmails[$fieldListType][$groupId][] = array(
                                    'id' => $fieldListRecordId,
                                    'name' => $emailName . ' <b>(' . $emailAddress . ')</b>',
                                    'emailid' => $emailAddress,
                                    'module' => $fieldListModule,
                                );
                                $to[$fieldListType][$groupId][$emailAddressId] = $emailName . ' <b>(' . $emailAddress . ')</b>';
                                $allMailNamesList[$fieldListType][$groupId][$emailAddressId][] = array(
                                    'id' => $fieldListEmailId,
                                    'recordid' => $emailRecordId,
                                    'sid' => $groupId,
                                    'label' => $emailName,
                                    'value' => $emailAddress,
                                    'module' => $fieldListModule,
                                );
                                $toMailInfo[$fieldListType][$groupId][$emailAddressId][] = $emailAddress;
                            }
                        }
                    }
                }
            }
        }

        $viewer->assign('SELECTED_SOURCEID', $selectedSourceId);
        $viewer->assign('SOURCE_NAMES', $sourceNames);
        $viewer->assign('SINGLE_RECORD', $isSingleRecord ? 'yes' : 'no');

        foreach (array('to', 'cc', 'bcc') as $t) {
            if (count((array)$to[$t]) > 0) {
                $viewer->assign(strtoupper($t), $to[$t]);
            }

            $viewer->assign(strtoupper($t) . '_EMAILS', $toEmails[$t]);
            $viewer->assign(strtoupper($t) . 'MAIL_INFO', $toMailInfo[$t]);
            $viewer->assign(strtoupper($t) . 'MAIL_NAMES_LIST', $allMailNamesList[$t]);
        }
    }

    public function getSourceRecords(Vtiger_Request $request)
    {
        if (empty($this->sourceRecordIds)) {
            if (!$request->isEmpty('record')) {
                $sourceIds = array($request->get('record'));
            } else {
                $sourceIds = $this->getRecordsListFromRequest($request);

                if (!is_array($sourceIds)) {
                    $sourceIds = array($sourceIds);
                }
            }

            $this->sourceRecordIds = $sourceIds;
        }

        return $this->sourceRecordIds;
    }

    public function getRecordId(Vtiger_Request $request)
    {
        if (empty($this->recordId)) {
            $sourceIds = $this->getSourceRecords($request);

            if (count($sourceIds) == 1) {
                $recordId = $sourceIds[0];
            } else {
                $recordId = $request->get('record');
            }

            $this->recordId = $recordId;
        }

        return $this->recordId;
    }

    public function isEmailListView(Vtiger_Request $request)
    {
        if (null === $this->emailListView) {
            $templates = $this->getEmailTemplateIds($request);

            if (!empty($templates)) {
                $this->emailListView = ITS4YouEmails_Utils_Helper::isTemplateForListView($templates);
            } else {
                $this->emailListView = false;
            }
        }

        return $this->emailListView;
    }

    /**
     * @throws Exception
     */
    public function retrieveEmailContent(Vtiger_Request $request)
    {
        $emailTemplateIds = $this->getEmailTemplateIds($request);
        $emailTemplateLanguage = $this->getEmailTemplateLanguage($request);
        $sourceIds = $this->getSourceRecords($request);
        $recordId = $this->getRecordId($request);
        $subject = $body = '';
        $sourceModule = $request->get('sourceModule');

        if (!empty($emailTemplateIds)) {
            if ($this->isEmailListView($request)) {

                $ListViewBlocks = array();
                $ListViewBlock = array();
                $ListViewBlockContent = array();

                foreach ($sourceIds as $sourceId) {
                    $this->EMAILContentModel = EMAILMaker_EMAILContent_Model::getInstanceById($emailTemplateIds, $emailTemplateLanguage, $sourceModule, $sourceId);
                    $this->EMAILContentModel->getContent(false);

                    $subject = $this->EMAILContentModel->getSubject();
                    $body = $this->EMAILContentModel->getBody();

                    if (false !== strpos($body, '#LISTVIEWBLOCK_START#') && false !== strpos($body, '#LISTVIEWBLOCK_END#')) {
                        preg_match_all('|#LISTVIEWBLOCK_START#(.*)#LISTVIEWBLOCK_END#|sU', $body, $ListViewBlocks, PREG_PATTERN_ORDER);
                    }

                    if (count($ListViewBlocks) > 0) {
                        $num_listview_blocks = count($ListViewBlocks[0]);

                        for ($idx = 0; $idx < $num_listview_blocks; $idx++) {
                            $ListViewBlock[$idx] = $ListViewBlocks[0][$idx];
                            $ListViewBlockContent[$idx][$sourceId][] = $ListViewBlocks[1][$idx];
                        }
                    }
                }

                foreach ($ListViewBlock as $id => $text) {
                    $replace = '';
                    $CRIdx = 1;

                    foreach ($sourceIds as $sourceId) {
                        $replace .= implode('', $ListViewBlockContent[$id][$sourceId]);
                        $replace = str_ireplace('$CRIDX$', $CRIdx++, $replace);
                    }

                    $body = str_replace($text, $replace, $body);
                }
            } else {
                $templateModel = EMAILMaker_Record_Model::getInstanceById($emailTemplateIds);

                if ($templateModel) {
                    $templateModule = $templateModel->get('module');

                    if ($request->has('cid') && !$request->isEmpty('cid') && 'Campaigns' === $templateModule) {
                        $recordId = $request->get('cid');
                        $sourceModule = $templateModule;
                    }

                    $this->EMAILContentModel = EMAILMaker_EMAILContent_Model::getInstanceById($emailTemplateIds, $emailTemplateLanguage, $sourceModule, $recordId);

                    if (!empty($recordId)) {
                        $this->EMAILContentModel->getContent(false);
                    }

                    $subject = $this->EMAILContentModel->getSubject();
                    $body = $this->EMAILContentModel->getBody();
                }
            }
        }

        $viewer = $this->getViewer($request);
        $viewer->assign('SUBJECT', $subject);
        $viewer->assign('DESCRIPTION', $body);
    }

    public function getEmailTemplateLanguage(Vtiger_Request $request)
    {
        return $request->get('email_template_language');
    }

    public function retrieveAttachments(Vtiger_Request $request)
    {
        $attachments = array();
        $documentIds = array();

        if ($this->EMAILContentModel) {
            $documentIds = $this->EMAILContentModel->getAttachments();
        }

        if (count($documentIds) > 0) {
            foreach ($documentIds as $documentId) {
                $moduleName = getSalesEntityType($documentId);
                $documentRecordModel = Vtiger_Record_Model::getInstanceById($documentId, "Documents");
                if ($documentRecordModel->get('filelocationtype') == 'I') {
                    $fileDetails = $documentRecordModel->getFileDetails();
                    if ($fileDetails) {
                        $fileDetails['fileid'] = $fileDetails['attachmentsid'];
                        $fileDetails['docid'] = $fileDetails['crmid'];
                        $fileDetails['attachment'] = $fileDetails['name'];
                        $fileDetails['nondeletable'] = true;
                        $fileDetails['size'] = filesize($fileDetails['path'] . $fileDetails['attachmentsid'] . "_" . $fileDetails['name']);
                        $attachments[] = $fileDetails;
                    }
                }
            }
        }

        $viewer = $this->getViewer($request);
        $viewer->assign('ATTACHMENTS', $attachments);
        $viewer->assign('DOCUMENT_IDS', $documentIds);
    }

    /**
     * @throws Exception
     */
    public function retrieveFromEmails(Vtiger_Request $request)
    {
        $emailTemplateId = $this->getEmailTemplateIds($request);
        $savedDefaultFrom = ITS4YouEmails_Utils_Helper::getSavedFromField($emailTemplateId);
        $selectedDefaultFrom = '';
        $fromEmails = array();
        $userDefaultFrom = ITS4YouEmails_Utils_Helper::getUserFromEmails($fromEmails, $savedDefaultFrom);
        $organizationDefaultFrom = ITS4YouEmails_Utils_Helper::getOrganizationFromEmails($fromEmails, $savedDefaultFrom);

        if (!empty($userDefaultFrom)) {
            $selectedDefaultFrom = $userDefaultFrom;
        }

        if (!empty($organizationDefaultFrom)) {
            $selectedDefaultFrom = $organizationDefaultFrom;
        }

        $viewer = $this->getViewer($request);
        $viewer->assign('SELECTED_DEFAULT_FROM', $selectedDefaultFrom);
        $viewer->assign('FROM_EMAILS', $fromEmails);
    }
}
