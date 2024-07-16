<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class EMAILMaker_Module_Model extends EMAILMaker_EMAILMaker_Model
{
    public static $mobileIcon = 'email-edit';
    private $profilesActions = array(
        "EDIT" => "EditView",
        "DETAIL" => "DetailView",
        "DELETE" => "Delete",
    );
    private $profilesPermissions = array();
    public function getAlphabetSearchField()
    {
        return 'templatename';
    }

    public function saveRecord(Vtiger_Record_Model $recordModel)
    {
        $db = PearDatabase::getInstance();
        $templateid = $recordModel->getId();
        if (empty($templateid)) {
            $templateid = $db->getUniqueID('vtiger_emakertemplates');
            $sql = "INSERT INTO vtiger_emakertemplates(templatename, subject, description, body, deleted, templateid) VALUES (?,?,?,?,?,?)";
        } else {
            $sql = "UPDATE vtiger_emakertemplates SET templatename=?, subject=?, description=?, body=?, deleted=? WHERE templateid = ?";
        }
        $params = array(
            decode_html($recordModel->get('templatename')),
            decode_html($recordModel->get('subject')),
            decode_html($recordModel->get('description')),
            $recordModel->get('body'),
            0,
            $templateid
        );
        $db->pquery($sql, $params);
        return $recordModel->setId($templateid);
    }

    public function deleteRecord(Vtiger_Record_Model $recordModel)
    {
        $recordId = $recordModel->getId();
        $db = PearDatabase::getInstance();
        $db->pquery('DELETE FROM vtiger_emakertemplates WHERE templateid = ? ', array($recordId));
    }

    public function deleteAllRecords()
    {
        $db = PearDatabase::getInstance();
        $db->pquery('DELETE FROM vtiger_emakertemplates', array());
    }

    public function getAllModuleEmailTemplateFields()
    {
        $currentUserModel = Users_Record_Model::getCurrentUserModel();
        $allModuleList = $this->getAllModuleList();
        $allFields = [];
        $allRelFields = [];

        foreach ($allModuleList as $index => $module) {
            if ($module == 'Users') {
                $fieldList = $this->getRelatedModuleFieldList($module, $currentUserModel);
            } else {
                $fieldList = $this->getRelatedFields($module, $currentUserModel);
            }

            foreach ($fieldList as $key => $field) {
                $option = array(vtranslate($field['module'], $field['module']) . ':' . vtranslate($field['fieldlabel'], $field['module']), "$" . strtolower($field['module']) . "-" . $field['columnname'] . "$");
                $allFields[] = $option;
                if (!empty($field['referencelist'])) {
                    foreach ($field['referencelist'] as $key => $relField) {
                        $relOption = array(vtranslate($field['fieldlabel'], $field['module']) . ':' . '(' . vtranslate($relField['module'], $relField['module']) . ')' . vtranslate($relField['fieldlabel'], $relField['module']), "$" . strtolower($field['module']) . "-" . $field['columnname'] . ":" . $relField['columnname'] . "$");
                        $allRelFields[] = $relOption;
                    }
                }
            }

            if (is_array($allFields) && is_array($allRelFields)) {
                $allFields = array_merge($allFields, $allRelFields);
                $allRelFields = [];
            }

            $allOptions[vtranslate($module, $module)] = $allFields;
            $allFields = [];
        }

        $option = array('Current Date', '$custom-currentdate$');
        $allFields[] = $option;
        $option = array('Current Time', '$custom-currenttime$');
        $allFields[] = $option;
        $allOptions['generalFields'] = $allFields;

        return $allOptions;
    }

    public function getAllModuleList()
    {
        $db = PearDatabase::getInstance();

        $query = 'SELECT DISTINCT(name) AS modulename FROM vtiger_tab 
                              LEFT JOIN vtiger_field ON vtiger_field.tabid = vtiger_tab.tabid
                              WHERE vtiger_field.uitype = ?';
        $result = $db->pquery($query, array(13));
        $num_rows = $db->num_rows($result);
        $moduleList = array();
        for ($i = 0; $i < $num_rows; $i++) {
            $moduleList[] = $db->query_result($result, $i, 'modulename');
        }
        return $moduleList;
    }

    public function getRelatedModuleFieldList($relModule, $user)
    {
        $handler = vtws_getModuleHandlerFromName($relModule, $user);
        $relMeta = $handler->getMeta();
        if (!$relMeta->isModuleEntity()) {
            return null;
        }
        $relModuleFields = $relMeta->getModuleFields();
        $relModuleFieldList = array();
        foreach ($relModuleFields as $relind => $relModuleField) {
            if ($relModule == 'Users') {
                if ($relModuleField->getFieldDataType() == 'string' || $relModuleField->getFieldDataType() == 'email' || $relModuleField->getFieldDataType() == 'phone') {
                    $skipFields = array(98, 115, 116, 31, 32);
                    if (!in_array($relModuleField->getUIType(), $skipFields) && $relModuleField->getFieldName() != 'asterisk_extension') {
                        $relModuleFieldList[] = array('module' => $relModule, 'fieldname' => $relModuleField->getFieldName(), 'columnname' => $relModuleField->getColumnName(), 'fieldlabel' => $relModuleField->getFieldLabelKey());
                    }
                }
            } else {
                $relModuleFieldList[] = array('module' => $relModule, 'fieldname' => $relModuleField->getFieldName(), 'columnname' => $relModuleField->getColumnName(), 'fieldlabel' => $relModuleField->getFieldLabelKey());
            }
        }
        return $relModuleFieldList;
    }

    public function getRelatedFields($module, $currentUserModel)
    {
        $handler = vtws_getModuleHandlerFromName($module, $currentUserModel);
        $meta = $handler->getMeta();
        $moduleFields = $meta->getModuleFields();
        $returnData = array();
        foreach ($moduleFields as $key => $field) {
            $referencelist = array();
            $relatedField = $field->getReferenceList();
            if ($field->getFieldName() == 'assigned_user_id') {
                $relModule = 'Users';
                $referencelist = $this->getRelatedModuleFieldList($relModule, $currentUserModel);
            }
            if (!empty($relatedField)) {
                foreach ($relatedField as $ind => $relModule) {
                    $referencelist = $this->getRelatedModuleFieldList($relModule, $currentUserModel);
                }
            }
            $returnData[] = array('module' => $module, 'fieldname' => $field->getFieldName(), 'columnname' => $field->getColumnName(), 'fieldlabel' => $field->getFieldLabelKey(), 'referencelist' => $referencelist);
        }
        return $returnData;
    }

    public function getListViewLinks($linkParams)
    {
        $currentUserModel = Users_Record_Model::getCurrentUserModel();

        $linkTypes = array('LISTVIEWMASSACTION', 'LISTVIEWSETTING');
        $links = Vtiger_Link_Model::getAllByType($this->getId(), $linkTypes, $linkParams);

        if ($this->CheckPermissions("DELETE")) {
            $massActionLink = array(
                'linktype' => 'LISTVIEWMASSACTION',
                'linklabel' => 'LBL_DELETE',
                'linkurl' => 'javascript:Vtiger_List_Js.massDeleteRecords("index.php?module=PDFMaker&action=MassDelete")',
                'linkicon' => ''
            );

            $links['LISTVIEWMASSACTION'][] = Vtiger_Link_Model::getInstanceFromValues($massActionLink);
        }

        $quickLinks = array();
        if ($this->CheckPermissions("EDIT")) {
            $quickLinks [] = array(
                'linktype' => 'LISTVIEW',
                'linklabel' => 'LBL_IMPORT',
                'linkurl' => 'javascript:Vtiger_Import_Js.triggerImportAction("index.php?module=EMAILMaker&view=Import")',
                'linkicon' => ''
            );
        }

        if ($this->CheckPermissions("EDIT")) {
            $quickLinks [] = array(
                'linktype' => 'LISTVIEW',
                'linklabel' => 'LBL_EXPORT',
                'linkurl' => 'javascript:Vtiger_List_Js.triggerExportAction("index.php?module=EMAILMaker&view=Export")',
                'linkicon' => ''
            );
        }

        foreach ($quickLinks as $quickLink) {
            $links['LISTVIEW'][] = Vtiger_Link_Model::getInstanceFromValues($quickLink);
        }

        if ($currentUserModel->isAdminUser()) {

            $settingsLinks = $this->getSettingLinks();
            foreach ($settingsLinks as $settingsLink) {
                $links['LISTVIEWSETTING'][] = Vtiger_Link_Model::getInstanceFromValues($settingsLink);
            }

            $SettingsLinks = $this->GetAvailableSettings();

            foreach ($SettingsLinks as $stype => $sdata) {
                $s_parr = array(
                    'linktype' => 'LISTVIEWSETTING',
                    'linklabel' => $sdata["label"],
                    'linkurl' => $sdata["location"],
                    'linkicon' => ''
                );

                $links['LISTVIEWSETTING'][] = Vtiger_Link_Model::getInstanceFromValues($s_parr);
            }
        }
        return $links;
    }

    /**
     * @param string $actionKey
     * @return bool
     */
    public function CheckPermissions($actionKey)
    {
        $current_user = Users_Record_Model::getCurrentUserModel();
        $profileid = getUserProfile($current_user->id);
        $result = false;

        if (isset($this->profilesActions[$actionKey])) {
            $actionid = getActionid($this->profilesActions[$actionKey]);
            $permissions = $this->GetProfilesPermissions();

            if (isset($permissions[$profileid[0]][$actionid]) && $permissions[$profileid[0]][$actionid] == "0") {
                $result = true;
            }
        }
        return $result;
    }

    public function GetProfilesPermissions()
    {
        if (count($this->profilesPermissions) == 0) {
            $adb = PearDatabase::getInstance();
            $profiles = Settings_Profiles_Record_Model::getAll();
            $res = $adb->pquery("SELECT * FROM vtiger_emakertemplates_profilespermissions", array());
            $permissions = array();
            while ($row = $adb->fetchByAssoc($res)) {
                if (isset($profiles[$row["profileid"]])) {
                    $permissions[$row["profileid"]][$row["operation"]] = $row["permissions"];
                }
            }

            foreach ($profiles as $profileid => $profilename) {
                foreach ($this->profilesActions as $actionName) {
                    $actionId = getActionid($actionName);
                    if (!isset($permissions[$profileid][$actionId])) {
                        $permissions[$profileid][$actionId] = "0";
                    }
                }
            }
            ksort($permissions);
            $this->profilesPermissions = $permissions;
        }
        return $this->profilesPermissions;
    }

    /*
    public function getSideBarLinks($linkParams) {

        $linkTypes = array('SIDEBARLINK', 'SIDEBARWIDGET');
        $links = Vtiger_Link_Model::getAllByType($this->getId(), $linkTypes, $linkParams);

        $quickLinks = array(
            array(
                'linktype' => 'SIDEBARLINK',
                'linklabel' => 'LBL_RECORDS_LIST',
                'linkurl' => $this->getDefaultUrl(),
                'linkicon' => '',
            ),
        );
        foreach($quickLinks as $quickLink) {
            $links['SIDEBARLINK'][] = Vtiger_Link_Model::getInstanceFromValues($quickLink);
        }
        return $links;
    }
    */

    /**
     * Function to get Settings links
     * @return <Array>
     */
    public function getSettingLinks()
    {
        $settingsLinks = array();

        $currentUserModel = Users_Record_Model::getCurrentUserModel();
        if ($currentUserModel->isAdminUser()) {

            $settingsLinks[] = array(
                'linktype' => 'LISTVIEWSETTING',
                'linklabel' => vtranslate('LBL_EXTENSIONS', $this->getName()),
                'linkurl' => 'index.php?module=' . $this->getName() . '&view=Extensions',
                'linkicon' => ''
            );

            $settingsLinks[] = array(
                'linktype' => 'LISTVIEWSETTING',
                'linklabel' => vtranslate('LBL_PROFILES', $this->getName()),
                'linkurl' => 'index.php?module=' . $this->getName() . '&view=ProfilesPrivilegies',
                'linkicon' => ''
            );

            $settingsLinks[] = array(
                'linktype' => 'LISTVIEWSETTING',
                'linklabel' => vtranslate('LBL_CUSTOM_LABELS', $this->getName()),
                'linkurl' => 'index.php?module=' . $this->getName() . '&view=CustomLabels',
                'linkicon' => ''
            );

            $settingsLinks[] = array(
                'linktype' => 'LISTVIEWSETTING',
                'linklabel' => vtranslate('LBL_PRODUCTBLOCKTPL', $this->getName()),
                'linkurl' => 'index.php?module=' . $this->getName() . '&view=ProductBlocks',
                'linkicon' => ''
            );
        }
        return $settingsLinks;
    }

    /**
     * Funxtion to identify if the module supports quick search or not
     */
    public function isQuickSearchEnabled()
    {
        return false;

    }

    public function getPopupUrl()
    {
        return '';
    }

    /*
     * Function to get supported utility actions for a module
     */
    public function getUtilityActionsNames()
    {
        return array();
    }

    /**
     * Function to get Module Header Links (for Vtiger7)
     * @return array
     */
    public function getModuleBasicLinks()
    {

        $moduleName = $this->getName();
        if ($this->CheckPermissions("EDIT")) {
            $basicLinks[] = array(
                'linktype' => 'BASIC',
                'linklabel' => 'LBL_ADD_TEMPLATE',
                'linkurl' => $this->getSelectThemeUrl(),
                'linkicon' => 'fa-plus'
            );
            $basicLinks[] = array(
                'linktype' => 'BASIC',
                'linklabel' => 'LBL_ADD_THEME',
                'linkurl' => $this->getCreateThemeRecordUrl(),
                'linkicon' => 'fa-plus'
            );
        }

        return $basicLinks;
    }

    public function getSelectThemeUrl()
    {
        $url = $this->getCreateRecordUrl();
        return $url . '&mode=selectTheme';
    }

    public function getCreateRecordUrl()
    {
        return 'index.php?module=' . $this->get('name') . '&view=' . $this->getEditViewName();
    }

    public function getCreateThemeRecordUrl()
    {
        $url = $this->getCreateRecordUrl();
        return $url . '&theme=new&mode=EditTheme';
    }

    public function getNameFields()
    {

        $nameFieldObject = Vtiger_Cache::get('EntityField', $this->getName());
        $moduleName = $this->getName();
        if ($nameFieldObject && $nameFieldObject->fieldname) {
            $this->nameFields = explode(',', $nameFieldObject->fieldname);
        } else {
            $fieldNames = 'filename';
            $this->nameFields = array($fieldNames);


            $entiyObj = new stdClass();
            $entiyObj->basetable = "vtiger_emakertemplates";
            $entiyObj->basetableid = "templateid";
            $entiyObj->fieldname = $fieldNames;
            Vtiger_Cache::set('EntityField', $this->getName(), $entiyObj);
        }

        return $this->nameFields;
    }

    public function isStarredEnabled()
    {
        return false;
    }

    public function isFilterColumnEnabled()
    {
        return false;
    }


    public function getRecordIds($skipRecords)
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery('SELECT templateid FROM vtiger_emakertemplates WHERE templateid NOT IN (' . generateQuestionMarks($skipRecords) . ')', $skipRecords);
        $num_rows = $adb->num_rows($result);
        $recordIds = array();
        if ($num_rows > 0) {
            while ($row = $adb->fetchByAssoc($result)) {
                $recordIds[] = $row['templateid'];
            }
        }
        return $recordIds;
    }

    public function getEmailRelatedModules()
    {
        $userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
        $relatedModules = vtws_listtypes(array('email'), Users_Record_Model::getCurrentUserModel());
        $relatedModules = $relatedModules['types'];

        foreach ($relatedModules as $key => $moduleName) {
            if ($moduleName === 'Users') {
                unset($relatedModules[$key]);
            }
        }

        foreach ($relatedModules as $moduleName) {
            $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
            if ($userPrivilegesModel->isAdminUser() || $userPrivilegesModel->hasGlobalReadPermission() || $userPrivilegesModel->hasModulePermission($moduleModel->getId())) {
                $emailRelatedModules[] = $moduleName;
            }
        }

        $emailRelatedModules[] = 'Users';

        return $emailRelatedModules;
    }

    public function searchEmails($searchValue, $moduleName = false)
    {
        global $current_user;
        $emailsResult = array();
        $db = PearDatabase::getInstance();

        $EmailsModuleModel = Vtiger_Module_Model::getInstance('ITS4YouEmails');
        $emailSupportedModulesList = $EmailsModuleModel->getEmailRelatedModules();
        foreach ($emailSupportedModulesList as $module) {
            if ($module != 'Users' && $module != 'ModComments') {
                $activeModules[] = "'" . $module . "'";
                $activeModuleModel = Vtiger_Module_Model::getInstance($module);
                $moduleEmailFields = $activeModuleModel->getFieldsByType('email');
                foreach ($moduleEmailFields as $fieldName => $fieldModel) {
                    if ($fieldModel->isViewable()) {
                        $fieldIds[] = $fieldModel->get('id');
                    }
                }
            }
        }

        if ($moduleName) {
            $activeModules = array("'" . $moduleName . "'");
        }

        $query = "SELECT vtiger_emailslookup.crmid, vtiger_emailslookup.setype, vtiger_emailslookup.value, 
                          vtiger_crmentity.label FROM vtiger_emailslookup INNER JOIN vtiger_crmentity on 
                          vtiger_crmentity.crmid = vtiger_emailslookup.crmid AND vtiger_crmentity.deleted=0 WHERE 
						  vtiger_emailslookup.fieldid in (" . implode(',', $fieldIds) . ") and 
						  vtiger_emailslookup.setype in (" . implode(',', $activeModules) . ") 
                          and (vtiger_emailslookup.value LIKE ? OR vtiger_crmentity.label LIKE ?)";

        $emailOptOutIds = $EmailsModuleModel->getEmailOptOutRecordIds();
        if (!empty($emailOptOutIds)) {
            $query .= " AND vtiger_emailslookup.crmid NOT IN (" . implode(',', $emailOptOutIds) . ")";
        }

        $result = $db->pquery($query, array('%' . $searchValue . '%', '%' . $searchValue . '%'));
        $isAdmin = is_admin($current_user);
        while ($row = $db->fetchByAssoc($result)) {
            if (!$isAdmin) {
                $recordPermission = Users_Privileges_Model::isPermitted($row['setype'], 'DetailView', $row['crmid']);
                if (!$recordPermission) {
                    continue;
                }
            }
            $emailsResult[vtranslate($row['setype'], $row['setype'])][$row['crmid']][] = array(
                'value' => $row['value'],
                'label' => decode_html($row['label']) . ' (' . $row['value'] . ')',
                'name' => decode_html($row['label']),
                'module' => $row['setype']
            );
        }

        // For Users we should only search in users table
        $additionalModule = array('Users');
        if (!$moduleName || in_array($moduleName, $additionalModule)) {
            foreach ($additionalModule as $moduleName) {
                $moduleInstance = CRMEntity::getInstance($moduleName);
                $searchFields = array();
                $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
                $emailFieldModels = $moduleModel->getFieldsByType('email');

                foreach ($emailFieldModels as $fieldName => $fieldModel) {
                    if ($fieldModel->isViewable()) {
                        $searchFields[] = $fieldName;
                    }
                }
                $emailFields = $searchFields;

                $nameFields = $moduleModel->getNameFields();
                foreach ($nameFields as $fieldName) {
                    $fieldModel = Vtiger_Field_Model::getInstance($fieldName, $moduleModel);
                    if ($fieldModel->isViewable()) {
                        $searchFields[] = $fieldName;
                    }
                }

                if ($emailFields) {
                    $userQuery = 'SELECT ' . $moduleInstance->table_index . ', ' . implode(',', $searchFields) . ' FROM vtiger_users WHERE deleted=0';
                    $result = $db->pquery($userQuery, array());
                    $numOfRows = $db->num_rows($result);
                    for ($i = 0; $i < $numOfRows; $i++) {
                        $row = $db->query_result_rowdata($result, $i);
                        foreach ($emailFields as $emailField) {
                            $emailFieldValue = $row[$emailField];
                            if ($emailFieldValue) {
                                $recordLabel = getEntityFieldNameDisplay($moduleName, $nameFields, $row);
                                if (strpos($emailFieldValue, $searchValue) !== false || strpos($recordLabel, $searchValue) !== false) {
                                    $emailsResult[vtranslate($moduleName, $moduleName)][$row[$moduleInstance->table_index]][]
                                        = array(
                                        'value' => $emailFieldValue,
                                        'name' => $recordLabel,
                                        'label' => $recordLabel . ' <b>(' . $emailFieldValue . ')</b>',
                                        'module' => $moduleName
                                    );

                                }
                            }
                        }
                    }
                }
            }
        }
        return $emailsResult;
    }

    public function GetListviewResult($orderby = 'templateid', $dir = 'ASC', $request = null, $all_data = true)
    {
        $adb = PearDatabase::getInstance();
        $R_Atr = array('0');

        $sql = "SELECT vtiger_emakertemplates_displayed.*, vtiger_emakertemplates.* FROM vtiger_emakertemplates 
                LEFT JOIN vtiger_emakertemplates_displayed USING(templateid)";

        $Search = array();
        $Search_Types = array("module", "description", "sharingtype", "owner");

        $sql .= " WHERE vtiger_emakertemplates.deleted = ? ";

        if ($request) {
            if ($request->has('search_params') && !$request->isEmpty('search_params')) {

                $listSearchParams = $request->get('search_params');

                foreach ($listSearchParams as $groupInfo) {
                    if (empty($groupInfo)) {
                        continue;
                    }
                    foreach ($groupInfo as $fieldSearchInfo) {
                        $st = $fieldSearchInfo[0];
                        $operator = $fieldSearchInfo[1];
                        $search_val = $fieldSearchInfo[2];

                        if (in_array($st, $Search_Types)) {
                            if ($st == "description") {
                                $search_val = "%" . $search_val . "%";
                                $Search[] = "vtiger_pdfmaker." . $st . " LIKE ?";
                            } else {
                                $Search[] = "vtiger_pdfmaker." . $st . " = ?";
                            }
                            $R_Atr[] = $search_val;
                        }
                        if ($st == "status") {
                            $search_status = $search_val;
                        }


                    }
                }
            }

            if (count($Search) > 0) {
                $sql .= " AND ";
                $sql .= implode(" AND ", $Search);
            }
        }
        if (!empty($orderby)) {
            $sql .= " ORDER BY ";
            if ($orderby == "owner" || $orderby == "sharingtype") {
                $sql .= "vtiger_pdfmaker_settings";
            } else {
                $sql .= "vtiger_pdfmaker";
            }
            $sql .= "." . $orderby . " " . $dir;
        }

        $result = $adb->pquery($sql, $R_Atr);
        return $result;
    }

    public function returnTemplatePermissionsData($selected_module = "", $templateid = "")
    {
        $current_user = Users_Record_Model::getCurrentUserModel();
        $result = true;
        if (!is_admin($current_user)) {
            if ($selected_module != "" && isPermitted($selected_module, '') != "yes") {
                $result = false;
            } elseif ($templateid != "" && $this->CheckSharing($templateid) === false) {
                $result = false;
            }
            $detail_result = $result;

            if (!$this->CheckPermissions("EDIT")) {
                $edit_result = false;
            } else {
                $edit_result = $result;
            }

            if (!$this->CheckPermissions("DELETE")) {
                $delete_result = false;
            } else {
                $delete_result = $result;
            }

            if ($detail_result === false || $edit_result === false || $delete_result === false) {
                $profileGlobalPermission = array();
                require('user_privileges/user_privileges_' . $current_user->id . '.php');
                require('user_privileges/sharing_privileges_' . $current_user->id . '.php');

                if ($profileGlobalPermission[1] == 0) {
                    $detail_result = true;
                }
                if ($profileGlobalPermission[2] == 0) {
                    $edit_result = $delete_result = true;
                }
            }
        } else {
            $detail_result = $edit_result = $delete_result = $result;
        }
        return array("detail" => $detail_result, "edit" => $edit_result, "delete" => $delete_result);
    }

    private function getSubRoleUserIds($roleid)
    {
        $subRoleUserIds = array();
        $subordinateUsers = getRoleAndSubordinateUsers($roleid);
        if (!empty($subordinateUsers) && count($subordinateUsers) > 0) {
            $currRoleUserIds = getRoleUserIds($roleid);
            $subRoleUserIds = array_diff($subordinateUsers, $currRoleUserIds);
        }
        return $subRoleUserIds;
    }

    public function getDatabaseTables()
    {
        return array(
            'vtiger_emakertemplates',
            'vtiger_emakertemplates_attch',
            'vtiger_emakertemplates_contents',
            'vtiger_emakertemplates_default_from',
            'vtiger_emakertemplates_delay',
            'vtiger_emakertemplates_displayed',
            'vtiger_emakertemplates_documents',
            'vtiger_emakertemplates_drips',
            'vtiger_emakertemplates_drips_seq',
            'vtiger_emakertemplates_drip_groups',
            'vtiger_emakertemplates_drip_groups_seq',
            'vtiger_emakertemplates_drip_tpls',
            'vtiger_emakertemplates_drip_tpls_seq',
            'vtiger_emakertemplates_emails',
            'vtiger_emakertemplates_ignorepicklistvalues',
            'vtiger_emakertemplates_images',
            'vtiger_emakertemplates_label_keys',
            'vtiger_emakertemplates_label_vals',
            'vtiger_emakertemplates_license',
            'vtiger_emakertemplates_me',
            'vtiger_emakertemplates_picklists',
            'vtiger_emakertemplates_productbloc_tpl',
            'vtiger_emakertemplates_profilespermissions',
            'vtiger_emakertemplates_relblockcol',
            'vtiger_emakertemplates_relblockcriteria',
            'vtiger_emakertemplates_relblockcriteria_g',
            'vtiger_emakertemplates_relblockdatefilter',
            'vtiger_emakertemplates_relblocks',
            'vtiger_emakertemplates_relblocksortcol',
            'vtiger_emakertemplates_relblocks_seq',
            'vtiger_emakertemplates_sent',
            'vtiger_emakertemplates_seq',
            'vtiger_emakertemplates_settings',
            'vtiger_emakertemplates_sharing',
            'vtiger_emakertemplates_sharing_drip',
            'vtiger_emakertemplates_userstatus',
            'vtiger_emakertemplates_version',
        );
    }

    public static function isPDFMakerInstalled()
    {
        return vtlib_isModuleActive('PDFMaker') && method_exists('PDFMaker_Module_Model', 'CheckPermissions') && method_exists('PDFMaker_Module_Model', 'GetAvailableTemplates');
    }

    public function getPicklistFields()
    {
        return [];
    }

    public function getModuleIcon($height = '')
    {
        return sprintf('<i style="font-size: %s" class="fa-solid fa-envelope-open-text" title=""></i>', $height);
    }
}