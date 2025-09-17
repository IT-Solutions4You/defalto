<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class EMAILMaker_Module_Model extends EMAILMaker_EMAILMaker_Model
{
    public static $mobileIcon = 'email-edit';
    private $profilesActions = [
        "EDIT"   => "EditView",
        "DETAIL" => "DetailView",
        "DELETE" => "Delete",
    ];
    private $profilesPermissions = [];

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
        $params = [
            decode_html($recordModel->get('templatename')),
            decode_html($recordModel->get('subject')),
            decode_html($recordModel->get('description')),
            $recordModel->get('body'),
            0,
            $templateid
        ];
        $db->pquery($sql, $params);

        return $recordModel->setId($templateid);
    }

    public function deleteRecord(Vtiger_Record_Model $recordModel)
    {
        $recordId = $recordModel->getId();
        $db = PearDatabase::getInstance();
        $db->pquery('DELETE FROM vtiger_emakertemplates WHERE templateid = ? ', [$recordId]);
    }

    public function deleteAllRecords()
    {
        $db = PearDatabase::getInstance();
        $db->pquery('DELETE FROM vtiger_emakertemplates', []);
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
                $option = [
                    vtranslate($field['module'], $field['module']) . ':' . vtranslate($field['fieldlabel'], $field['module']),
                    "$" . strtolower($field['module']) . "-" . $field['columnname'] . "$"
                ];
                $allFields[] = $option;
                if (!empty($field['referencelist'])) {
                    foreach ($field['referencelist'] as $key => $relField) {
                        $relOption = [
                            vtranslate($field['fieldlabel'], $field['module']) . ':' . '(' . vtranslate($relField['module'], $relField['module']) . ')' . vtranslate(
                                $relField['fieldlabel'],
                                $relField['module']
                            ),
                            "$" . strtolower($field['module']) . "-" . $field['columnname'] . ":" . $relField['columnname'] . "$"
                        ];
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

        $option = ['Current Date', '$custom-currentdate$'];
        $allFields[] = $option;
        $option = ['Current Time', '$custom-currenttime$'];
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
        $result = $db->pquery($query, [13]);
        $num_rows = $db->num_rows($result);
        $moduleList = [];
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
        $relModuleFieldList = [];
        foreach ($relModuleFields as $relind => $relModuleField) {
            if ($relModule == 'Users') {
                if ($relModuleField->getFieldDataType() == 'string' || $relModuleField->getFieldDataType() == 'email' || $relModuleField->getFieldDataType() == 'phone') {
                    $skipFields = [98, 115, 116, 31, 32];
                    if (!in_array($relModuleField->getUIType(), $skipFields) && $relModuleField->getFieldName() != 'asterisk_extension') {
                        $relModuleFieldList[] = [
                            'module'     => $relModule,
                            'fieldname'  => $relModuleField->getFieldName(),
                            'columnname' => $relModuleField->getColumnName(),
                            'fieldlabel' => $relModuleField->getFieldLabelKey()
                        ];
                    }
                }
            } else {
                $relModuleFieldList[] = [
                    'module'     => $relModule,
                    'fieldname'  => $relModuleField->getFieldName(),
                    'columnname' => $relModuleField->getColumnName(),
                    'fieldlabel' => $relModuleField->getFieldLabelKey()
                ];
            }
        }

        return $relModuleFieldList;
    }

    public function getRelatedFields($module, $currentUserModel)
    {
        $handler = vtws_getModuleHandlerFromName($module, $currentUserModel);
        $meta = $handler->getMeta();
        $moduleFields = $meta->getModuleFields();
        $returnData = [];
        foreach ($moduleFields as $key => $field) {
            $referencelist = [];
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
            $returnData[] = [
                'module'        => $module,
                'fieldname'     => $field->getFieldName(),
                'columnname'    => $field->getColumnName(),
                'fieldlabel'    => $field->getFieldLabelKey(),
                'referencelist' => $referencelist
            ];
        }

        return $returnData;
    }

    public function getListViewLinks($linkParams)
    {
        $currentUserModel = Users_Record_Model::getCurrentUserModel();
        $EMAILMaker = EMAILMaker_EMAILMaker_Model::getInstance();
        $linkTypes = ['LISTVIEWMASSACTION', 'LISTVIEWSETTING'];
        $links = Vtiger_Link_Model::getAllByType($this->getId(), $linkTypes, $linkParams);

        if ($EMAILMaker->CheckPermissions("DELETE")) {
            $massActionLink = [
                'linktype'  => 'LISTVIEWMASSACTION',
                'linklabel' => 'LBL_DELETE',
                'linkurl'   => 'javascript:Vtiger_List_Js.massDeleteRecords("index.php?module=PDFMaker&action=MassDelete")',
                'linkicon'  => ''
            ];

            $links['LISTVIEWMASSACTION'][] = Vtiger_Link_Model::getInstanceFromValues($massActionLink);
        }

        $quickLinks = [];
        if ($EMAILMaker->CheckPermissions("EDIT")) {
            $quickLinks [] = [
                'linktype'  => 'LISTVIEW',
                'linklabel' => 'LBL_IMPORT',
                'linkurl'   => 'javascript:Vtiger_Import_Js.triggerImportAction("index.php?module=EMAILMaker&view=Import")',
                'linkicon'  => ''
            ];
        }

        if ($EMAILMaker->CheckPermissions("EDIT")) {
            $quickLinks [] = [
                'linktype'  => 'LISTVIEW',
                'linklabel' => 'LBL_EXPORT',
                'linkurl'   => 'javascript:Vtiger_List_Js.triggerExportAction("index.php?module=EMAILMaker&view=Export")',
                'linkicon'  => ''
            ];
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
                $s_parr = [
                    'linktype'  => 'LISTVIEWSETTING',
                    'linklabel' => $sdata["label"],
                    'linkurl'   => $sdata["location"],
                    'linkicon'  => ''
                ];

                $links['LISTVIEWSETTING'][] = Vtiger_Link_Model::getInstanceFromValues($s_parr);
            }
        }

        return $links;
    }

    /**
     * Function to get Settings links
     * @return <Array>
     */
    public function getSettingLinks()
    {
        $settingsLinks = [];

        $currentUserModel = Users_Record_Model::getCurrentUserModel();
        if ($currentUserModel->isAdminUser()) {
            $settingsLinks[] = [
                'linktype'  => 'LISTVIEWSETTING',
                'linklabel' => vtranslate('LBL_EXTENSIONS', $this->getName()),
                'linkurl'   => 'index.php?module=' . $this->getName() . '&view=Extensions',
                'linkicon'  => ''
            ];

            $settingsLinks[] = [
                'linktype'  => 'LISTVIEWSETTING',
                'linklabel' => vtranslate('LBL_PROFILES', $this->getName()),
                'linkurl'   => 'index.php?module=' . $this->getName() . '&view=ProfilesPrivilegies',
                'linkicon'  => ''
            ];

            $settingsLinks[] = [
                'linktype'  => 'LISTVIEWSETTING',
                'linklabel' => vtranslate('LBL_CUSTOM_LABELS', $this->getName()),
                'linkurl'   => 'index.php?module=' . $this->getName() . '&view=CustomLabels',
                'linkicon'  => ''
            ];

            $settingsLinks[] = [
                'linktype'  => 'LISTVIEWSETTING',
                'linklabel' => vtranslate('LBL_PRODUCTBLOCKTPL', $this->getName()),
                'linkurl'   => 'index.php?module=' . $this->getName() . '&view=ProductBlocks',
                'linkicon'  => ''
            ];
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
        return [];
    }

    /**
     * Function to get Module Header Links (for Vtiger7)
     * @return array
     * @throws Exception
     */
    public function getModuleBasicLinks()
    {
        $moduleName = $this->getName();
        $EMAILMaker = EMAILMaker_EMAILMaker_Model::getInstance();

        if ($EMAILMaker->CheckPermissions("EDIT")) {
            $basicLinks[] = [
                'linktype'    => 'BASIC',
                'linklabel'   => 'LBL_ADD_TEMPLATE',
                'linkurl'     => $this->getSelectThemeUrl(),
                'linkicon'    => 'fa-plus',
                'style_class' => Vtiger_Link_Model::PRIMARY_STYLE_CLASS,
            ];
            $basicLinks[] = [
                'linktype'  => 'BASIC',
                'linklabel' => 'LBL_ADD_THEME',
                'linkurl'   => $this->getCreateThemeRecordUrl(),
                'linkicon'  => 'fa-plus'
            ];
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
            $this->nameFields = [$fieldNames];

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
        $recordIds = [];
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
        $relatedModules = vtws_listtypes(['email'], Users_Record_Model::getCurrentUserModel());
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
        $emailsResult = [];
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
            $activeModules = ["'" . $moduleName . "'"];
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

        $result = $db->pquery($query, ['%' . $searchValue . '%', '%' . $searchValue . '%']);
        $isAdmin = is_admin($current_user);
        while ($row = $db->fetchByAssoc($result)) {
            if (!$isAdmin) {
                $recordPermission = Users_Privileges_Model::isPermitted($row['setype'], 'DetailView', $row['crmid']);
                if (!$recordPermission) {
                    continue;
                }
            }
            $emailsResult[vtranslate($row['setype'], $row['setype'])][$row['crmid']][] = [
                'value'  => $row['value'],
                'label'  => decode_html($row['label']) . ' (' . $row['value'] . ')',
                'name'   => decode_html($row['label']),
                'module' => $row['setype']
            ];
        }

        // For Users we should only search in users table
        $additionalModule = ['Users'];
        if (!$moduleName || in_array($moduleName, $additionalModule)) {
            foreach ($additionalModule as $moduleName) {
                $moduleInstance = CRMEntity::getInstance($moduleName);
                $searchFields = [];
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
                    $result = $db->pquery($userQuery, []);
                    $numOfRows = $db->num_rows($result);
                    for ($i = 0; $i < $numOfRows; $i++) {
                        $row = $db->query_result_rowdata($result, $i);
                        foreach ($emailFields as $emailField) {
                            $emailFieldValue = $row[$emailField];
                            if ($emailFieldValue) {
                                $recordLabel = getEntityFieldNameDisplay($moduleName, $nameFields, $row);
                                if (strpos($emailFieldValue, $searchValue) !== false || strpos($recordLabel, $searchValue) !== false) {
                                    $emailsResult[vtranslate($moduleName, $moduleName)][$row[$moduleInstance->table_index]][]
                                        = [
                                        'value'  => $emailFieldValue,
                                        'name'   => $recordLabel,
                                        'label'  => $recordLabel . ' <b>(' . $emailFieldValue . ')</b>',
                                        'module' => $moduleName
                                    ];
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
        $R_Atr = ['0'];

        $sql = "SELECT vtiger_emakertemplates_displayed.*, vtiger_emakertemplates.* FROM vtiger_emakertemplates 
                LEFT JOIN vtiger_emakertemplates_displayed USING(templateid)";

        $Search = [];
        $Search_Types = ["module", "description", "sharingtype", "owner"];

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

    private function getSubRoleUserIds($roleid)
    {
        $subRoleUserIds = [];
        $subordinateUsers = getRoleAndSubordinateUsers($roleid);
        if (!empty($subordinateUsers) && count($subordinateUsers) > 0) {
            $currRoleUserIds = getRoleUserIds($roleid);
            $subRoleUserIds = array_diff($subordinateUsers, $currRoleUserIds);
        }

        return $subRoleUserIds;
    }

    public function getDatabaseTables()
    {
        return [
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
        ];
    }

    public static function isPDFMakerInstalled()
    {
        return vtlib_isModuleActive('PDFMaker') && method_exists('PDFMaker_PDFMaker_Model', 'CheckPermissions') && method_exists('PDFMaker_PDFMaker_Model', 'GetAvailableTemplates');
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