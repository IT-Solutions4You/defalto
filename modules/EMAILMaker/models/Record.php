<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class EMAILMaker_Record_Model extends Vtiger_Record_Model
{

    public static function getInstanceById($templateId, $module = null)
    {
        $db = PearDatabase::getInstance();
        $result = $db->pquery(
            'SELECT vtiger_emakertemplates_displayed.*, vtiger_emakertemplates.*  FROM vtiger_emakertemplates 
                                    LEFT JOIN vtiger_emakertemplates_displayed ON vtiger_emakertemplates_displayed.templateid = vtiger_emakertemplates.templateid 
                                    WHERE vtiger_emakertemplates.templateid = ?',
            array($templateId)
        );
        if ($db->num_rows($result) > 0) {
            $row = $db->query_result_rowdata($result, 0);
            $recordModel = new self();
            $row['label'] = $row['templatename'];

            return $recordModel->setData($row)->setId($templateId)->setModule($row['module'] != "" ? $row['module'] : 'EMAILMaker');
        }
        return null;
    }

    public function setId($value)
    {
        return $this->set('templateid', $value);
    }

    /**
     * @param int $templateId
     * @return string
     * @throws Exception
     */
    public static function getDefaultFromEmail($templateId)
    {
        $currentUser = Users_Record_Model::getCurrentUserModel();
        $adb = PearDatabase::getInstance();
        $result_lfn = $adb->pquery(
            'SELECT fieldname FROM vtiger_emakertemplates_default_from WHERE templateid = ? AND userid = ?',
            array($templateId, $currentUser->id)
        );

        return $adb->query_result($result_lfn, 0, 'fieldname');
    }

    /**
     * @return string
     */
    public static function getIgnorePicklistValues()
    {
        $adb = PearDatabase::getInstance();
        $ignore_picklist_values = '';
        $result = $adb->pquery('SELECT value FROM vtiger_emakertemplates_ignorepicklistvalues', array());

        if ($adb->num_rows($result)) {
            $values = array();

            while ($row = $adb->fetchByAssoc($result)) {
                $values[] = $row['value'];
            }

            $ignore_picklist_values = implode(', ', $values);
        }

        return $ignore_picklist_values;
    }

    /**
     * @return array
     */
    public static function getDecimalSettings()
    {
        $current_user = Users_Record_Model::getCurrentUserModel();
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery('SELECT * FROM vtiger_emakertemplates_settings', array());

        if ($adb->num_rows($result)) {
            $settingsResult = $adb->fetchByAssoc($result, 0);

            return array(
                'point' => $settingsResult['decimal_point'],
                'decimals' => $settingsResult['decimals'],
                'thousands' => ($settingsResult['thousands_separator'] != 'sp' ? $settingsResult['thousands_separator'] : ' ')
            );
        }

        $thousands_separator = $current_user->currency_grouping_separator;

        return array(
            'point' => $current_user->currency_decimal_separator,
            'decimals' => $current_user->no_of_currency_decimals,
            'thousands' => ($thousands_separator != 'sp' ? $thousands_separator : ' ')
        );
    }

    /**
     * @return array
     * @throws Exception
     */
    public static function getCompanyImages()
    {
        global $site_URL;

        $adb = PearDatabase::getInstance();
        $result = $adb->pquery('SELECT * FROM vtiger_organizationdetails', array());
        $row = $adb->query_result_rowdata($result);
        $path = $site_URL . '/test/logo/';
        $images = array(
            'logoname' => decode_html($row['logoname']),
            'headername' => decode_html($row['headername']),
            'stamp_signature' => $row['stamp_signature'],
        );

        if (isset($images['logoname'])) {
            $images['logoname_img'] = "<img src=\"" . $path . $images['logoname'] . "\">";
        }

        if (isset($images['headername'])) {
            $images['headername_img'] = "<img src=\"" . $path . $images['headername'] . "\">";
        }

        if (isset($images['stamp_signature'])) {
            $images['stamp_signature_img'] = "<img src=\"" . $path . $images['stamp_signature'] . "\">";
        }

        return $images;
    }

    public static function saveTemplate($templateParams, $templateId)
    {
        $adb = PearDatabase::getInstance();
        $templateTable = 'vtiger_emakertemplates';
        $templateTableId = 'templateid';

        if (!empty($templateId)) {
            $templateQuery = self::getUpdateFromParams($templateParams, $templateTable, $templateTableId);
            $templateParams[$templateTableId] = $templateId;

            $adb->pquery($templateQuery, $templateParams);
        } else {
            $templateId = $adb->getUniqueID($templateTable);
            $templateParams[$templateTableId] = $templateId;
            $templateQuery = self::getInsertFromParams($templateParams, $templateTable);

            $adb->pquery($templateQuery, $templateParams);
        }

        return $templateId;
    }

    /**
     * @param $params
     * @param $table
     * @param false $index
     * @return string
     */
    public static function getUpdateFromParams($params, $table, $index = false)
    {
        $query = 'UPDATE `' . $table . '` SET `' . implode('`=?,`', array_keys($params)) . '`=?';

        if ($index) {
            $query .= ' WHERE `' . $index . '`=?';
        }

        return $query;
    }

    /**
     * @param $params
     * @param $table
     * @return string
     */
    public static function getInsertFromParams($params, $table)
    {
        return 'INSERT INTO `' . $table . '` (`' . implode('`,`', array_keys($params)) . '`) VALUES (' . generateQuestionMarks($params) . ')';
    }

    public static function saveTemplateSettings($settingsParams)
    {
        $adb = PearDatabase::getInstance();
        $settingsResult = $adb->pquery('SELECT * FROM vtiger_emakertemplates_settings', array());
        $settingsTable = 'vtiger_emakertemplates_settings';

        if ($adb->num_rows($settingsResult)) {
            $settingsQuery = EMAILMaker_Record_Model::getUpdateFromParams($settingsParams, $settingsTable);
        } else {
            $settingsQuery = EMAILMaker_Record_Model::getInsertFromParams($settingsParams, $settingsTable);
        }

        $adb->pquery($settingsQuery, $settingsParams);
    }

    public static function saveIgnoredPicklistValues($values)
    {
        $adb = PearDatabase::getInstance();
        $adb->query('DELETE FROM vtiger_emakertemplates_ignorepicklistvalues');

        foreach ($values as $value) {
            $adb->pquery(
                'INSERT INTO vtiger_emakertemplates_ignorepicklistvalues(value) VALUES(?)',
                array(trim($value))
            );
        }
    }

    public static function saveUserStatus($templateId, $moduleName, $isActive, $isDefaultListView, $isDefaultDetailView, $order)
    {
        $adb = PearDatabase::getInstance();
        $currentUser = Users_Record_Model::getCurrentUserModel();
        $currentUserId = $currentUser->getId();

        //unset the former default template because only one template can be default per user x module
        $is_default_bin = $isDefaultListView . $isDefaultDetailView;
        $is_default_dec = intval(base_convert($is_default_bin, 2, 10)); // convert binary format xy to decimal; where x stands for is_default_lv and y stands for is_default_dv

        if ($is_default_dec > 0) {
            $userStatusQuery = 'UPDATE vtiger_emakertemplates_userstatus INNER JOIN vtiger_emakertemplates USING(templateid) SET is_default=? WHERE is_default=? AND userid=? AND module=?';

            switch ($is_default_dec) {
                //in case of only is_default_dv is checked
                case 1:
                    $adb->pquery($userStatusQuery, array('0', '1', $currentUserId, $moduleName));
                    $adb->pquery($userStatusQuery, array('2', '3', $currentUserId, $moduleName));
                    break;
                //in case of only is_default_lv is checked
                case 2:
                    $adb->pquery($userStatusQuery, array('0', '2', $currentUserId, $moduleName));
                    $adb->pquery($userStatusQuery, array('1', '3', $currentUserId, $moduleName));
                    break;
                //in case of both is_default_* are checked
                case 3:
                    $userStatusQuery = 'UPDATE vtiger_emakertemplates_userstatus INNER JOIN vtiger_emakertemplates USING(templateid) SET is_default=? WHERE is_default > ? AND userid=? AND module=?';
                    $adb->pquery($userStatusQuery, array('0', '0', $currentUserId, $moduleName));
            }
        }

        $adb->pquery('DELETE FROM vtiger_emakertemplates_userstatus WHERE templateid=? AND userid=?', array($templateId, $currentUserId));
        $adb->pquery('INSERT INTO vtiger_emakertemplates_userstatus(templateid, userid, is_active, is_default, sequence) VALUES(?,?,?,?,?)', array($templateId, $currentUserId, $isActive, $is_default_dec, $order));
    }

    public static function saveSharing($templateId, $sharingType, $members) {
        //SHARING
        $adb = PearDatabase::getInstance();
        $adb->pquery('DELETE FROM vtiger_emakertemplates_sharing WHERE templateid=?', array($templateId));

        $member_array = $members;

        if ($sharingType == 'share' && EMAILMaker_Utils_Helper::count($member_array) > 0) {
            $groupMemberArray = self::constructSharingMemberArray($member_array);
            $sharingQuery = '';
            $sharingParams = array();

            foreach ($groupMemberArray as $setype => $shareIdArr) {
                foreach ($shareIdArr as $shareId) {
                    $sharingQuery .= "(?, ?, ?),";
                    $sharingParams[] = $templateId;
                    $sharingParams[] = $shareId;
                    $sharingParams[] = $setype;
                }
            }

            if (!empty($sharingQuery)) {
                $sharingQuery = 'INSERT INTO vtiger_emakertemplates_sharing(templateid, shareid, setype) VALUES ' . rtrim($sharingQuery, ',');
                $adb->pquery($sharingQuery, $sharingParams);
            }
        }
    }

    public static function saveDefaultFrom($templateId, $defaultFromEmail)
    {
        //DEFAULT FROM SETTING
        $currentUser = Users_Record_Model::getCurrentUserModel();
        $currentUserId = $currentUser->getId();

        $adb = PearDatabase::getInstance();
        $adb->pquery('DELETE FROM vtiger_emakertemplates_default_from WHERE templateid=? AND userid=?', array($templateId, $currentUserId));

        if (!empty($defaultFromEmail)) {
            $adb->pquery(
                'INSERT INTO vtiger_emakertemplates_default_from (templateid,userid,fieldname) VALUES (?,?,?)',
                array(
                    $templateId,
                    $currentUserId,
                    $defaultFromEmail
                )
            );
        }
    }

    public static function saveDisplayed($templateId, $displayedValue, $displayedConditions)
    {
        $adb = PearDatabase::getInstance();
        $adb->pquery('DELETE FROM vtiger_emakertemplates_displayed WHERE templateid=?', array($templateId));
        $adb->pquery(
            'INSERT INTO vtiger_emakertemplates_displayed (templateid,displayed,conditions) VALUES (?,?,?)',
            array(
                $templateId,
                $displayedValue,
                Zend_Json::encode($displayedConditions)
            )
        );
    }

    public static function constructSharingMemberArray($member_array)
    {
        $groupMemberArray = [];

        foreach ($member_array as $member) {
            $memSubArray = explode(':', $member);

            switch ($memSubArray[0]) {
                case 'RoleAndSubordinates':
                    $groupMemberArray['rs'][] = $memSubArray[1];
                    break;
                default:
                    $groupMemberArray[strtolower($memSubArray[0])][] = $memSubArray[1];
                    break;
            }
        }

        return $groupMemberArray;
    }

    public function delete()
    {
        $this->getModule()->deleteRecord($this);
    }

    public function deleteAllRecords()
    {
        $this->getModule()->deleteAllRecords();
    }

    public function getEmailTemplateFields()
    {
        return $this->getModule()->getAllModuleEmailTemplateFields();
    }

    public function getTemplateData($record)
    {
        return $this->getModule()->getTemplateData($record);
    }

    /**
     *  Functions returns delete url
     * @return String - delete url
     */
    public function getDeleteUrl()
    {
        return 'index.php?module=EMAILMaker&action=Delete&record=' . $this->getId();
    }

    public function getId()
    {
        return $this->get('templateid');
    }

    /**
     * Function to get the Edit View url for the record
     * @return <String> - Record Edit View Url
     */
    public function getEditViewUrl()
    {
        return 'index.php?module=EMAILMaker&view=Edit&record=' . $this->getId();
    }

    /**
     * Funtion to get Duplicate Record Url
     * @return <String>
     */
    public function getDuplicateRecordUrl()
    {
        return 'index.php?module=EMAILMaker&view=Edit&record=' . $this->getId() . '&isDuplicate=true';
    }

    public function getDetailViewUrl()
    {
        $module = $this->getModule();
        return 'index.php?module=EMAILMaker&view=' . $module->getDetailViewName() . '&record=' . $this->getId();
    }

    public function getName()
    {
        return $this->get('templatename');
    }

    public function isDeleted()
    {
        if ($this->get('deleted') == '1') {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Function returns valuetype of the field filter
     * @return <String>
     */
    public function getFieldFilterValueType($fieldname)
    {
        $conditions = $this->get('conditions');
        if (!empty($conditions) && is_array($conditions)) {
            foreach ($conditions as $filter) {
                if ($fieldname == $filter['fieldname']) {
                    return $filter['valuetype'];
                }
            }
        }
        return false;
    }

    public function updateDisplayConditions($conditions, $displayed_value)
    {
        $adb = PearDatabase::getInstance();
        $templateid = $this->getId();
        $adb->pquery("DELETE FROM vtiger_emakertemplates_displayed WHERE templateid=?", array($templateid));

        $conditions = $this->transformAdvanceFilterToEMAILMakerFilter($conditions);

        $display_conditions = Zend_Json::encode($conditions);


        $adb->pquery("INSERT INTO vtiger_emakertemplates_displayed (templateid,displayed,conditions) VALUES (?,?,?)", array($templateid, $displayed_value, $display_conditions));
        return true;
    }

    public function transformAdvanceFilterToEMAILMakerFilter($conditions)
    {
        $wfCondition = array();

        if (!empty($conditions)) {
            foreach ($conditions as $index => $condition) {
                $columns = $condition['columns'];
                if ($index == '1' && empty($columns)) {
                    $wfCondition[] = array(
                        'fieldname' => '',
                        'operation' => '',
                        'value' => '',
                        'valuetype' => '',
                        'joincondition' => '',
                        'groupid' => '0'
                    );
                }
                if (!empty($columns) && is_array($columns)) {
                    foreach ($columns as $column) {
                        $wfCondition[] = array(
                            'fieldname' => $column['columnname'],
                            'operation' => $column['comparator'],
                            'value' => $column['value'],
                            'valuetype' => $column['valuetype'],
                            'joincondition' => $column['column_condition'],
                            'groupjoin' => $condition['condition'],
                            'groupid' => $column['groupid']
                        );
                    }
                }
            }
        }
        return $wfCondition;
    }

    public function getConditonDisplayValue()
    {
        $conditionList = array(
            'All' => [],
            'Any' => [],
        );
        $displayed = $this->get('displayed');
        $conditions = $this->get('conditions');
        $moduleName = $this->get('module');
        if (!empty($conditions)) {
            $EMAILMaker_Display_Model = new EMAILMaker_Display_Model();
            $conditionList = $EMAILMaker_Display_Model->getConditionsForDetail($displayed, $conditions, $moduleName);
        }
        return $conditionList;
    }

    public static function getTemplateId($params)
    {
        $adb = PearDatabase::getInstance();
        $sql = sprintf('SELECT templateid FROM vtiger_emakertemplates WHERE %s=?', implode('=? AND ', array_keys($params)));
        $result = $adb->pquery($sql, $params);

        return $adb->query_result($result, 0, 'templateid');
    }
}
