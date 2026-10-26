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

class Vtiger_RecipientPreference_Model extends Vtiger_Record_Model
{
    protected string $table = 'vtiger_emails_recipientprefs';
    protected string $tableId = 'id';
    protected array $columns = ['id', 'tabid', 'prefs', 'userid'];
    protected string $moduleColumn = 'tabid';
    protected string $userColumn = 'userid';
    protected static $instanceCache = [];

    public static function getInstance(string $moduleName): ?self
    {
        if (!isset(self::$instanceCache[$moduleName])) {
            $instance = new self();
            $currentUserModel = Users_Record_Model::getCurrentUserModel();
            $data = $instance->getRecipientPreferenceTable()->selectData(
                $instance->columns,
                [
                    $instance->moduleColumn => getTabid($moduleName),
                    $instance->userColumn => $currentUserModel->getId(),
                ],
            );

            if (empty($data)) {
                return null;
            }

            $data['prefs'] = Zend_Json::decode($data['prefs']);
            $instance->setData($data);
            self::$instanceCache[$moduleName] = $instance;
        }

        return self::$instanceCache[$moduleName];
    }

    public function getRecipientPreferenceTable(): Core_DatabaseData_Model
    {
        return $this->getTable($this->table, $this->tableId);
    }

    public function save(): void
    {
        $table = $this->getRecipientPreferenceTable();
        $currentUserModel = Users_Record_Model::getCurrentUserModel();

        if ($this->getId()) {
            $table->updateData(
                ['prefs' => Zend_Json::encode($this->get('prefs'))],
                [
                    $this->tableId => $this->getId(),
                    $this->moduleColumn => $this->get($this->moduleColumn),
                    $this->userColumn => $this->get($this->userColumn),
                ],
            );
        } else {
            $userId = $currentUserModel->getId();
            $recordId = $table->insertData([
                $this->moduleColumn => $this->get($this->moduleColumn),
                'prefs' => Zend_Json::encode($this->get('prefs')),
                $this->userColumn => $userId,
            ]);
            $this->set($this->userColumn, $userId);
            $this->setId((int)$recordId);
        }
    }

    public function delete(): void
    {
        $this->getRecipientPreferenceTable()->deleteData([$this->tableId => $this->getId()]);
    }

    public function getPreferences()
    {
        return $this->get('prefs');
    }

    public function getSourceModule()
    {
        return getTabModuleName($this->get($this->moduleColumn));
    }

    public function setSourceModule($moduleName)
    {
        $this->set($this->moduleColumn, getTabid($moduleName));
    }
}
