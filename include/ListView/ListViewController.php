<?php
/*************************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
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

/**
 * Description of ListViewController
 *
 * @author MAK
 */
class ListViewController
{
    /**
     *
     * @var QueryGenerator
     */
    protected $queryGenerator;
    /**
     *
     * @var PearDatabase
     */
    protected $db;
    protected $nameList;
    protected $typeList;
    protected $ownerNameList;
    protected $user;
    protected $picklistValueMap;
    protected $picklistRoleMap;
    protected $headerSortingEnabled;

    public function __construct($db, $user, $generator)
    {
        $this->queryGenerator = $generator;
        $this->db = $db;
        $this->user = $user;
        $this->nameList = [];
        $this->typeList = [];
        $this->ownerNameList = [];
        $this->picklistValueMap = [];
        $this->picklistRoleMap = [];
        $this->headerSortingEnabled = true;
    }

    public function isHeaderSortingEnabled()
    {
        return $this->headerSortingEnabled;
    }

    public function setHeaderSorting($enabled)
    {
        $this->headerSortingEnabled = $enabled;
    }

    public function setupAccessiblePicklistValueList($name)
    {
        require_once 'modules/PickList/PickListUtils.php';
        $isRoleBased = vtws_isRoleBasedPicklist($name);
        $this->picklistRoleMap[$name] = $isRoleBased;
        if ($this->picklistRoleMap[$name]) {
            $this->picklistValueMap[$name] = getAllPickListValues($name, $this->user->roleid);
        }
    }

    public function fetchNameList($field, $result)
    {
        $referenceFieldInfoList = $this->queryGenerator->getReferenceFieldInfoList();
        $fieldName = $field->getFieldName();
        $rowCount = $this->db->num_rows($result);

        $columnName = $field->getColumnName();
        if (isset($field->referenceFieldName) && $field->referenceFieldName) {
            preg_match('/(\w+) ; \((\w+)\) (\w+)/', $field->referenceFieldName, $matches);
            if (php7_count($matches) != 0) {
                [$full, $parentReferenceFieldName, $referenceModule, $referenceFieldName] = $matches;
            }
            $columnName = $parentReferenceFieldName . $referenceFieldName;
        }

        $idList = [];
        for ($i = 0; $i < $rowCount; $i++) {
            $id = $this->db->query_result($result, $i, $columnName);
            if (!isset($this->nameList[$fieldName][$id]) && $id != null) {
                $idList[$id] = $id;
            }
        }

        $idList = array_keys($idList);
        if (php7_count($idList) == 0) {
            return;
        }
        if (isset($parentReferenceFieldName) && $parentReferenceFieldName) {
            $moduleList = $referenceFieldInfoList[$field->referenceFieldName];
        } else {
            $moduleList = $referenceFieldInfoList[$fieldName];
        }

        if ($moduleList) {
            foreach ($moduleList as $module) {
                $meta = $this->queryGenerator->getMeta($module);
                if ($meta->isModuleEntity()) {
                    if ($module == 'Users') {
                        $nameList = getOwnerNameList($idList);
                    } else {
                        //TODO handle multiple module names overriding each other.
                        $nameList = getEntityName($module, $idList);
                    }
                } else {
                    $nameList = vtws_getActorEntityName($module, $idList);
                }
                $entityTypeList = array_intersect(array_keys($nameList), $idList);
                foreach ($entityTypeList as $id) {
                    $this->typeList[$id] = $module;
                }
                if (empty($this->nameList[$fieldName])) {
                    $this->nameList[$fieldName] = [];
                }
                foreach ($entityTypeList as $id) {
                    $this->typeList[$id] = $module;
                    $this->nameList[$fieldName][$id] = $nameList[$id];
                }
            }
        }
    }

    public function getListViewHeaderFields()
    {
        $meta = $this->queryGenerator->getMeta($this->queryGenerator->getModule());
        $moduleFields = $this->queryGenerator->getModuleFields();
        $fields = $this->queryGenerator->getFields();
        $headerFields = [];
        foreach ($fields as $fieldName) {
            if (is_array($moduleFields) && array_key_exists($fieldName, $moduleFields)) {
                $headerFields[$fieldName] = $moduleFields[$fieldName];
            }
        }

        return $headerFields;
    }

