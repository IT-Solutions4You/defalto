<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class ITS4YouEmails_ComposeEmail_View extends Vtiger_Footer_View
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
    /**
     * @var array
     */
    public $sourceRecordIds = [];
    /**
     * @var
     */
    public $recordId;

    /**
     * @param Vtiger_Request $request
     * @return array
     */
    public function requiresPermission(Vtiger_Request $request)
    {
        $permissions = parent::requiresPermission($request);

        $permissions[] = ['module_parameter' => 'module', 'action' => 'DetailView', 'record_parameter' => 'record'];
        $permissions[] = ['module_parameter' => 'custom_module', 'action' => 'DetailView'];
        $request->set('custom_module', 'ITS4YouEmails');

        return $permissions;
    }

    /**
     * @throws Exception
     */
    public function composeMailData(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();
        $qualifiedModuleName = $request->getModule(false);
        $fieldModule = $request->get('fieldModule');
        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
        $userRecordModel = Users_Record_Model::getCurrentUserModel();
        $cvId = $request->get('viewname');
        $selectedIds = $request->get('selected_ids', []);
        $excludedIds = $request->get('excluded_ids', []);
        $selectedFields = $request->get('selectedFields');
        $relatedLoad = $request->get('relatedLoad');
        $documentIds = $request->get('documentIds');
        $sourceModule = $request->get('sourceModule');

        $viewer = $this->getViewer($request);
        $viewer->assign('MODULE', $moduleName);
        $viewer->assign('FIELD_MODULE', $fieldModule);
        $viewer->assign('VIEWNAME', $cvId);
        $viewer->assign('SELECTED_IDS', $selectedIds);
        $viewer->assign('EXCLUDED_IDS', $excludedIds);
        $viewer->assign('USER_MODEL', $userRecordModel);
        $viewer->assign('MAX_UPLOAD_SIZE', Vtiger_Util_Helper::getMaxUploadSizeInBytes());
        $viewer->assign('RELATED_MODULES', $moduleModel->getEmailRelatedModules());
        $viewer->assign('SOURCE_MODULE', $sourceModule);

        if ($documentIds) {
            $attachements = [];
            foreach ($documentIds as $documentId) {
                $documentRecordModel = Vtiger_Record_Model::getInstanceById($documentId, $sourceModule);
                if ($documentRecordModel->get('filelocationtype') == 'I') {
                    $fileDetails = $documentRecordModel->getFileDetails();
                    if ($fileDetails) {
                        $fileDetails['fileid'] = $fileDetails['attachmentsid'];
                        $fileDetails['docid'] = $fileDetails['crmid'];
                        $fileDetails['attachment'] = $fileDetails['name'];
                        $fileDetails['size'] = filesize($fileDetails['path'] . $fileDetails['attachmentsid'] . "_" . $fileDetails['name']);
                        $attachements[] = $fileDetails;
                    }
                }
            }
            $viewer->assign('ATTACHMENTS', $attachements);
        }

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

        $to = [];
        $toMailInfo = [];
        $toMailNamesList = [];
        $selectIds = $this->getRecordsListFromRequest($request);

        $ccMailInfo = $request->get('ccemailinfo');

        if (empty($ccMailInfo)) {
            $ccMailInfo = [];
        }

        $bccMailInfo = $request->get('bccemailinfo');

        if (empty($bccMailInfo)) {
            $bccMailInfo = [];
        }

        $sourceRecordId = $request->get('record');

        if ($sourceRecordId) {
            $sourceRecordModel = Vtiger_Record_Model::getInstanceById($sourceRecordId);

            if ($sourceRecordModel->get('email_flag') === 'SAVED') {
                $selectIds = explode('|', $sourceRecordModel->get('parent_id'));
            }
        }

        $fallBack = false;

        if (!empty($selectedFields)) {
            if ($request->get('emailSource') == 'ListView') {
                foreach ($selectIds as $recordId) {
                    $recordModel = Vtiger_Record_Model::getInstanceById($recordId, $sourceModule);

                    if ($recordModel) {

                        if ($recordModel->get('emailoptout')) {
                            continue;
                        }

                        foreach ($selectedFields as $selectedFieldJson) {
                            $selectedFieldInfo = Zend_Json::decode($selectedFieldJson);
                            if (!empty($selectedFieldInfo['basefield'])) {
                                $refField = $selectedFieldInfo['basefield'];
                                $refModule = getTabModuleName($selectedFieldInfo['module_id']);
                                $fieldName = $selectedFieldInfo['field'];
                                $refFieldValue = $recordModel->get($refField);

                                if (!empty($refFieldValue)) {
                                    try {
                                        $refRecordModel = Vtiger_Record_Model::getInstanceById($refFieldValue, $refModule);
                                        $emailValue = $refRecordModel->get($fieldName);
                                        $moduleLabel = $refModule;
                                    } catch (Exception $e) {
                                        continue;
                                    }
                                }
                            } else {
                                $fieldName = $selectedFieldInfo['field'];
                                $emailValue = $recordModel->get($fieldName);
                                $moduleLabel = $sourceModule;
                            }

                            if (!empty($emailValue)) {
                                $to[] = $emailValue;
                                $toMailInfo[$recordId][] = $emailValue;
                                $toMailNamesList[$recordId][] = ['label' => decode_html($recordModel->get('label')) . ' : ' . vtranslate('SINGLE_' . $moduleLabel, $moduleLabel), 'value' => $emailValue];
                            }
                        }
                    }
                }
            } else {
                foreach ($selectedFields as $selectedFieldJson) {
                    $selectedFieldInfo = Zend_Json::decode($selectedFieldJson);

                    if ($selectedFieldInfo) {
                        $to[] = $selectedFieldInfo['field_value'];
                        $toMailInfo[$selectedFieldInfo['record']][] = $selectedFieldInfo['field_value'];
                        $toMailNamesList[$selectedFieldInfo['record']][] = ['label' => decode_html($selectedFieldInfo['record_label']), 'value' => $selectedFieldInfo['field_value']];
                    } else {
                        $fallBack = true;
                    }
                }
            }
        }

        //fallback to old code
        if ($fallBack) {
            foreach ($selectIds as $id) {
                if ($id) {
                    $parentIdComponents = explode('@', $id);

                    if (php7_count($parentIdComponents) > 1) {
                        $id = $parentIdComponents[0];

                        if ($parentIdComponents[1] === '-1') {
                            $recordModel = Users_Record_Model::getInstanceById($id, 'Users');
                        } else {
                            $recordModel = Vtiger_Record_Model::getInstanceById($id);
                        }
                    } elseif ($fieldModule) {
                        $recordModel = Vtiger_Record_Model::getInstanceById($id, $fieldModule);
                    } else {
                        $recordModel = Vtiger_Record_Model::getInstanceById($id);
                    }

                    if ($selectedFields) {
                        foreach ($selectedFields as $field) {
                            $value = $recordModel->get($field);
                            $emailOptOutValue = $recordModel->get('emailoptout');

                            if (!empty($value) && (!$emailOptOutValue)) {
                                $to[] = $value;
                                $toMailInfo[$id][] = $value;
                                $toMailNamesList[$id][] = ['label' => decode_html($recordModel->getName()), 'value' => decode_html($value)];
                            }
                        }
                    }
                }
            }
        }

        $requestTo = $request->get('to');

        if (!$to && is_array($requestTo)) {
            $to = $requestTo;
        }

        $documentsModel = Vtiger_Module_Model::getInstance('Documents');
        $documentsURL = $documentsModel->getInternalDocumentsURL();

        $emailTemplateModuleModel = Vtiger_Module_Model::getInstance('EMAILMaker');
        $emailTemplateListURL = $emailTemplateModuleModel->getPopupUrl();

        $viewer->assign('DOCUMENTS_URL', $documentsURL);
        $viewer->assign('EMAIL_TEMPLATE_URL', $emailTemplateListURL);
        $viewer->assign('TO', $to);
        $viewer->assign('TOMAIL_INFO', $toMailInfo);
        $viewer->assign('TOMAIL_NAMES_LIST', json_encode($toMailNamesList, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP));
        $viewer->assign('CC', $request->get('cc'));
        $viewer->assign('CCMAIL_INFO', $ccMailInfo);
        $viewer->assign('BCC', $request->get('bcc'));
        $viewer->assign('BCCMAIL_INFO', $bccMailInfo);

        $userPrevilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
        $viewer->assign('MODULE_IS_ACTIVE', $userPrevilegesModel->hasModulePermission(Vtiger_Module_Model::getInstance('EMAILMaker')->getId()));

        if ($relatedLoad) {
            $viewer->assign('RELATED_LOAD', true);
        }

        $emailTemplateIds = $this->getEmailTemplateIds($request);
        $emailTemplateLanguage = $request->get('email_template_language');

        $viewer = $this->getViewer($request);
        $viewer->assign('EMAIL_TEMPLATE_LANGUAGE', $emailTemplateLanguage);
        $viewer->assign('EMAIL_TEMPLATE_IDS', $emailTemplateIds);
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
     * @param Vtiger_Request $request
     * @return void
     */
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

    /**
     * @param Vtiger_Request $request
     * @return void
     */
    public function retrieveDocumentsUrl(Vtiger_Request $request)
    {
        $viewer = $this->getViewer($request);
        $viewer->assign('DOCUMENTS_URL', 'view=Popup&module=Documents&src_module=ITS4YouEmails&src_field=composeEmail');
    }

    /**
     * @param Vtiger_Request $request
     * @param $model
     * @return array|mixed|String
     */
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
            $recordField = $this->getEmailFieldFromRecord($recordModel);

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
                        $refRecordField = $this->getEmailFieldFromRecord($refRecordModel);

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

    /**
     * @param object $recordModel
     * @return string
     */
    public function getEmailFieldFromRecord(object $recordModel): string
    {
        $moduleModel = $recordModel->getModule();
        $fields = $moduleModel->getFieldsByType('email');

        foreach ($fields as $field) {
            $fieldName = $field->get('name');

            if (!$recordModel->isEmpty($fieldName)) {
                return (string)$fieldName;
            }
        }

        return '';
    }


    /**
     * @param Vtiger_Request $request
     * @return mixed|String
     */
    public function getEmailTemplateIds(Vtiger_Request $request)
    {
        return $request->get('email_template_ids');
    }

    /**
     * @param Vtiger_Request $request
     * @return mixed|String
     */
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

    /**
     * @param Vtiger_Request $request
     * @return void
     */
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
                            [$fieldListId, $fieldListField, $fieldListModule] = explode('|', $fieldListEmailId);

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

    /**
     * @param Vtiger_Request $request
     * @return array|mixed|String|String[]
     */
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

    /**
     * @param Vtiger_Request $request
     * @return mixed|String
     */
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

    /**
     * @param Vtiger_Request $request
     * @return bool
     */
    public function isEmailListView(Vtiger_Request $request): bool
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
    public function retrieveEmailContent(Vtiger_Request $request): void
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
                    $this->EMAILContentModel = EMAILMaker_EMAILContent_Model::getInstanceById((int)$emailTemplateIds, $emailTemplateLanguage, $sourceModule, (int)$sourceId);
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

                    $this->EMAILContentModel = EMAILMaker_EMAILContent_Model::getInstanceById($emailTemplateIds, $emailTemplateLanguage, $sourceModule, (int)$recordId);

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

    /**
     * @param Vtiger_Request $request
     * @return mixed|String
     */
    public function getEmailTemplateLanguage(Vtiger_Request $request)
    {
        return $request->get('email_template_language');
    }

    /**
     * @param Vtiger_Request $request
     * @return void
     */
    public function retrieveAttachments(Vtiger_Request $request): void
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
                        $fileDetails['nondeletable'] = false;
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
    public function retrieveFromEmails(Vtiger_Request $request): void
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
