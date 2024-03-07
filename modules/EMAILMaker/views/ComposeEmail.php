<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class EMAILMaker_ComposeEmail_View extends Vtiger_ComposeEmail_View
{
    public function __construct()
    {
        parent::__construct();
        $this->exposeMethod('previewPrint');
        $this->exposeMethod('emailPreview');
        $this->exposeMethod('emailForward');
    }

    public function checkPermission(Vtiger_Request $request)
    {
        //$moduleName = "Emails";

        //if (!Users_Privileges_Model::isPermitted($moduleName, 'EditView')) {
        //        throw new AppException('LBL_PERMISSION_DENIED');
        //}
    }

    public function preProcess(Vtiger_Request $request, $display = true)
    {
        if ($request->getMode() == 'previewPrint') {
            return;
        }
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

        $this->composeEMAILMakerData($request);

        $viewer = $this->getViewer($request);
        $viewer->view('ComposeEmailForm.tpl', 'EMAILMaker');
    }

    public function composeEMAILMakerData($request)
    {
        $EMAILMaker = new EMAILMaker_EMAILMaker_Model();
        $adb = PearDatabase::getInstance();
        $current_user = Users_Record_Model::getCurrentUserModel();

        $sourceModule = $request->get('sourceModule');
        $templateid = $request->get('emailtemplateid');
        $language = $request->get('email_template_language');
        $viewer = $this->getViewer($request);
        $selectedIds = $request->get('selected_ids');

        $viewer->assign('EMAIL_TEMPLATE_LANGUAGE', $language);


        if ($request->has('cid') && !$request->isEmpty('cid')) {
            $viewer->assign('SOURCEMODULE', $request->get('parentModule'));
        } else {
            $viewer->assign('SOURCEMODULE', $sourceModule);
        }


        $RecordId = $request->get('record');

        if ($RecordId != "") {
            $SourceIds = array($RecordId);
        } else {
            $SourceIds = $this->getRecordsListFromRequest($request);

            if (!is_array($SourceIds)) {
                $SourceIds = array($SourceIds);
            }

            if (count($SourceIds) == 1) {
                $RecordId = $SourceIds[0];
            }
        }

        if (count($SourceIds) > 1) {
            $more_sources = true;
            $single_record = false;
        } else {
            $more_sources = false;
            $single_record = true;
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

        if (!$more_sources) {
            $viewer->assign('SOURCERECORD', $RecordId);
        }

        $aec = $e_seq = 0;
        $to = $toMailInfo = $SourceNames = array();

        $allfieldLists["to"] = $request->get('field_lists');
        $allfieldLists["cc"] = $request->get('ccfield_lists');
        $allfieldLists["bcc"] = $request->get('bccfield_lists');
        $selected_sourceid = "";

        $SM = array("Accounts", "Contacts", "Leads");
        if ((in_array($sourceModule, $SM) && !$single_record && $ispdfactive != "1" && $selectedIds != "all") || ($is_listview && $ispdfactive != "1")) {
            $nogruping = true;
            $single_record = true;
            $selected_sourceid = "0";
            $viewer->assign('SOURCE_IDS', array("0"));
            $SourceNames[0] = "";
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

                foreach ($allfieldLists as $listtype => $fieldLists) {
                    if (!isset($toMailInfo[$listtype][$groupid])) {
                        $toMailInfo[$listtype][$groupid] = array();
                    }
                    if (!isset($allMailNamesList[$listtype][$groupid])) {
                        $allMailNamesList[$listtype][$groupid] = array();
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
                                $R_RecordId = $sid;
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
                            $email_val = $recordModel->get($field);
                            $emailOptOutValue = $recordModel->get('emailoptout');

                            if (!empty($email_val) && (!$emailOptOutValue || !$more_sources)) {
                                $email_field = $R_RecordId;
                                $i = $email_field . "|" . $email_val . "|" . $rmodule;

                                $toEmails[$listtype][$groupid][] = array("id" => $R_RecordId, "name" => $ename . " <b>(" . $email_val . ")</b>", "emailid" => $email_val, "module" => $rmodule);
                                $to[$listtype][$groupid][$i] = $ename . " <b>(" . $email_val . ")</b>";
                                $allMailNamesList[$listtype][$groupid][$i][] = array('id' => $id_field, 'recordid' => $fid, 'sid' => $groupid, 'label' => $ename, 'value' => $recordModel->get($field), "module" => $rmodule);
                                $toMailInfo[$listtype][$groupid][$i][] = $recordModel->get($field);
                                if ($selected_sourceid == $id) {
                                    $aec++;
                                }
                                $e_seq++;
                            }
                        }
                    }
                }
            }
        }

        foreach (array('to', 'cc', 'bcc') as $t) {
            if (count($to[$t]) > 0) {
                $viewer->assign(strtoupper($t), $to[$t]);
            }

            $viewer->assign(strtoupper($t) . '_EMAILS', $toEmails[$t]);
            $viewer->assign(strtoupper($t) . 'MAIL_INFO', $toMailInfo[$t]);
            $viewer->assign(strtoupper($t) . 'MAIL_NAMES_LIST', $allMailNamesList[$t]);
        }

        $EMAILContentModel = false;

        if ($is_listview) {
            $ListViewBlocks = array();
            foreach ($SourceIds as $sid) {
                $EMAILContentModel = EMAILMaker_EMAILContent_Model::getInstanceById($templateid, $language, $sourceModule, $sid);
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
                        $sourceModule = $template_module;
                    }
                    $EMAILContentModel = EMAILMaker_EMAILContent_Model::getInstanceById($templateid, $language, $sourceModule, $RecordId);

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

        $attachements = array();
        if ($EMAILContentModel) {
            $documentIds = $EMAILContentModel->getAttachments();
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
                        $attachements[] = $fileDetails;
                    }
                }
            }

        }
        $viewer->assign('ATTACHMENTS', $attachements);

        $From_Emails = array();
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

        $from_email_field = Settings_Vtiger_Systems_Model::getFromEmailField();

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
        $viewer->assign('SOURCE_NAMES', $SourceNames);

        $viewer->assign('SELECTED_SOURCEID', $selected_sourceid);
        $viewer->assign('IS_MERGE_TEMPLATES', $request->get('is_merge_templates'));
    }

    public function previewPrint($request)
    {
        $this->emailPreview($request);
    }

    public function emailPreview($request)
    {
        $recordId = $request->get('record');
        $moduleName = "Emails";

        $this->record = Vtiger_DetailView_Model::getInstance("Emails", $recordId);
        $recordModel = $this->record->getRecord();

        $viewer = $this->getViewer($request);
        $TO = Zend_Json::decode(html_entity_decode($recordModel->get('saved_toid')));
        $CC = Zend_Json::decode(html_entity_decode($recordModel->get('ccmail')));
        $BCC = Zend_Json::decode(html_entity_decode($recordModel->get('bccmail')));

        $EMAILMaker = new EMAILMaker_EMAILMaker_Model();
        $description = $EMAILMaker->getEmailContent($recordId);
        $recordModel->set('description', $description);

        $parentId = $request->get('parentId');
        if (empty($parentId)) {
            $array = array_filter(explode('|', $recordModel->get('parent_id')));

            list($parentRecord, $status) = explode('@', reset($array));
            $parentId = $parentRecord;
        }

        $viewer->assign('FROM', $recordModel->get('from_email'));
        $viewer->assign('TO', $TO);
        $viewer->assign('CC', implode(',', $CC));
        $viewer->assign('BCC', implode(',', $BCC));
        $viewer->assign('RECORD', $recordModel);
        $viewer->assign('MODULE', $moduleName);
        $viewer->assign('RECORD_ID', $recordId);
        $viewer->assign('PARENT_RECORD', $parentId);

        if ($request->get('mode') == 'previewPrint') {
            $viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
            echo $viewer->view('EmailPreviewPrint.tpl', $moduleName, true);
        } else {
            echo $viewer->view('EmailPreview.tpl', $moduleName, true);
        }
    }
}
