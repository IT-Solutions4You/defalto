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
    public static array $deleteFiles = [
        'modules/Invoice/views/Edit.php',
        'modules/SalesOrder/views/Edit.php',
        'modules/PurchaseOrder/views/Edit.php',
        'modules/Quotes/views/Edit.php',
    ];

    public static array $createTablesClasses = [
        'Vtiger_Module_Model',
        'Vtiger_Field_Model',
        'Vtiger_Block_Model',
        'Vtiger_Record_Model',
        'Core_BlockUiType_Model',
        'Settings_Workflows_Record_Model',
        'Settings_Workflows_TaskRecord_Model',
        'Core_InventoryItemsBlock_Model',
        'Core_RelatedBlock_Model',
        'Core_Tax_Model',
        'Core_TaxRegion_Model',
        'Core_TaxRecord_Model',
        'Settings_Vtiger_MenuItem_Model',
        'Settings_Vtiger_Menu_Model',
        'Core_Modifiers_Model',
    ];

    public array $registerFieldTypes = [
        [Vtiger_Field_Model::UITYPE_CURRENCY_CODE, Vtiger_Field_Model::CURRENCY_LIST],
        [Vtiger_Field_Model::UITYPE_REGION, 'region'],
        [Vtiger_Field_Model::UITYPE_TAX, 'tax'],
        [Vtiger_Field_Model::UITYPE_COUNTRY, 'country'],
    ];

    /**
     * @var array
     * [name, handler, frequency, module, sequence, description]
     */
    public array $registerCron = [
        ['Workflow', 'cron/modules/com_vtiger_workflow/com_vtiger_workflow.php', 900, 'com_vtiger_workflow', 1, 'Recommended frequency for Workflow is 15 mins'],
        ['RecurringInvoice', 'cron/modules/SalesOrder/RecurringInvoice.php', 3600, 'SalesOrder', 2, 'Recommended frequency for RecurringInvoice is 1 hour'],
        ['SendReminder', 'cron/SendReminder.php', 900, 'Appointments', 3, 'Recommended frequency for SendReminder is 15 mins'],
        ['MailScanner', 'cron/MailScanner.php', 900, 'Settings', 4, 'Recommended frequency for MailScanner is 15 mins'],
    ];

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

        $this->updateCron();
        $this->updateFieldTypes();
    }

    /**
     * @throws Exception
     */
    public function installTables(): void
    {
        foreach (self::$createTablesClasses as $className) {
            if (class_exists($className) && method_exists($className, 'createTables')) {
                (new $className())->createTables();
            }
        }

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

        global $root_directory;

        self::logSuccess('Files delete start');

        foreach (self::$deleteFiles as $deleteFile) {
            self::logSuccess('File delete: ' . $deleteFile);

            if (is_file($deleteFile) && unlink($deleteFile)) {
                self::logSuccess('File deleted');
            } else {
                self::logInfo('File already deleted');
            }
        }

        self::logSuccess('Files delete end');
    }
}