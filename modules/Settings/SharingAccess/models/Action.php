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

/**
 * Sharing Access Action Model Class
 */
class Settings_SharingAccess_Action_Model extends Vtiger_Base_Model
{
    static $nonConfigurableActions = ['Hide Details', 'Hide Details and Add Events', 'Show Details', 'Show Details and Add Events'];

    public function getId()
    {
        return $this->get('share_action_id');
    }

    public function getName()
    {
        return $this->get('share_action_name');
    }

    public function isUtilityTool()
    {
        return false;
    }

    public function isModuleEnabled($module)
    {
        $db = PearDatabase::getInstance();
        $tabId = $module->getId();

        $sql = 'SELECT 1 FROM vtiger_org_share_action2tab WHERE tabid = ? AND share_action_id = ?';
        $params = [$tabId, $this->getId()];
        $result = $db->pquery($sql, $params);
        if ($result && $db->num_rows($result) > 0) {
            return true;
        }

        return false;
    }

    public static function getInstanceFromQResult($result, $rowNo = 0)
    {
        $db = PearDatabase::getInstance();
        $row = $db->query_result_rowdata($result, $rowNo);
        $actionModel = new Settings_SharingAccess_Action_Model();

        return $actionModel->setData($row);
    }

    public static function getInstance($value)
    {
        $db = PearDatabase::getInstance();

        if (Vtiger_Utils::isNumber($value)) {
            $sql = 'SELECT * FROM vtiger_org_share_action_mapping WHERE share_action_id = ?';
        } else {
            $sql = 'SELECT * FROM vtiger_org_share_action_mapping WHERE share_action_name = ?';
        }
        $params = [$value];
        $result = $db->pquery($sql, $params);
        if ($db->num_rows($result) > 0) {
            return self::getInstanceFromQResult($result);
        }

        return null;
    }

    public static function getAll($configurable = true)
    {
        $db = PearDatabase::getInstance();

        $sql = 'SELECT * FROM vtiger_org_share_action_mapping';
        $params = [];
        if ($configurable) {
            $sql .= ' WHERE share_action_name NOT IN (' . generateQuestionMarks(self::$nonConfigurableActions) . ')';
            array_push($params, self::$nonConfigurableActions);
        }
        $result = $db->pquery($sql, $params);
        $noOfRows = $db->num_rows($result);
        $actionModels = [];
        for ($i = 0; $i < $noOfRows; ++$i) {
            $actionModels[] = self::getInstanceFromQResult($result, $i);
        }

        return $actionModels;
    }
}