<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Vtiger_Install_Model extends Core_Install_Model
{
    public function addCustomLinks(): void
    {
    }

    public function deleteCustomLinks(): void
    {
    }

    public function getBlocks(): array
    {
        return [];
    }

    public function getTables(): array
    {
        return [];
    }

    public function installModule()
    {
        if ($this->isRequiredInstallTables()) {
            $this->installTables();
        }

        (new Vtiger_Field_Model())->insertDefaultData();
    }

    /**
     * @throws Exception
     */
    public function installTables(): void
    {
        (new Vtiger_Module_Model())->createTables();
        (new Vtiger_Field_Model())->createTables();
        (new Vtiger_Block_Model())->createTables();
        (new Vtiger_Record_Model())->createTables();
        (new Core_BlockUiType_Model())->createTables();
        (new Settings_Workflows_Record_Model())->createTables();
        (new Settings_Workflows_TaskRecord_Model())->createTables();

        $this->createPicklistTable('vtiger_taxtype', 'taxtypeid', 'taxtype');
    }

    /**
     * @return void
     * @throws Exception
     */
    public function migrate()
    {
        $fieldTable = (new Vtiger_Field_Model())->getFieldTable();
        $fieldTable->updateData(['columnname' => 'assigned_user_id', 'fieldname' => 'assigned_user_id'], ['columnname' => 'smownerid']);
        $fieldTable->updateData(['columnname' => 'creator_user_id', 'fieldname' => 'creator_user_id'], ['columnname' => 'smcreatorid']);
        $fieldTable->updateData(['columnname' => 'createdtime', 'fieldname' => 'createdtime'], ['columnname' => 'createdtime']);
        $fieldTable->updateData(['columnname' => 'modifiedtime', 'fieldname' => 'modifiedtime'], ['columnname' => 'modifiedtime']);

        $this->getDB()->pquery('DELETE FROM vtiger_field WHERE fieldid NOT IN (SELECT min(fieldid) FROM vtiger_field GROUP BY tabid,columnname,tablename,fieldname)');
    }
}