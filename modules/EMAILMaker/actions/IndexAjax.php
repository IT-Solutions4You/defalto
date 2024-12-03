<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class EMAILMaker_IndexAjax_Action extends Vtiger_Action_Controller
{

    public function __construct()
    {
        parent::__construct();

        $Methods = array(
            'checkDuplicateKey',
            'SaveCustomLabel',
            'SaveCustomLabelValues',
            'deleteCustomLabel',
            'DeleteCustomLabels',
            'SaveProductBlock',
            'deleteProductBlocks',
            'DeleteProductBlock',
            'DeleteTheme',
            'DeleteTemplate',
            'updateLinkForModule',
            'controlEmails',
            'sendEmail',
            'getDocuments',
            'getFiltersForModule',
            'getEmailTemplatesForModule',
            'getEmailTemplatePreview',
            'DeleteME',
            'installExtension',
            'stopSendingEmails',
            'SaveEMAILImages',
            'SaveProfilesPrivilegies',
            'ChangeActiveOrDefault',
            'SearchEmails',
            'getModuleFields',
            'GetRelatedBlockColumns',
            'SaveDisplayConditions',
            'getSendingMsg',
            'getUserSignature'
        );

        foreach ($Methods as $method) {
            $this->exposeMethod($method);
        }
    }

    public function checkPermission(Vtiger_Request $request)
    {
        return true;
    }

    public function process(Vtiger_Request $request)
    {
        $mode = $request->get('mode');
        if (!empty($mode)) {
            $this->invokeExposedMethod($mode, $request);
            return;
        }
        $type = $request->get('type');
    }

    public function checkDuplicateKey(Vtiger_Request $request)
    {
        $adb = PearDatabase::getInstance();
        $lblkey = $request->get('lblkey');
        $result = $adb->pquery("SELECT label_id FROM vtiger_emakertemplates_label_keys WHERE label_key=?", array("C_" . $lblkey));
        $num_rows = $adb->num_rows($result);

        if ($num_rows > 0) {
            $result = array('success' => true, 'message' => vtranslate('LBL_LABEL_KEY_EXIST', 'EMAILMaker'));
        } else {
            $result = array('success' => false);
        }

        $response = new Vtiger_Response();
        $response->setResult($result);
        $response->emit();
    }

    /**
     * @throws Exception
     */
    public function SaveCustomLabel(Vtiger_Request $request)
    {
        $labelId = $request->get('labelid');
        $languageId = $request->get('langid');
        $labelValue = $request->get('LblVal');

        if (empty($labelId)) {
            $labelKey = 'C_' . $request->get('LblKey');
            $customLabel = EMAILMaker_CustomLabels_Model::getInstance($labelKey);
            $customLabel->saveLabelKey();
        } else {
            $customLabel = EMAILMaker_CustomLabels_Model::getInstanceById($labelId);
        }

        $customLabel->saveLabelValue($languageId, $labelValue);

        $response = new Vtiger_Response();
        $response->setResult(array(
            'labelid' => $customLabel->getLabelId(),
            'langid' => $languageId,
            'lblval' => $labelValue,
            'lblkey' => $customLabel->getLabelKey(),
        ));
        $response->emit();
    }

    /**
     * @throws Exception
     */
    public function SaveCustomLabelValues(Vtiger_Request $request)
    {
        $labelKey = $request->get('lblkey');
        $customLabels = EMAILMaker_CustomLabels_Model::getInstance($labelKey);
        $customLabels->saveLabelValues($request);

        $response = new Vtiger_Response();
        $response->setResult(array(
            'success' => true
        ));
        $response->emit();
    }

    public function deleteCustomLabel(Vtiger_Request $request)
    {
        $customLabels = EMAILMaker_CustomLabels_Model::getCleanInstance();
        $customLabels->setLabelId($request->get('labelid'));
        $customLabels->deleteLabelKeys();
        $customLabels->deleteLabelValues();

        $response = new Vtiger_Response();
        $response->setResult(array('success' => true));
        $response->emit();
    }

    public function DeleteCustomLabels(Vtiger_Request $request)
    {
        $params = array();

        foreach ($_REQUEST as $key => $val) {
            if ('chx_' === substr($key, 0, 4) && 'on' === $val) {
                list($dump, $id) = explode('_', $key, 2);

                if (is_numeric($id)) {
                    $params[] = $id;
                }
            }
        }

        if (count($params) > 0) {
            $adb = PearDatabase::getInstance();
            $sql1 = 'DELETE FROM vtiger_emakertemplates_label_vals WHERE label_id IN (' . generateQuestionMarks($params) . ')';
            $sql2 = 'DELETE FROM vtiger_emakertemplates_label_keys WHERE label_id IN (' . generateQuestionMarks($params) . ')';
            $adb->pquery($sql1, $params);
            $adb->pquery($sql2, $params);
        }

        header("Location:index.php?module=EMAILMaker&view=CustomLabels");
    }

    public function SaveProductBlock(Vtiger_Request $request)
    {
        EMAILMaker_Debugger_Model::GetInstance()->Init();
        $adb = PearDatabase::getInstance();
        $tplid = $request->get('tplid');
        $template_name = $request->get('template_name');
        $body = $request->get('body');

        if (isset($tplid) && $tplid != "") {
            $adb->pquery("UPDATE vtiger_emakertemplates_productbloc_tpl SET name=?, body=? WHERE id=?", array($template_name, $body, $tplid));
        } else {
            $adb->pquery("INSERT INTO vtiger_emakertemplates_productbloc_tpl(name, body) VALUES(?,?)", array($template_name, $body));
        }
        header("Location:index.php?module=EMAILMaker&view=ProductBlocks");
    }

    public function DeleteProductBlock(Vtiger_Request $request)
    {
        EMAILMaker_Debugger_Model::GetInstance()->Init();
        $adb = PearDatabase::getInstance();

        $tplid = $request->get('tplid');
        $adb->pquery("DELETE FROM vtiger_emakertemplates_productbloc_tpl WHERE id = ?", array($tplid));
        header("Location:index.php?module=EMAILMaker&view=ProductBlocks");
    }

    public function deleteProductBlocks(Vtiger_Request $request)
    {
        EMAILMaker_Debugger_Model::GetInstance()->Init();
        $adb = PearDatabase::getInstance();
        $sql = "DELETE FROM vtiger_emakertemplates_productbloc_tpl WHERE id IN (";
        $params = array();
        foreach ($_REQUEST as $key => $val) {
            if (substr($key, 0, 4) == "chx_" && $val == "on") {
                list($dump, $id) = explode("_", $key, 2);
                if (is_numeric($id)) {
                    $sql .= "?,";
                    array_push($params, $id);
                }
            }
        }
        if (count($params) > 0) {
            $sql = rtrim($sql, ",") . ")";
            $adb->pquery($sql, $params);
        }
        header("Location:index.php?module=EMAILMaker&view=ProductBlocks");
    }

    public function DeleteTheme(Vtiger_Request $request)
    {
        EMAILMaker_Debugger_Model::GetInstance()->Init();
        $adb = PearDatabase::getInstance();
        $themeid = $request->get('themeid');
        $return_module = $request->get('return_module');
        $return_view = $request->get('return_view');
        $adb->pquery("UPDATE vtiger_emakertemplates SET deleted=1 WHERE templateid=?", array($themeid));
        header("Location:index.php?module=EMAILMaker&view=Edit&mode=selectTheme&return_module=" . $return_module . "&return_view=" . $return_view);
    }

    public function DeleteTemplate(Vtiger_Request $request)
    {
        EMAILMaker_Debugger_Model::GetInstance()->Init();

        $EMAILMakerModel = Vtiger_Module_Model::getInstance('EMAILMaker');

        if ($EMAILMakerModel->CheckPermissions("DELETE") == false) {
            throw new Exception(vtranslate("LBL_PERMISSION", "EMAILMaker"));
        }
        $adb = PearDatabase::getInstance();

        if ($request->has('templateid') && !$request->isEmpty('templateid')) {
            $templateid = $request->get('templateid');
            $checkRes = $adb->pquery("select module from vtiger_emakertemplates where templateid=?", array($templateid));
            $checkRow = $adb->fetchByAssoc($checkRes);

            $EMAILMakerModel->CheckTemplatePermissions($checkRow["module"], $templateid);
            $adb->pquery("delete from vtiger_emakertemplates where templateid=?", array($templateid));
        } else {
            $idlist = $request->get('idlist');
            $id_array = explode(';', $idlist);
            $checkRes = $adb->pquery("select templateid, module from vtiger_emakertemplates where templateid IN (" . generateQuestionMarks($id_array) . ")", $id_array);
            $checkArr = array();
            while ($checkRow = $adb->fetchByAssoc($checkRes)) {
                $checkArr[$checkRow["templateid"]] = $checkRow["module"];
            }
            for ($i = 0; $i < count($id_array) - 1; $i++) {
                $EMAILMakerModel->CheckTemplatePermissions($checkArr[$id_array[$i]], $id_array[$i]);
                $sql = "delete from vtiger_emakertemplates where templateid=?";
                $adb->pquery($sql, array($id_array[$i]));
            }
        }
        $ajaxDelete = $request->get('ajaxDelete');
        $listViewUrl = "index.php?module=EMAILMaker&view=List";
        if ($ajaxDelete) {
            $response = new Vtiger_Response();
            $response->setResult($listViewUrl);
            return $response;
        } else {
            header("Location: $listViewUrl");
        }
    }

    public function updateLinkForModule(Vtiger_Request $request)
    {
        EMAILMaker_Debugger_Model::GetInstance()->Init();
        $for_module = $request->get('forModule');
        $link_type = $request->get('linkType');
        $update_type = $request->get('updateType');
        $link_module = Vtiger_Module::getInstance($for_module);

        if ($update_type == "true") {
            $useFunction = "addLink";
        } else {
            $useFunction = "deleteLink";
        }

        if ($link_type == "1") {
            $link_module->$useFunction('DETAILVIEWSIDEBARWIDGET', 'EMAILMaker', 'module=EMAILMaker&view=GetEMAILActions&record=$RECORD$');
        } elseif ($link_type == "2") {
            $link_module->$useFunction('LISTVIEWMASSACTION', 'Send Emails with EMAILMaker', 'javascript:EMAILMaker_Actions_Js.getListViewPopup(this,\'$MODULE$\');');
        }

        $result = array('success' => true);
        $response = new Vtiger_Response();
        $response->setResult($result);
        $response->emit();
    }

    public function controlEmails(Vtiger_Request $request)
    {
        $records = array();
        $EMAILMaker = new EMAILMaker_EMAILMaker_Model();
        $adb = PearDatabase::getInstance();
        $currentUserModel = Users_Record_Model::getCurrentUserModel();
        $esentid = $request->get('esentid');

        if ($esentid != "") {
            $Record = $EMAILMaker->getEmailsInfo($esentid);
        } else {
            $sql = "SELECT DISTINCT tb1.esentid FROM vtiger_emakertemplates_sent AS tb1 INNER JOIN vtiger_emakertemplates_emails AS tb2 ON tb1.esentid = tb2.esentid WHERE tb2.deleted= '0' AND tb2.status = '0' AND tb1.userid = ? AND (tb1.drip_group = '0' OR tb1.drip_group IS NULL) AND tb1.drip_delay <= 0 AND tb1.type = 1 LIMIT 0,1";
            $result = $adb->pquery($sql, array($currentUserModel->id));
            $num_rows = $adb->num_rows($result);

            if ($num_rows > 0) {
                while ($row = $adb->fetchByAssoc($result)) {
                    $Record = $EMAILMaker->getEmailsInfo($row['esentid']);
                }
            }
        }

        $response = new Vtiger_Response();
        $response->setResult($Record);
        $response->emit();
    }

    public function sendEmail(Vtiger_Request $request)
    {
        $records = array();
        $rootDirectory = vglobal('root_directory');
        $adb = PearDatabase::getInstance();

        if ($request->has('debug') && !$request->isEmpty('debug')) {
            if ($request->get('debug') == 'true') {
                error_reporting(63);
                ini_set("display_errors", 1);
                $adb->setDebug(true);
                $debug = true;
            }
        }


        $EMAILMaker = new EMAILMaker_EMAILMaker_Model();

        $esentid = $request->get('esentid');
        $EMAILMaker->sendEmails($esentid);

        $Emails_Info = $EMAILMaker->getEmailsInfo($esentid);
        $response_result = array("emails_info" => $Emails_Info);
        $response = new Vtiger_Response();
        $response->setResult($response_result);
        $response->emit();
    }

    public function getRelatedDocuments($crmid)
    {
        $adb = PearDatabase::getInstance();
        $documentRes = $adb->pquery("SELECT * FROM vtiger_senotesrel
                                        INNER JOIN vtiger_crmentity ON vtiger_senotesrel.notesid = vtiger_crmentity.crmid AND vtiger_senotesrel.crmid = ?
                                        INNER JOIN vtiger_notes ON vtiger_notes.notesid = vtiger_senotesrel.notesid
                                        INNER JOIN vtiger_seattachmentsrel ON vtiger_seattachmentsrel.crmid = vtiger_notes.notesid
                                        INNER JOIN vtiger_attachments ON vtiger_attachments.attachmentsid = vtiger_seattachmentsrel.attachmentsid
                                        WHERE vtiger_crmentity.deleted = 0", array($crmid));
        $numOfRows = $adb->num_rows($documentRes);
        if ($numOfRows) {
            for ($i = 0; $i < $numOfRows; $i++) {
                $row = $adb->query_result_rowdata($documentRes, $i);

                if (!isset($row['storedname']) || empty($row['storedname'])) {
                    $row['storedname'] = $row['name'];
                }

                $documentsList[$i]['name'] = $row['filename'];
                $filesize = $row['filesize'];
                $documentsList[$i]['size'] = $this->getFormattedFileSize($filesize);
                $documentsList[$i]['docid'] = $row['notesid'];
                $documentsList[$i]['path'] = $row['path'];
                $documentsList[$i]['fileid'] = $row['attachmentsid'];
                $documentsList[$i]['attachment'] = $row['storedname'];
                $documentsList[$i]['type'] = $row['type'];
            }
        }
        return $documentsList;
    }

    public function getFormattedFileSize($filesize)
    {
        if ($filesize < 1024) {
            $filesize = sprintf("%0.2f", round($filesize, 2)) . 'B';
        } else {
            if ($filesize > 1024 && $filesize < 1048576) {
                $filesize = sprintf("%0.2f", round($filesize / 1024, 2)) . 'KB';
            } else {
                if ($filesize > 1048576) {
                    $filesize = sprintf("%0.2f", round($filesize / (1024 * 1024), 2)) . 'MB';
                }
            }
        }
        return $filesize;
    }

    public function getDocuments(Vtiger_Request $request)
    {
        $result = array();
        $EMAILMaker = new EMAILMaker_EMAILMaker_Model();

        $templateid = $request->get('templateid');

        if ($templateid != "" && $templateid != "0") {
            $Documents_Records = $EMAILMaker->getEmailTemplateDocuments($templateid);
            if (count($Documents_Records) > 0) {
                foreach ($Documents_Records as $DD) {
                    $result[$DD["id"]] = array("filename" => $DD["name"], "filesize" => $DD["filesize"]);
                }
            }
        }
        $response = new Vtiger_Response();
        $response->setResult($result);
        $response->emit();
    }

    public function getFiltersForModule(Vtiger_Request $request)
    {
        $source_module = $request->get("source_module");
        $module_filters = EMAILMaker_RecordME_Model::getModuleFilters($source_module);
        $module_columns = EMAILMaker_RecordME_Model::getModuleColumns($source_module);

        echo '<div class="control-group">
                            <div class="control-label">
                                 ' . vtranslate('LBL_FILTER', 'EMAILMaker') . '
                            </div>
                            <div class="controls">
                                ' . $module_filters . '
                            </div>
                        </div>
                        <div class="control-group">
                            <div class="control-label">
                                ' . vtranslate('LBL_COLUMN', 'EMAILMaker') . '
                            </div>
                            <div class="controls">
                                ' . $module_columns . '
                            </div>
                        </div>';
    }

    public function getEmailTemplatesForModule(Vtiger_Request $request)
    {
        $source_module = $request->get("source_module");
        $stemplateid = $request->get("templateid");

        $orderby = "templateid";
        $dir = "asc";
        $c = "<div class='row-fluid'>";

        $EMAILMaker = new EMAILMaker_EMAILMaker_Model();
        $return_data = $EMAILMaker->GetListviewData($orderby, $dir, $source_module, false, $request);

        $select_lbl = vtranslate("LBL_SELECT", "EMAILMaker");
        $preview_lbl = vtranslate("LBL_PREVIEW", "EMAILMaker");

        foreach ($return_data as $edata) {
            $templateid = $edata["templateid"];
            if ($templateid == $stemplateid) {
                $class = "blockHeader";
            } else {
                $class = "tableHeading";
            }

            $c .= '<div class="span" style="margin:10px;" >
                    <div style="float:left;border:1px solid #000000;width:140px;height:185px;" class="themeTableColor">
                        <div style="height:160px;overflow:auto;">
                            <div class="tableHeading cursorPointer" style="border-bottom:1px solid #000000;" border="1">
                                <div id="EmailTemplateHeader' . $templateid . '" data-templateid="' . $templateid . '" style="padding:5px;text-align:center;font-weight:bold;" class="EmailTemplateSelect cursorPointer ' . $class . '">
                                    ' . $edata["name"] . '
                                </div>
                            </div>
                            <div style="padding:2px">' . $edata["description"] . '</div>
                        </div>
                        <center>
                            <div class="actions">
                                <span class="actionImages"><a class="EmailTemplateSelect cursorPointer" data-templateid="' . $templateid . '"><i title="Select" class="icon-plus actionImagesAlignment"></i>' . $select_lbl . '</a></span>
                                <span class="actionImages"><a class="EmailTemplatePreview cursorPointer" data-templateid="' . $templateid . '"><i title="Edit" class="icon-search actionImagesAlignment"></i>' . $preview_lbl . '</a></span>
                            </div>
                        </center>
                        </div>
                    </div>';

            $class = "tableHeading";
        }
        $c .= "</div>";
        echo $c;
    }

    public function getEmailTemplatePreview(Vtiger_Request $request)
    {
        $templateid = $request->get("templateid");
        $EMAILMaker = new EMAILMaker_EMAILMaker_Model();
        $emailtemplateResult = $EMAILMaker->GetDetailViewData($templateid);
        echo decode_html($emailtemplateResult["body"]);
    }

    public function DeleteME(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();
        $recordId = $request->get('record');
        $ajaxDelete = $request->get('ajaxDelete');

        $recordModel = EMAILMaker_RecordME_Model::getInstance($recordId, $moduleName);
        $recordModel->delete();

        if ($ajaxDelete) {
            $result = "index.php?module=EMAILMaker&view=ListME";
        } else {
            $result = array('module' => $moduleName);
        }

        $response = new Vtiger_Response();
        $response->setResult($result);
        $response->emit();
    }

    public function installExtension(Vtiger_Request $request)
    {
        $extname = $request->get("extname");
        $layout = Vtiger_Viewer::getLayoutName();

        if ($extname == "Workflow") {
            $Errors = array();
            include_once('modules/EMAILMaker/EMAILMaker.php');
            $EMAILMaker = new EMAILMaker();

            $EMAILMakerModel = new EMAILMaker_EMAILMaker_Model();
            $Workflows = $EMAILMakerModel->GetWorkflowsList();

            foreach ($Workflows as $name) {
                $folder_dest1 = "modules/com_vtiger_workflow/tasks/";
                $dest1 = $folder_dest1 . $name . ".inc";

                $source1 = "modules/EMAILMaker/workflow/" . $name . ".inc";
                if (!file_exists($dest1)) {
                    if (!copy($source1, $dest1)) {
                        $Errors[] = vtranslate("LBL_PERMISSION_ERROR_PART_1", "EMAILMaker") . ' "' . $source1 . '" ' . vtranslate("LBL_PERMISSION_ERROR_PART_2", "EMAILMaker") . ' "' . $folder_dest1 . '" ' . vtranslate("LBL_PERMISSION_ERROR_PART_3", "EMAILMaker") . '.';
                    }
                }

                $folder_dest2 = "layouts/$layout/modules/Settings/Workflows/Tasks/";
                $dest2 = $folder_dest2 . $name . ".tpl";

                $source2 = "layouts/$layout/modules/EMAILMaker/taskforms/" . $name . ".tpl";
                if (!file_exists($dest2)) {
                    if (!copy($source2, $dest2)) {
                        $Errors[] = vtranslate("LBL_PERMISSION_ERROR_PART_1", "EMAILMaker") . ' "' . $source2 . '" ' . vtranslate("LBL_PERMISSION_ERROR_PART_2", "PDFMaker") . ' "' . $folder_dest2 . '" ' . vtranslate("LBL_PERMISSION_ERROR_PART_3", "PDFMaker") . '.';
                    }
                }
            }
            if (count($Errors) > 0) {
                $error = '<div class="modal-dialog modal-lg modelContainer"><div class="modal-content">';
                $error .= '<div class="modal-header">';
                $error .= '<h3 class="redColor">';
                $error .= vtranslate("LBL_INSTALLATION_FAILED", "EMAILMaker");
                $error .= '</h3>';
                $error .= '<button class="btn-close" data-bs-dismiss="modal"></button>';
                $error .= '</div>';
                $error .= '<div class="modal-body">';
                $error .= implode("<br>", $Errors);
                $error .= "<br><br>" . vtranslate("LBL_CHANGE_PERMISSION", "EMAILMaker");
                $error .= '</div>';
                $error .= '</div></div>';
            } else {
                $EMAILMaker->installWorkflows();
                $control = $EMAILMakerModel->controlWorkflows();

                if (!$control) {
                    $error = vtranslate("LBL_INSTALLATION_FAILED", "EMAILMaker");
                }
            }
            if ($error == "") {
                $result = array('success' => true, 'message' => vtranslate("LBL_WORKFLOWS_ARE_ALREADY_INSTALLED", "EMAILMaker"));
            } else {
                $result = array('success' => false, 'message' => vtranslate($error, 'EMAILMaker'));
            }
        } else {
            $result = array('success' => false);
        }
        $response = new Vtiger_Response();
        $response->setResult($result);
        $response->emit();
    }

    public function stopSendingEmails(Vtiger_Request $request)
    {
        $adb = PearDatabase::getInstance();
        $esentid = $request->get('esentid');
        if ($esentid != "") {
            $adb->pquery("UPDATE vtiger_emakertemplates_emails SET deleted = '1' WHERE esentid = ? AND status = '0'", array($esentid));
            $success = true;
        } else {
            $success = false;
        }
        $response = new Vtiger_Response();
        $response->setResult(array('success' => $success));
        $response->emit();
    }

    public function SaveEMAILImages(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();
        $crmid = $request->get('record');
        $adb = PearDatabase::getInstance();
        $adb->pquery("DELETE FROM vtiger_emakertemplates_images WHERE crmid=?", array($crmid));
        $R_Data = $request->getAll();

        foreach ($R_Data as $key => $value) {
            if (strpos($key, "img_") !== false) {
                list($bin, $productid, $sequence) = explode("_", $key);
                if ($value != "no_image") {
                    $width = $R_Data["width_" . $productid . "_" . $sequence];
                    $height = $R_Data["height_" . $productid . "_" . $sequence];
                    if (!is_numeric($width) || $width > 999) {
                        $width = 0;
                    }
                    if (!is_numeric($height) || $height > 999) {
                        $height = 0;
                    }
                } else {
                    $height = $width = $value = 0;
                }
                $adb->pquery("INSERT INTO vtiger_emakertemplates_images (crmid, productid, sequence, attachmentid, width, height) VALUES (?, ?, ?, ?, ?, ?)", array($crmid, $productid, $sequence, $value, $width, $height));
            }
        }
    }

    public function SaveProfilesPrivilegies(Vtiger_Request $request)
    {
        $EMAILMakerModel = Vtiger_Module_Model::getInstance('EMAILMaker');
        $adb = PearDatabase::getInstance();
        $permissions = $EMAILMakerModel->GetProfilesPermissions();
        foreach ($permissions as $profileid => $subArr) {
            foreach ($subArr as $actionid => $perm) {
                $adb->pquery("DELETE FROM vtiger_emakertemplates_profilespermissions WHERE profileid = ? AND operation = ?", array($profileid, $actionid));
                $priv_chk = $request->get("priv_chk_" . $profileid . "_" . $actionid);
                if ($priv_chk == "on") {
                    $p_val = "0";
                } else {
                    $p_val = "1";
                }

                $adb->pquery("INSERT INTO vtiger_emakertemplates_profilespermissions (profileid, operation, permissions) VALUES(?, ?, ?)", array($profileid, $actionid, $p_val));
            }
        }
        header("Location:index.php?module=EMAILMaker&view=ProfilesPrivilegies");
    }

    public function ChangeActiveOrDefault(Vtiger_Request $request)
    {
        $current_user = Users_Record_Model::getCurrentUserModel();
        $adb = PearDatabase::getInstance();
        $templateid = $request->get("templateid");
        $subject = $request->get("subjectChanged");

        $result = $adb->pquery("SELECT is_listview FROM vtiger_emakertemplates WHERE templateid=?", array($templateid));
        if ($adb->query_result($result, 0, "is_listview") == "1") {
            $set_default_val = "2";
        } else {
            $set_default_val = "3";
        }

        $result = $adb->pquery("SELECT * FROM vtiger_emakertemplates_userstatus WHERE templateid=? AND userid=?", array($templateid, $current_user->id));

        if ($adb->num_rows($result) > 0) {
            if ($subject == "active") {
                $sql = "UPDATE vtiger_emakertemplates_userstatus SET is_active=IF(is_active=0,1,0), is_default=IF(is_active=0,0,is_default) WHERE templateid=? AND userid=?";
            } elseif ($subject == "default") {
                $sql = "UPDATE vtiger_emakertemplates_userstatus SET is_default=IF(is_default > 0,0," . $set_default_val . ") WHERE templateid=? AND userid=?";
            }
        } else {
            if ($subject == "active") {
                $sql = "INSERT INTO vtiger_emakertemplates_userstatus(templateid,userid,is_active,is_default) VALUES(?,?,0,0)";
            } elseif ($subject == "default") {
                $sql = "INSERT INTO vtiger_emakertemplates_userstatus(templateid,userid,is_active,is_default) VALUES(?,?,1," . $set_default_val . ")";
            }
        }
        $adb->pquery($sql, array($templateid, $current_user->id));

        $sql = "SELECT is_default, module
            FROM vtiger_emakertemplates_userstatus
            INNER JOIN vtiger_emakertemplates USING(templateid)
            WHERE templateid=? AND userid=?";
        $result = $adb->pquery($sql, array($templateid, $current_user->id));
        $new_is_default = $adb->query_result($result, 0, "is_default");
        $module = $adb->query_result($result, 0, "module");

        if ($new_is_default == $set_default_val) {
            $sql5 = "UPDATE vtiger_emakertemplates_userstatus 
	       INNER JOIN vtiger_emakertemplates USING(templateid)
	       SET is_default=0
	       WHERE is_default > 0
             AND userid=?
             AND module=?
             AND templateid!=?";
            $adb->pquery($sql5, array($current_user->id, $module, $templateid));
        }

        $response = new Vtiger_Response();
        $response->setResult(array('success' => true));
        $response->emit();
    }

    public function SearchEmails(Vtiger_Request $request)
    {
        $moduleName = $request->get('module');
        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
        $searchValue = $request->get('searchValue');

        $emailsResult = array();
        if ($searchValue) {
            $emailsResult = $moduleModel->searchEmails($request->get('searchValue'));
        }

        $response = new Vtiger_Response();
        $response->setResult($emailsResult);
        $response->emit();
    }

    public function getModuleFields(Vtiger_Request $request)
    {

        $current_user = Users_Record_Model::getCurrentUserModel();
        $this->cu_language = $current_user->get('language');
        $module = $request->get("formodule");
        $forfieldname = $request->get("forfieldname");

        $SelectModuleFields = array();
        $RelatedModules = array();

        if ($module != "") {
            $EMAILMakerFieldsModel = new EMAILMaker_Fields_Model();
            $SelectModuleFields = $EMAILMakerFieldsModel->getSelectModuleFields($module, $forfieldname);
            $RelatedModules = $EMAILMakerFieldsModel->getRelatedModules($module);

        }
        $EMAILMaker = new EMAILMaker_EMAILMaker_Model();
        $subject_fields = $EMAILMaker->getSubjectFields();

        $response = new Vtiger_Response();
        $response->setResult(array('success' => true, 'fields' => $SelectModuleFields, 'related_modules' => $RelatedModules, 'subject_fields' => array(vtranslate('LBL_COMMON_EMAILINFO', 'EMAILMaker') => $subject_fields)));
        $response->emit();
    }

    public function SaveDisplayConditions(Vtiger_Request $request)
    {
        $templateid = $request->get('record');
        $recordModel = EMAILMaker_Record_Model::getInstanceById($templateid);

        $conditions = $request->get('conditions');
        $displayed_value = $request->get('displayedValue');
        $recordModel->updateDisplayConditions($conditions, $displayed_value);

        $detailViewurl = $recordModel->getDetailViewUrl();
        header("Location:" . $detailViewurl);
    }

    public function getSendingMsg(Vtiger_Request $request)
    {

        if ($request->has('esentid') && !$request->isEmpty('esentid')) {

            $esentid = $request->has('esentid');

            $adb = PearDatabase::getInstance();
            $result = $adb->pquery("SELECT total_emails FROM vtiger_emakertemplates_sent WHERE esentid = ?", array($esentid));
            $total_emails = $adb->query_result($result, 0, "total_emails");
            $result2 = $adb->pquery("SELECT count(emailid) as total FROM vtiger_emakertemplates_emails WHERE status = '1' AND esentid = ?", array($esentid));
            $sent_emails = $adb->query_result($result2, 0, "total");

            if ($sent_emails == $total_emails) {
                if ($total_emails > 1) {
                    $title = "LBL_EMAILS_HAS_BEEN_SENT";
                } else {
                    $title = "LBL_EMAIL_HAS_BEEN_SENT";
                }
            } else {
                $title = "LBL_EMAILS_DISTRIBUTION";
            }

            $content = $sent_emails . ' ' . vtranslate("LBL_EMAILS_SENT_FROM", "EMAILMaker") . ' ' . $total_emails;

            $response = new Vtiger_Response();
            $response->setResult(array('id' => $esentid, 'title' => vtranslate($title, "EMAILMaker"), 'content' => $content));
            $response->emit();
        }
    }

    public function getUserSignature(Vtiger_Request $request)
    {
        $def_charset = vglobal('default_charset');
        $currentUserModel = Users_Record_Model::getCurrentUserModel();
        $signature = html_entity_decode($currentUserModel->get('signature'), ENT_QUOTES, $def_charset);

        $response = new Vtiger_Response();
        $response->setResult(array('success' => true, 'signature' => $signature));
        $response->emit();
    }
}