    /**
     * @throws Exception
     */
    function getListViewRecords($focus, $module, $result)
    {
        global $listview_max_textlength, $theme, $default_charset;
        $is_admin = false;
        require('user_privileges/user_privileges_' . $this->user->id . '.php');
        $fields = $this->queryGenerator->getFields();
        $meta = $this->queryGenerator->getMeta($this->queryGenerator->getModule());
        $baseModule = $module;
        $moduleFields = $this->queryGenerator->getModuleFields();
        $accessibleFieldList = is_array($moduleFields) ? array_keys($moduleFields) : [];
        $listViewFields = array_intersect($fields, $accessibleFieldList);

        $referenceFieldList = $this->queryGenerator->getReferenceFieldList();

        if ($referenceFieldList) {
            foreach ($referenceFieldList as $fieldName) {
                if (in_array($fieldName, $listViewFields)) {
                    $field = $moduleFields[$fieldName];
                    $this->fetchNameList($field, $result);
                }
            }
        }

        $db = PearDatabase::getInstance();
        $rowCount = $db->num_rows($result);
        $ownerFieldList = $this->queryGenerator->getOwnerFieldList();

        foreach ($ownerFieldList as $fieldName) {
            if (in_array($fieldName, $listViewFields)) {
                /** @var WebserviceField $field */
                $field = $moduleFields[$fieldName];
                $idList = [];

                //if the assigned to is related to the reference field
                preg_match('/(\w+) ; \((\w+)\) (\w+)/', $fieldName, $matches);
                if (php7_count($matches) > 0) {
                    [$full, $referenceParentField, $module, $fieldName] = $matches;
                    $columnName = strtolower($referenceParentField . $fieldName);
                } else {
                    $columnName = $field->getColumnName();
                }

                for ($i = 0; $i < $rowCount; $i++) {
                    $id = $this->db->query_result($result, $i, $columnName);
                    if (!isset($this->ownerNameList[$fieldName][$id])) {
                        $idList[] = $id;
                    }
                }
                if (php7_count($idList) > 0) {
                    if (isset($this->onwerNameList[$fieldName]) && !is_array($this->ownerNameList[$fieldName])) {
                        $this->ownerNameList[$fieldName] = getOwnerNameList($idList);
                    } else {
                        //array_merge API loses key information so need to merge the arrays
                        // manually.
                        $newOwnerList = getOwnerNameList($idList);
                        foreach ($newOwnerList as $id => $name) {
                            $this->ownerNameList[$fieldName][$id] = $name;
                        }
                    }
                }
            }
        }
        $fileTypeFields = [];
        foreach ($listViewFields as $fieldName) {
            $field = $moduleFields[$fieldName];
            if (!$is_admin && ($field->getFieldDataType() == 'picklist' ||
                    $field->getFieldDataType() == 'multipicklist')) {
                $this->setupAccessiblePicklistValueList($fieldName);
            }
            if ($field->getUIType() == '61') {
                $fileTypeFields[] = $field->getColumnName();
            }
        }

        //performance optimization for uitype 61
        $attachmentsCache = [];
        $attachmentIds = [];
        if (php7_count($fileTypeFields)) {
            foreach ($fileTypeFields as $fileTypeField) {
                for ($i = 0; $i < $rowCount; ++$i) {
                    $attachmentId = $db->query_result($result, $i, $fileTypeField);
                    if ($attachmentId) {
                        $attachmentIds[] = $attachmentId;
                    }
                }
            }
        }
        if (php7_count($attachmentIds)) {
            $getAttachmentsNamesSql = 'SELECT attachmentsid,name FROM vtiger_attachments WHERE attachmentsid IN (' . generateQuestionMarks($attachmentIds) . ')';
            $attachmentNamesRes = $db->pquery($getAttachmentsNamesSql, $attachmentIds);
            $attachmentNamesRowCount = $db->num_rows($attachmentNamesRes);
            for ($i = 0; $i < $attachmentNamesRowCount; $i++) {
                $attachmentsName = $db->query_result($attachmentNamesRes, $i, 'name');
                $attachmentsId = $db->query_result($attachmentNamesRes, $i, 'attachmentsid');
                $attachmentsCache[$attachmentsId] = decode_html($attachmentsName);
            }
        }

        $moduleInstance = Vtiger_Module_Model::getInstance("PBXManager");
        $outgoingCallPermission = false;

        if ($moduleInstance && $moduleInstance->isActive()) {
            $outgoingCallPermission = PBXManager_Server_Model::checkPermissionForOutgoingCall();
            $clickToCallLabel = vtranslate("LBL_CLICK_TO_CALL");
        }

        $data = [];
        for ($i = 0; $i < $rowCount; ++$i) {
            //Getting the recordId
            if ($module != 'Users') {
                $baseTable = $meta->getEntityBaseTable();
                $moduleTableIndexList = $meta->getEntityTableIndexList();
                $baseTableIndex = $moduleTableIndexList[$baseTable];

                $baseRecordId = $recordId = $db->query_result($result, $i, $baseTableIndex);
            } else {
                $baseRecordId = $recordId = $db->query_result($result, $i, "id");
            }

            $row = [];

            foreach ($listViewFields as $fieldName) {
                $recordId = $baseRecordId;
                $rawFieldName = $fieldName;
                /** @var WebserviceField $field */
                $field = $moduleFields[$fieldName];
                $uitype = $field->getUIType();
                $fieldDataType = $field->getFieldDataType();
                // for reference fields read the value differently
                preg_match('/(\w+) ; \((\w+)\) (\w+)/', $fieldName, $matches);
                if (php7_count($matches) > 0) {
                    [$full, $referenceParentField, $module, $fieldName] = $matches;
                    $matches = null;
                    $rawValue = $this->db->query_result($result, $i, strtolower($referenceParentField . $fieldName));
                    //if the field is related to reference module's field, then we might need id of that record for example emails field
                    $recordId = $this->db->query_result($result, $i, strtolower($referenceParentField . $fieldName) . '_id');
                } else {
                    $rawValue = $this->db->query_result($result, $i, $field->getColumnName());
                    //if not reference module field then we need to reset the module
                    $module = $baseModule;
                }

                if (in_array($uitype, [15, 33, 16])) {
                    $value = html_entity_decode($rawValue, ENT_QUOTES, $default_charset);
                } else {
                    $value = $rawValue;
                }

                if ($module == 'Documents' && $fieldName == 'filename') {
                    $downloadtype = $db->query_result($result, $i, 'filelocationtype');
                    $fileName = $db->query_result($result, $i, 'filename');

                    $downloadType = $db->query_result($result, $i, 'filelocationtype');
                    $status = $db->query_result($result, $i, 'filestatus');
                    $fileIdQuery = "select attachmentsid from vtiger_seattachmentsrel where crmid=?";
                    $fileIdRes = $db->pquery($fileIdQuery, [$recordId]);
                    $fileId = $db->query_result($fileIdRes, 0, 'attachmentsid');
                    if ($fileName != '' && $status == 1) {
                        if ($downloadType == 'I' && $fileId) {
                            $value = '<a href="index.php?module=Documents&action=DownloadFile&record=' . $recordId . '&fileid=' . $fileId . '"' .
                                ' title="' . getTranslatedString('LBL_DOWNLOAD_FILE', $module) .
                                '" >' . textlength_check($value) .
                                '</a>';
                        } elseif ($downloadType == 'E') {
                            $value = '<a onclick="event.stopPropagation()"' .
                                ' href="' . $fileName . '" target="_blank"' .
                                ' title="' . getTranslatedString('LBL_DOWNLOAD_FILE', $module) .
                                '" >' . textlength_check($value) .
                                '</a>';
                        } else {
                            $value = ' --';
                        }
                    } else {
                        $value = textlength_check($value);
                    }
                } elseif ($module == 'Documents' && $fieldName == 'filesize') {
                    $downloadType = $db->query_result($result, $i, 'filelocationtype');
                    if ($downloadType == 'I') {
                        $filesize = $value;
                        if ($filesize < 1024) {
                            $value = $filesize . ' B';
                        } elseif ($filesize > 1024 && $filesize < 1048576) {
                            $value = round($filesize / 1024, 2) . ' KB';
                        } elseif ($filesize > 1048576) {
                            $value = round($filesize / (1024 * 1024), 2) . ' MB';
                        }
                    } else {
                        $value = ' --';
                    }
                } elseif ($module == 'Documents' && $fieldName == 'filestatus') {
                    if ($value == 1) {
                        $value = getTranslatedString('yes', $module);
                    } elseif ($value == 0) {
                        $value = getTranslatedString('no', $module);
                    } else {
                        $value = '--';
                    }
                } elseif ($module == 'Documents' && $fieldName == 'filetype') {
                    $downloadType = $db->query_result($result, $i, 'filelocationtype');
                    if ($downloadType == 'E' || $downloadType != 'I') {
                        $value = '--';
                    }
                } elseif ($field->getUIType() == '27') {
                    if ($value == 'I') {
                        $value = getTranslatedString('LBL_INTERNAL', $module);
                    } elseif ($value == 'E') {
                        $value = getTranslatedString('LBL_EXTERNAL', $module);
                    } else {
                        $value = ' --';
                    }
                } elseif ($fieldDataType == 'picklist') {
                    //not check for permissions for non admin users for status and activity type field
                    $value = Vtiger_Language_Handler::getTranslatedString($value, $module);
                    $value = textlength_check($value);
                } elseif ($fieldDataType == 'date') {
                    if ($value != '' && $value != '0000-00-00' && $value != 'NULL') {
                        $date = new DateTimeField($value);
                        $value = $date->getDisplayDate();
                    } elseif ($value == '0000-00-00') {
                        $value = '';
                    }
                } elseif ($fieldDataType == 'time') {
                    if (!empty($value)) {
                        $userModel = Users_Privileges_Model::getCurrentUserModel();
                        if ($userModel->get('hour_format') == '12') {
                            $value = Vtiger_Time_UIType::getTimeValueInAMorPM($value);
                        }
                    }
                } elseif ($fieldDataType == 'email') {
                    global $current_user;
                    $emailModuleInstance = Vtiger_Module_Model::getInstance('ITS4YouEmails');

                    if ($emailModuleInstance && $emailModuleInstance->isActive() && $current_user->internal_mailer == 1) {
                        //check added for email link in user detailview
                        $value = "<a class='emailField' data-rawvalue=\"$rawValue\" onclick=\"Vtiger_Helper_Js.getInternalMailer($recordId," .
                            "'$fieldName','$module');\">" . textlength_check($value) . "</a>";
                    } else {
                        $value = '<a class="emailField" data-rawvalue="' . $rawValue . '" href="mailto:' . $rawValue . '">' . textlength_check($value) . '</a>';
                    }
                } elseif ($fieldDataType == 'boolean') {
                    if ($value === 'on') {
                        $value = 1;
                    } elseif ($value == 'off') {
                        $value = 0;
                    }
                    if ($value == 1) {
                        $value = vtranslate('LBL_YES', $module);
                    } elseif ($value == 0) {
                        $value = vtranslate('LBL_NO', $module);
                    } else {
                        $value = '--';
                    }
                } elseif ($field->getUIType() == 98) {
                    $value = '<a href="index.php?module=Roles&parent=Settings&view=Edit&record=' . $value . '">' . textlength_check(getRoleName($value)) . '</a>';
                } elseif ($fieldDataType == 'multipicklist') {
                    if ($value != '') {
                        $moduleName = getTabModuleName($field->getTabId());
                        $value = explode(' |##| ', $value);
                        foreach ($value as $key => $val) {
                            $value[$key] = vtranslate($val, $moduleName);
                        }
                        $value = implode(' |##| ', $value);
                        $value = str_replace(' |##| ', ', ', $value);
                    }
                } elseif ($fieldDataType == 'skype') {
                    $value = ($value != "") ? "<a href='skype:$value?call'>" . textlength_check($value) . "</a>" : "";
                } elseif ($field->getUIType() == 11) {
                    if ($outgoingCallPermission && !empty($value)) {
                        $phoneNumber = $value;
                        $value = $phoneNumber;
                    } else {
                        $value = textlength_check($value);
                    }
                } elseif ($field->getFieldDataType() == 'reference') {
                    $referenceFieldInfoList = $this->queryGenerator->getReferenceFieldInfoList();
                    $moduleList = $referenceFieldInfoList[$fieldName];
                    $parentModule = 1 === php7_count($moduleList) ? $moduleList[0] : ($this->typeList[$value] ?? null);

                    if (!empty($rawValue) && !empty($parentModule)) {
                        $value = Vtiger_Reference_UIType::transformToDisplayValue($rawValue, $parentModule);
                    } else {
                        $value = '--';
                    }
                } elseif ($fieldDataType == 'owner' || $fieldDataType == 'ownergroup') {
                    $value = textlength_check($this->ownerNameList[$fieldName][$value]);
                } elseif ($field->getUIType() == 25) {
                    //TODO clean request object reference.
                    $contactId = $_REQUEST['record'];
                    $emailId = $this->db->query_result($result, $i, "activityid");
                    $result1 = $this->db->pquery(
                        "SELECT access_count FROM vtiger_email_track WHERE " .
                        "crmid=? AND mailid=?",
                        [$contactId, $emailId]
                    );
                    $value = $this->db->query_result($result1, 0, "access_count");
                    if (!$value) {
                        $value = 0;
                    }
                } elseif ($field->getUIType() == 8) {
                    if (!empty($value)) {
                        $temp_val = html_entity_decode($value, ENT_QUOTES, $default_charset);
                        $json = new Zend_Json();
                        $value = vt_suppressHTMLTags(implode(',', $json->decode($temp_val)));
                    }
                } elseif (90 === $uitype) {
                    $value = "<span align='right'>" . textlength_check($value) . "</span>";
                } elseif ($field && isset($field->isNameField) && $field->isNameField) {
                    $value = "<a href='?module=$field->moduleName&view=Detail&record=$recordId' title='" . vtranslate($field->moduleName, $field->moduleName) . "'>$value</a>";
                } elseif ($field->getUIType() == 61) {
                    $attachmentId = (int)$value;
                    $displayValue = '--';
                    if ($attachmentId) {
                        $displayValue = $attachmentName = $attachmentsCache[$attachmentId];
                        $url = 'index.php?module=' . $module .
                            '&action=DownloadAttachment&record=' . $recordId . '&attachmentid=' . $attachmentId;
                        $displayValue = '<a href="' . $url . '" title="' . vtranslate('LBL_DOWNLOAD_FILE', $module) . '">' .
                            textlength_check($attachmentName) .
                            '</a>';
                    }
                    $value = $displayValue;
                } elseif ($field->getUIType() == Vtiger_Field_Model::UITYPE_USER_PROFILE) {
                    $profileName = Settings_Profiles_Record_Model::getProfileName((int)$value);
                    $value = sprintf('<a href="index.php?module=Profiles&parent=Settings&view=Detail&record=%s">%s</a>', $value, textlength_check($profileName));
                } else {
                    if ($field->getUIType() == Vtiger_Field_Model::UITYPE_TAX) {
                        $rawValue = $baseRecordId;
                    }

                    if (!empty($rawValue)) {
                        $fieldModel = Vtiger_Field_Model::getInstance($field->getFieldId());
                        $uiTypeModel = $fieldModel->getUITypeModel();
                        $value = $uiTypeModel->getDisplayValue($rawValue, $baseRecordId);
                    } else {
                        $value = '';
                    }

                    $value = textlength_check($value);
                }



                $row[$rawFieldName] = $value;
            }

            $data[$baseRecordId] = $row;
        }

        return $data;
    }
}