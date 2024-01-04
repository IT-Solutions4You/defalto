<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class EMAILMaker_SendEmail_View extends Vtiger_Footer_View
{
    public function __construct()
    {
        parent::__construct();

        $this->exposeMethod('composeMailData');
    }

    public function checkPermission(Vtiger_Request $request)
    {
        //$moduleName = $request->getModule();

        //if (!Users_Privileges_Model::isPermitted($moduleName, 'EditView')){
        //        throw new AppException('LBL_PERMISSION_DENIED');
        //}
    }

    public function preProcess(Vtiger_Request $request, $display = true)
    {
        return parent::preProcess($request, $display);
    }

    public function process(Vtiger_Request $request)
    {

        $mode = $request->getMode();
        if (!empty($mode)) {
            echo $this->invokeExposedMethod($mode, $request);
            return;
        }
        $this->composeMailData($request);
    }

    public function composeMailData(Vtiger_Request $request)
    {
        $moduleName = 'Emails';
        $EMAILMaker = new EMAILMaker_EMAILMaker_Model();
        $viewer = $this->getViewer($request);
        $single_record = true;
        $adb = PearDatabase::getInstance();
        $current_user = $cu_model = Users_Record_Model::getCurrentUserModel();
        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
        $userRecordModel = Users_Record_Model::getCurrentUserModel();
        $sourceModule = $request->getModule();
        $cvId = $request->get('viewname');
        $selectedIds = $request->get('selected_ids', array());
        $excludedIds = $request->get('excluded_ids', array());
        $selectedFields = $request->get('selectedFields');
        $relatedLoad = $request->get('relatedLoad');
        $documentIds = $request->get('documentIds');
        $formodule = $request->get('formodule');
        $templateid = $request->get('emailtemplateid');
        $language = $request->get('language');

        $viewer->assign('MODULE', $moduleName);
        $viewer->assign('VIEWNAME', $cvId);
        $viewer->assign('SELECTED_IDS', $selectedIds);
        $viewer->assign('EXCLUDED_IDS', $excludedIds);
        $viewer->assign('USER_MODEL', $userRecordModel);
        $viewer->assign('MAX_UPLOAD_SIZE', vglobal('upload_maxsize'));
        $viewer->assign('RELATED_MODULES', $moduleModel->getEmailRelatedModules());

        $searchKey = $request->get('search_key');
        $searchValue = $request->get('search_value');
        $operator = $request->get('operator');
        if (!empty($operator)) {
            $viewer->assign('OPERATOR', $operator);
            $viewer->assign('ALPHABET_VALUE', $searchValue);
            $viewer->assign('SEARCH_KEY', $searchKey);
        }
        $to = array();
        $toMailInfo = array();

        $ccMailInfo = $request->get('ccemailinfo');
        if (empty($ccMailInfo)) {
            $ccMailInfo = array();
        }
        $bccMailInfo = $request->get('bccemailinfo');
        if (empty($bccMailInfo)) {
            $bccMailInfo = array();
        }
        $documentsModel = Vtiger_Module_Model::getInstance('Documents');
        $documentsURL = $documentsModel->getInternalDocumentsURL();

        $emailTemplateListURL = "module=EMAILMaker&parent=Settings&view=List&formodule=" . $formodule;
        $viewer->assign('DOCUMENTS_URL', $documentsURL);
        $viewer->assign('EMAIL_TEMPLATE_URL', $emailTemplateListURL);
        $viewer->assign('CC', $request->get('cc'));
        $viewer->assign('CCMAIL_INFO', $ccMailInfo);
        $viewer->assign('BCC', $request->get('bcc'));
        $viewer->assign('BCCMAIL_INFO', $bccMailInfo);
        //EmailTemplate module percission check
        $userPrevilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
        $viewer->assign('MODULE_IS_ACTIVE', $userPrevilegesModel->hasModulePermission(Vtiger_Module_Model::getInstance('EMAILMaker')->getId()));

        if ($relatedLoad) {
            $viewer->assign('RELATED_LOAD', true);
        }

        $viewer = $this->getViewer($request);
        $RecordId = $request->get('record');

        if ($RecordId != "") {
            $SourceIds = array($RecordId);
        } else {
            $SourceIds = $this->getRecordsListFromRequest($request);
            if (count($SourceIds) == 1) {
                $RecordId = $SourceIds[0];
            } else {
                $single_record = false;
            }
        }

        if (count($SourceIds) > 1) {
            $more_sources = true;
        } else {
            $more_sources = false;
        }

        $ispdfactive = $request->get('ispdfactive');

        if ($ispdfactive == "1") {
            $pdftemplateid = rtrim($request->get('pdftemplateid'), ';');
            $PDFTemplateIds = explode(";", $pdftemplateid);

            if (count($PDFTemplateIds) > 0) {
                $PDFTemplatesList = $EMAILMaker->GetEMAILPDFListData($PDFTemplateIds);
                $viewer->assign('PDFTEMPLATES', $PDFTemplatesList);

                $pdftemplateids = implode(";", array_keys($PDFTemplatesList));
                $viewer->assign('PDFTEMPLATEIDS', $pdftemplateids);

                $pdflanguage = $request->get('pdflanguage');
                if ($pdflanguage == "") {
                    $pdflanguage = $language;
                }
                $viewer->assign('PDFLANGUAGE', $pdflanguage);
            } else {
                $ispdfactive = 0;
            }
        }
        if ($templateid != "" && $templateid != "0") {
            $is_listview = $EMAILMaker->isTemplateForListView($templateid);
        }
        $aec = $e_seq = 0;
        $to = $toMailInfo = $SourceNames = array();
        $fieldLists = $request->get('field_lists');
        $selected_sourceid = "";

        $SM = array("Accounts", "Contacts", "Leads");
        if ((in_array($formodule, $SM) && !$single_record && $ispdfactive != "1" && $selectedIds != "all") || ($is_listview && $ispdfactive != "1")) {
            $nogruping = true;
            $single_record = true;
            $selected_sourceid = "0";
            $viewer->assign('SOURCE_IDS', array("0"));
        } else {
            $nogruping = false;
            $viewer->assign('SOURCE_IDS', $SourceIds);
        }

        if (count($SourceIds) > 0) {
            foreach ($SourceIds as $sid) {

                if ($nogruping) {
                    $groupid = "0";
                } else {
                    $groupid = $sid;
                }
                if (!isset($toMailInfo[$groupid])) {
                    $toMailInfo[$groupid] = array();
                }
                if (!isset($toMailCCInfo[$groupid])) {
                    $toMailCCInfo[$groupid] = array();
                }
                if (!isset($toMailBCCInfo[$groupid])) {
                    $toMailBCCInfo[$groupid] = array();
                }
                if (!isset($toMailNamesList[$groupid])) {
                    $toMailNamesList[$groupid] = array();
                }
                if (!isset($toMailCCNamesList[$groupid])) {
                    $toMailCCNamesList[$groupid] = array();
                }
                if (!isset($toMailBCCNamesList[$groupid])) {
                    $toMailBCCNamesList[$groupid] = array();
                }

                if ($selected_sourceid == "") {
                    $selected_sourceid = $sid;
                }
                $recordSorceModel = Vtiger_Record_Model::getInstanceById($sid);
                if (!$nogruping) {
                    $SourceNames[$sid] = $recordSorceModel->getName();
                }

                if (!empty($fieldLists)) {
                    foreach ($fieldLists as $id_field) {
                        list($id, $field, $rmodule) = explode("|", $id_field);
                        if ($id == $sid || $id == "0" || $id == "") {
                            $recordModel = $recordSorceModel;
                            $id_field = $sid . "|" . $field . "|" . $rmodule;
                        } else {
                            if (!$single_record || ($is_listview && count($SourceIds) > 1) || $more_sources) {
                                $parent_id = $recordSorceModel->get($id);
                                if (!$parent_id) {
                                    continue;
                                }

                                $id_field = $parent_id . "|" . $field . "|" . $rmodule;
                                $R_RecordId = $parent_id;
                            } else {
                                $R_RecordId = $id;
                            }

                            if ($rmodule == "Users") {
                                $ufocus = new Users();
                                $ufocus->id = $R_RecordId;
                                $ufocus->retrieve_entity_info($R_RecordId, 'Users');
                                $recordModel = Users_Record_Model::getInstanceFromUserObject($ufocus);
                            } else {
                                if (Vtiger_Util_Helper::checkRecordExistance($R_RecordId) == "1") {
                                    continue;
                                }
                                $recordModel = Vtiger_Record_Model::getInstanceById($R_RecordId);
                            }
                        }
                        $recordModuleModel = $recordModel->getModule();
                        $NameFields = $recordModuleModel->getNameFields();

                        $ENames = array();
                        foreach ($NameFields as $nameField) {
                            $ENames[] = $recordModel->get($nameField);
                        }
                        $ename = implode(" ", $ENames);
                        $fid = $recordModel->getID();
                        $value = $recordModel->get($field);
                        $emailOptOutValue = $recordModel->get('emailoptout');
                        if (!empty($value) && (!$emailOptOutValue || !$more_sources)) {

                            $to[$groupid][] = array("id" => $id_field, "text" => $ename . " &lt;" . $recordModel->get($field) . "&gt;");
                            $toMailNamesList[$groupid][$id_field] = array('id' => $id_field, 'recordid' => $fid, 'sid' => $groupid, 'label' => $ename, 'value' => $recordModel->get($field));

                            $toMailInfo[$groupid][$fid][] = $recordModel->get($field);
                            if ($selected_sourceid == $id) {
                                $aec++;
                            }
                            $e_seq++;
                        }
                    }
                }
            }
        }

        $viewer->assign('SELECTED_SOURCEID', $selected_sourceid);
        $viewer->assign('SOURCE_NAMES', $SourceNames);
        $viewer->assign('ACTUAL_EMAILS_COUNT', $aec);
        $viewer->assign('EMAILS_SEQ', $e_seq);
        $viewer->assign('TO', $to);
        $viewer->assign('TOMAIL_INFO', $toMailInfo);
        $viewer->assign('TOMAIL_CC_INFO', $toMailCCInfo);
        $viewer->assign('TOMAIL_BCC_INFO', $toMailBCCInfo);
        $viewer->assign('TOMAIL_NAMES_LIST', $toMailNamesList);
        $viewer->assign('TOMAIL_CC_NAMES_LIST', $toMailCCNamesList);
        $viewer->assign('TOMAIL_BCC_NAMES_LIST', $toMailBCCNamesList);
        $viewer->assign('FOR_MODULE', $formodule);

        $EMAILContentModel = false;

        if ($is_listview) {
            $ListViewBlocks = array();
            foreach ($SourceIds as $sid) {
                $EMAILContentModel = EMAILMaker_EMAILContent_Model::getInstanceById($templateid, $language, $formodule, $sid);
                $EMAILContentModel->getContent(false);

                $subject = $EMAILContentModel->getSubject();
                $body = $EMAILContentModel->getBody();

                if (strpos($body, "#LISTVIEWBLOCK_START#") !== false && strpos($body, "#LISTVIEWBLOCK_END#") !== false) {
                    preg_match_all("|#LISTVIEWBLOCK_START#(.*)#LISTVIEWBLOCK_END#|sU", $body, $ListViewBlocks, PREG_PATTERN_ORDER);
                }

                if (count($ListViewBlocks) > 0) {
                    $num_listview_blocks = count($ListViewBlocks[0]);
                    for ($idx = 0; $idx < $num_listview_blocks; $idx++) {
                        $ListViewBlock[$idx] = $ListViewBlocks[0][$idx];
                        $ListViewBlockContent[$idx][$sid][] = $ListViewBlocks[1][$idx];
                    }
                }
            }
            foreach ($ListViewBlock as $id => $text) {
                $replace = "";
                $cridx = 1;
                foreach ($SourceIds as $sid) {
                    $replace .= implode("", $ListViewBlockContent[$id][$sid]);
                    $replace = str_ireplace('$CRIDX$', $cridx++, $replace);
                }
                $body = str_replace($text, $replace, $body);
            }
        } else {
            $subject = $body = "";
            if ($templateid != "" && $templateid != "0") {
                $TemplateModel = EMAILMaker_Record_Model::getInstanceById($templateid);
                if ($TemplateModel) {
                    $template_module = $TemplateModel->get("module");
                    if ($request->has('cid') && !$request->isEmpty('cid') && $template_module == 'Campaigns') {
                        $RecordId = $request->get('cid');
                        $formodule = $template_module;
                    }
                    $EMAILContentModel = EMAILMaker_EMAILContent_Model::getInstanceById($templateid, $language, $formodule, $RecordId);
                    if ($RecordId != "") {
                        $EMAILContentModel->getContent(false);
                    }

                    $subject = $EMAILContentModel->getSubject();
                    $body = $EMAILContentModel->getBody();
                }
            }
        }
        $viewer->assign('SUBJECT', $subject);
        $viewer->assign('DESCRIPTION', $body);

        if ($single_record) {
            $viewer->assign('SINGLE_RECORD', 'yes');
        }

        if ($EMAILContentModel) {
            $TemplateAttachments = $EMAILContentModel->getAttachments();

            if (count($TemplateAttachments) > 0) {
                if ($documentIds) {
                    $documentIds = array_merge($documentIds, $TemplateAttachments);
                } else {
                    $documentIds = $TemplateAttachments;
                }
            }
        }
        if ($documentIds) {
            $attachements = array();
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
                        $attachements[] = $fileDetails;
                    }
                }
            }
            $viewer->assign('ATTACHMENTS', $attachements);
        }
        $selected_default_from = $saved_default_from = "";
        $result_lfn = $adb->pquery("SELECT fieldname FROM vtiger_emakertemplates_default_from WHERE templateid = ? AND userid = ?", array($templateid, $current_user->getId()));
        $num_rows_lfn = $adb->num_rows($result_lfn);

        if ($num_rows_lfn > 0) {
            $saved_default_from = $adb->query_result($result_lfn, 0, "fieldname");
        }

        $full_name = trim($current_user->get("first_name") . " " . $current_user->get("last_name"));
        $result_fm = $adb->pquery("SELECT fieldname, fieldlabel FROM vtiger_field WHERE tabid = ? AND uitype IN ( ? , ? ) ORDER BY fieldid ASC ", array('29', '104', '13'));

        $current_user_id = $current_user->getId();
        $Current_User_Data = Users_Record_Model::getInstanceById($current_user_id, "Users");

        while ($row_fm = $adb->fetchByAssoc($result_fm)) {
            $cue = $Current_User_Data->get($row_fm['fieldname']);
            if ($cue != "") {
                $from_key = $row_fm['fieldname'] . "::" . $current_user_id;
                $From_Emails[$from_key] = $full_name . " &lt;" . $cue . "&gt;";

                if ($saved_default_from == "1_" . $row_fm['fieldname']) {
                    $selected_default_from = $from_key;
                }
            }
        }

        $result_a = $adb->pquery("select * from vtiger_systems where from_email_field != ? AND server_type = ?", array('', 'email'));
        $from_email_field = $adb->query_result($result_a, 0, "from_email_field");

        if ($from_email_field != "") {
            $result2 = $adb->pquery("select * from vtiger_organizationdetails where organizationname != ''", array());

            while ($row2 = $adb->fetchByAssoc($result2)) {
                $from_key = "a::" . $row2['organizationname'];
                $From_Emails[$from_key] = $row2['organizationname'] . " &lt" . $from_email_field . "&gt;";

                if ($saved_default_from == "0_organization_email") {
                    $selected_default_from = $from_key;
                }
            }
        }

        $viewer->assign("SELECTED_DEFAULT_FROM", $selected_default_from);
        $viewer->assign("FROM_EMAILS", $From_Emails);
        $viewer->assign("EMAIL_LANGUAGE", $language);
        echo $viewer->view('ComposeEmailForm.tpl', 'EMAILMaker', true);
    }

    public function getRecordsListFromRequest(Vtiger_Request $request)
    {
        $cvId = $request->get('cvid');
        $selectedIds = $request->get('selected_ids');
        $excludedIds = $request->get('excluded_ids');

        if (!empty($selectedIds) && $selectedIds != 'all') {
            if (!empty($selectedIds) && count($selectedIds) > 0) {
                return $selectedIds;
            }
        }

        if (($request->has('cid') && !$request->isEmpty('cid')) && $selectedIds == 'all') {
            $sourceRecordModel = Vtiger_Record_Model::getInstanceById($request->get('cid'), "Campaigns");
            return $sourceRecordModel->getSelectedIdsList($request->get('formodule'), $excludedIds);
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

            $customViewModel->set('search_params', $request->get('search_params'));
            return $customViewModel->getRecordIds($excludedIds, '');
        }
    }

    public function postProcess(Vtiger_Request $request)
    {
        return;
    }
}