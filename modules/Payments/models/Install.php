<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

/**
 * Installs Payments as a module without records, fields or tables. Its All filter uses the fields of the payment
 * modules, because filters, searching and ordering work with field names that every payment module shares.
 */
class Payments_Install_Model extends Core_Install_Model
{
    public bool $requireInstallTables = false;

    /**
     * Registers the module links: Payments stays an installable custom module, the documents get the common
     * payments widget and the payment modules are hidden in the menu (once).
     *
     * @return void
     * @throws Exception
     */
    public function addCustomLinks(): void
    {
        $this->updateToCustomModule();
        $this->updateWebserviceHandler();
        $this->updateListFilter();
        Core_PaymentWidget_Model::getInstance()->updateDocumentWidgets();
        Core_PaymentMenu_Model::getInstance()->hideModules();
    }

    /**
     * Creates the All filter of the Payments list with the default columns when it does not exist yet. Payments
     * installed before any payment module has no columns to offer, so the first installed payment module calls this
     * again (Core_PaymentInstall_Model::updatePaymentsListFilter()).
     *
     * @return void
     * @throws Exception
     */
    public function updateListFilter(): void
    {
        $moduleInstance = Vtiger_Module::getInstance($this->moduleName);
        $sourceModule = Vtiger_Module_Model::getInstance(Payments_Sources_Helper::getPrimaryModule());

        if (!$moduleInstance || !$sourceModule || Vtiger_Filter::getInstance('All', $moduleInstance)) {
            return;
        }

        // the module cache of updateFilters() is filled only while Payments itself installs, so the filter is built here
        $filterInstance = $this->createFilter('All', $moduleInstance);

        foreach (Payments_Sources_Helper::LIST_FIELDS as $index => $fieldName) {
            $field = Vtiger_Field_Model::getInstance($fieldName, $sourceModule);

            if ($field) {
                $filterInstance->addField($field, $index + 1);
            }
        }
    }

    /**
     * Registers the webservice entity after the core created the default one, with the Payments handler.
     *
     * @return void
     */
    public function postInstall(): void
    {
        parent::postInstall();
        $this->updateWebserviceHandler();
    }

    /**
     * Points the webservice entity of Payments to Payments_WebserviceOperation_Helper, which supplies the metadata
     * of the primary payment module to core code such as CustomView.
     *
     * @return void
     */
    public function updateWebserviceHandler(): void
    {
        $result = $this->db->pquery('SELECT id FROM vtiger_ws_entity WHERE name = ? ORDER BY id', [$this->moduleName]);
        $entityIds = [];

        while ($row = $this->db->fetchByAssoc($result)) {
            $entityIds[] = (int)$row['id'];
        }

        if (!$entityIds) {
            return;
        }

        // vtws_addDefaultModuleTypeEntity() adds a default row again on every update; keep only the first one.
        $entityId = array_shift($entityIds);

        if ($entityIds) {
            $this->db->pquery('DELETE FROM vtiger_ws_entity WHERE id IN (' . generateQuestionMarks($entityIds) . ')', $entityIds);
        }

        $this->db->pquery(
            'UPDATE vtiger_ws_entity SET handler_path = ?, handler_class = ? WHERE id = ?',
            ['modules/Payments/helpers/WebserviceOperation.php', 'Payments_WebserviceOperation_Helper', $entityId]
        );
    }

    /**
     * Removes the common widget and the menu change, and the webservice entity, which the core leaves behind,
     * before the core drops the module.
     *
     * @return void
     * @throws Exception
     */
    public function deleteModule(): void
    {
        $this->deleteCustomLinks();
        $this->db->pquery('DELETE FROM vtiger_ws_entity WHERE name = ?', [$this->moduleName]);
        parent::deleteModule();
    }

    /**
     * Gives the documents back the widgets of the payment modules. When Payments is disabled or uninstalled,
     * the payment modules are shown in the menu again; an update keeps the menu as it is.
     *
     * @return void
     * @throws Exception
     */
    public function deleteCustomLinks(): void
    {
        Core_PaymentWidget_Model::getInstance()->updateDocumentWidgets([$this->moduleName]);

        if ('module.preupdate' !== $this->eventType) {
            Core_PaymentMenu_Model::getInstance()->showModules();
        }
    }

    /**
     * Keeps the module marked as a custom (installable) module, so the Installer may uninstall it.
     *
     * @return void
     */
    public function updateToCustomModule(): void
    {
        $this->db->pquery('UPDATE vtiger_tab SET customized = 1, source = ? WHERE name = ?', ['custom', $this->moduleName]);
    }

    /**
     * Payments has no blocks or fields of its own.
     *
     * @return array
     */
    public function getBlocks(): array
    {
        return [];
    }

    /**
     * Payments owns no tables.
     *
     * @return array
     */
    public function getTables(): array
    {
        return [];
    }

    /**
     * Payments owns no tables.
     *
     * @return void
     */
    public function installTables(): void
    {
    }

    /**
     * Adds the default columns of the All filter from the fields of the primary payment module.
     * Only the field name of a filter column is used when the list is built, so the column works for every source.
     *
     * @return void
     */
    public function retrieveFilters(): void
    {
        $sourceModule = Vtiger_Module_Model::getInstance(Payments_Sources_Helper::getPrimaryModule());

        if (!$sourceModule) {
            return;
        }

        foreach (Payments_Sources_Helper::LIST_FIELDS as $index => $fieldName) {
            $field = Vtiger_Field_Model::getInstance($fieldName, $sourceModule);

            if ($field) {
                $this->setFilterField('All', $field, $index + 1);
            }
        }
    }
}
