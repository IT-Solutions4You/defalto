<?php
/*
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Vtiger_Install_Model extends Core_Install_Model
{
    /**
     * @var array
     * [name, handler, frequency, module, sequence, description]
     */
    public array $registerCron = [
        ['Workflow', 'cron/modules/com_vtiger_workflow/com_vtiger_workflow.php', 900, 'com_vtiger_workflow', 1, 'Recommended frequency for Workflow is 15 mins'],
        ['RecurringInvoice', 'cron/modules/SalesOrder/RecurringInvoice.php', 43200, 'SalesOrder', 2, 'Recommended frequency for RecurringInvoice is 12 hours'],
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
        (new Vtiger_Field_Model())->insertDefaultData();
        $this->updateCron();
    }

    /**
     * @throws AppException
     */
    public function installTables(): void
    {
        (new Vtiger_Module_Model())->createTables();
        (new Vtiger_Field_Model())->createTables();
        (new Vtiger_Block_Model())->createTables();
        (new Core_BlockUiType_Model())->createTables();
        (new Settings_Workflows_Record_Model())->createTables();
        (new Settings_Workflows_TaskRecord_Model())->createTables();
    }
}