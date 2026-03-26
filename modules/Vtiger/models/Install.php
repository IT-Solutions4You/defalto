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
        'modules/Invoice/views/Detail.php',
        'modules/SalesOrder/views/Detail.php',
        'modules/PurchaseOrder/views/Detail.php',
        'modules/Quotes/views/Detail.php',
        'modules/Invoice/views/QuickCreateAjax.php',
        'modules/SalesOrder/views/QuickCreateAjax.php',
        'modules/Quotes/views/QuickCreateAjax.php',
        'modules/Potentials/views/Detail.php',
        'modules/Import/resources/Import.js',
        'modules/Webforms/Webforms.js',
        'modules/Vendors/Vendors.js',
        'modules/Users/Users.js',
        'modules/Tooltip/TooltipHeaderScript.js',
        'modules/Tooltip/Tooltip.js',
        'modules/Tooltip/TooltipSettings.js',
        'modules/Settings/Settings.js',
        'modules/Services/multifile.js',
        'modules/Services/Services.js',
        'modules/Services/Servicesslide.js',
        'modules/ServiceContracts/ServiceContracts.js',
        'modules/SalesOrder/SalesOrder.js',
        'modules/Rss/Rss.js',
        'modules/RecycleBin/RecycleBin.js',
        'modules/Quotes/Quotes.js',
        'modules/PurchaseOrder/PurchaseOrder.js',
        'modules/ProjectTask/ProjectTask.js',
        'modules/ProjectMilestone/ProjectMilestone.js',
        'modules/Project/Project.js',
        'modules/Products/Productsslide.js',
        'modules/Products/Products.js',
        'modules/Products/multifile.js',
        'modules/PriceBooks/PriceBooks.js',
        'modules/Potentials/Potentials.js',
        'modules/Portal/Portal.js',
        'modules/Picklist/DependencyPicklist.js',
        'modules/PBXManager/PBXManager.js',
        'modules/MailManager/MailManager.js',
        'modules/Leads/Leads.js',
        'modules/Invoice/Invoice.js',
        'modules/Integration/Integration.js',
        'modules/Home/Homestuff.js',
        'modules/HelpDesk/HelpDesk.js',
        'modules/Faq/Faq.js',
        'modules/CustomerPortal/CustomerPortal.js',
        'modules/CronTasks/CronTasks.js',
        'modules/Contacts/Contacts.js',
        'modules/ConfigurationEditor/ConfigEditor.js',
        'modules/Documents/Documents.js',
        'modules/CustomView/CustomView.js',
        'modules/ModComments/ModComments.js',
        'modules/ModComments/ModCommentsCommon.js',
        'modules/ModTracker/ModTracker.js',
        'modules/ModTracker/ModTrackerCommon.js',
        'modules/com_vtiger_workflow/resources/functional.js',
        'modules/com_vtiger_workflow/resources/parallelexecuter.js',
        'modules/com_vtiger_workflow/resources/editworkflowscript.js',
        'modules/com_vtiger_workflow/resources/fieldexpressionpopup.js',
        'modules/com_vtiger_workflow/resources/workflowlistscript.js',
        'modules/com_vtiger_workflow/resources/fieldvalidator.js',
        'modules/com_vtiger_workflow/resources/updatefieldstaskscript.js',
        'modules/com_vtiger_workflow/resources/jquery-1.2.6.js',
        'modules/com_vtiger_workflow/resources/json2.js',
        'modules/com_vtiger_workflow/resources/vtigerwebservices.js',
        'modules/com_vtiger_workflow/resources/jquery.timepicker.js',
        'modules/com_vtiger_workflow/resources/createentitytaskscript.js',
        'modules/com_vtiger_workflow/resources/edittaskscript.js',
        'modules/com_vtiger_workflow/resources/emailtaskscript.js',
        'modules/FieldFormulas/editexpressionscript.js',
        'modules/FieldFormulas/jquery-1.2.6.js',
        'modules/FieldFormulas/json2.js',
        'modules/FieldFormulas/vtigerwebservices.js',
        'modules/FieldFormulas/functional.js',
        'modules/Vtiger/helpers/Logger.php',
        'layouts/d1/modules/Settings/Vtiger/resources/Tax.js',
        'layouts/d1/modules/Settings/Vtiger/ChargesAndItsTaxes.tpl',
        'layouts/d1/modules/Settings/Vtiger/EditCharge.tpl',
        'layouts/d1/modules/Settings/Vtiger/EditRegion.tpl',
        'layouts/d1/modules/Settings/Vtiger/EditTax.tpl',
        'layouts/d1/modules/Settings/Vtiger/TaxIndex.tpl',
        'layouts/d1/modules/Settings/Vtiger/TaxRegions.tpl',
        'layouts/d1/modules//Vtiger/uitypes/ProductTax.tpl',
        'modules/Inventory/models/Charges.php',
        'modules/Inventory/models/TaxRecord.php',
        'modules/Inventory/models/TaxRegion.php',
        'modules/Vtiger/uitypes/ProductTax.php',
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
        'CustomView_Record_Model',
        'Settings_LayoutEditor_RelatedListSettings_Model',
        'Settings_LayoutEditor_PopupSettings_Model',
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