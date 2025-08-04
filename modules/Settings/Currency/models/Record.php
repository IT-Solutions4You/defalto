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

class Settings_Currency_Record_Model extends Settings_Vtiger_Record_Model
{
    public function getId()
    {
        return $this->get('id');
    }

    public function getName()
    {
        return $this->get('currency_name');
    }

    public function isDisabledRestricted()
    {
        $db = PearDatabase::getInstance();
        $disabledRestircted = $this->get('_disable_restricted');
        if (!empty($disabledRestircted)) {
            return $disabledRestircted;
        }
        $query = 'SELECT 1 FROM vtiger_users WHERE currency_id = ?';
        $params = [$this->getId()];
        $result = $db->pquery($query, $params);

        $disabledRestircted = ($db->num_rows($result) > 0) ? true : false;
        $this->set('_disable_restricted', $disabledRestircted);

        return $disabledRestircted;
    }

    public function isBaseCurrency()
    {
        return ($this->get('defaultid') != '-11') ? false : true;
    }

    public function getRecordLinks()
    {
        if ($this->isBaseCurrency()) {
            //NO Edit and delete link for base currency
            return [];
        }
        $editLink = [
            'linkurl'   => "javascript:Settings_Currency_Js.triggerEdit(event, '" . $this->getId() . "')",
            'linklabel' => 'LBL_EDIT',
            'linkicon'  => 'icon-pencil'
        ];
        $editLinkInstance = Vtiger_Link_Model::getInstanceFromValues($editLink);

        $deleteLink = [
            'linkurl'   => "javascript:Settings_Currency_Js.triggerDelete(event,'" . $this->getId() . "')",
            'linklabel' => 'LBL_DELETE',
            'linkicon'  => 'icon-trash'
        ];
        $deleteLinkInstance = Vtiger_Link_Model::getInstanceFromValues($deleteLink);

        return [$editLinkInstance, $deleteLinkInstance];
    }

    public function getDeleteStatus()
    {
        if ($this->has('deleted')) {
            return $this->get('deleted');
        }

        //by default non deleted
        return 0;
    }

    public function save()
    {
        $db = PearDatabase::getInstance();
        $id = $this->getId();
        $tableName = Settings_Currency_Module_Model::tableName;
        if (!empty($id)) {
            $query = 'UPDATE ' . $tableName . ' SET currency_name=?, currency_code=?, 
                currency_status=?,currency_symbol=?,conversion_rate=?, deleted=? WHERE id=?';
            $params = [
                $this->get('currency_name'),
                $this->get('currency_code'),
                $this->get('currency_status'),
                $this->get('currency_symbol'),
                $this->get('conversion_rate'),
                $this->getDeleteStatus(),
                $id
            ];
        } else {
            $id = $db->getUniqueID($tableName);
            $query = 'INSERT INTO ' . $tableName . ' VALUES(?,?,?,?,?,?,?,?)';
            $params = [
                $id,
                $this->get('currency_name'),
                $this->get('currency_code'),
                $this->get('currency_symbol'),
                $this->get('conversion_rate'),
                $this->get('currency_status'),
                0,
                0
            ];
        }
        $db->pquery($query, $params);

        return $id;
    }

    public static function getInstance()
    {
        [$id] = func_get_args();
        $db = PearDatabase::getInstance();
        if (Vtiger_Utils::isNumber($id)) {
            $query = 'SELECT * FROM ' . Settings_Currency_Module_Model::tableName . ' WHERE id=?';
        } else {
            $query = 'SELECT * FROM ' . Settings_Currency_Module_Model::tableName . ' WHERE currency_name=?';
        }

        $params = [$id];
        $result = $db->pquery($query, $params);
        if ($db->num_rows($result) > 0) {
            $instance = new self();
            $row = $db->query_result_rowdata($result, 0);
            $instance->setData($row);
        }

        return $instance;
    }

    public static function getAllNonMapped($includedIds = [])
    {
        $db = PearDatabase::getInstance();
        if (!is_array($includedIds)) {
            if (!empty($includedIds)) {
                $includedIds = [$includedIds];
            } else {
                $includedIds = [];
            }
        }

        $query = 'SELECT vtiger_currencies.* FROM vtiger_currencies 
                    LEFT JOIN vtiger_currency_info ON vtiger_currency_info.currency_name = vtiger_currencies.currency_name
                    WHERE vtiger_currency_info.currency_name IS NULL or vtiger_currency_info.deleted=1';
        $params = [];
        if (!empty($includedIds)) {
            $params = $includedIds;
            $query .= ' OR vtiger_currency_info.id IN(' . generateQuestionMarks($includedIds) . ')';
        }
        $query .= ' ORDER BY vtiger_currencies.currency_name';

        $result = $db->pquery($query, $params);
        $currencyModelList = [];
        $num_rows = $db->num_rows($result);

        for ($i = 0; $i < $num_rows; $i++) {
            $row = $db->query_result_rowdata($result, $i);
            $modelInstance = new self();
            $modelInstance->setData($row);
            $currencyModelList[$row['currencyid']] = $modelInstance;
        }

        return $currencyModelList;
    }

    public static function getAll($excludedIds = [])
    {
        $db = PearDatabase::getInstance();

        if (!is_array($excludedIds)) {
            $excludedIds = [$excludedIds];
        }

        $query = 'SELECT * FROM ' . Settings_Currency_Module_Model::tableName . ' WHERE deleted=0 AND currency_status="Active"';
        $params = [];
        if (!empty($excludedIds)) {
            $params = $excludedIds;
            $query .= ' AND id NOT IN (' . generateQuestionMarks($excludedIds) . ')';
        }
        $result = $db->pquery($query, $params);
        $num_rows = $db->num_rows($result);
        $instanceList = [];

        for ($i = 0; $i < $num_rows; $i++) {
            $row = $db->query_result_rowdata($result, $i);
            $instanceList[$row['id']] = new Settings_Currency_Record_Model($row);
        }

        return $instanceList;
    }
}