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
 * Installation of incoming payments. Fields, tables and registrations are shared by the payment modules
 * (Core_PaymentInstall_Model); this model adds the migrations of earlier IncomingPayment installations.
 */
class IncomingPayment_Install_Model extends Core_PaymentInstall_Model
{
    /**
     * Block of earlier installations, removed by migrate() once its fields are moved.
     */
    private const LEGACY_ACCOUNTING_BLOCK = 'LBL_ACCOUNTING_INFORMATION';

    /**
     * Fields of earlier installations, removed by migrate().
     */
    private const LEGACY_FIELDS = ['relations', 'accountingdate', 'tax_expense', 'vat_amount', 'total_without_vat', 'category'];

    /**
     * Reference column of earlier installations and its index while it was an int column (uitype 10).
     */
    private const LEGACY_RELATION_COLUMN = 'relationid';
    private const LEGACY_RELATION_INDEX = 'df_incomingpayment_relationid_idx';
    private const LEGACY_RELATED_TO_COLUMN = 'relatedto';
    private const LEGACY_RELATED_TO_INDEX = 'df_incomingpayment_relatedto_idx';
    private const LEGACY_STATUS_CRON = 'IncomingPaymentStatus';

    /**
     * Field names of earlier installations and their current names. renameLegacyFields() renames each field and its
     * column in place, so the field id and everything bound to it (profiles, related lists, references) stay.
     * The former currency and rate fields become the hidden fields of the amount with its own currency.
     */
    private const LEGACY_FIELD_NAMES = [
        'paymentamount' => IncomingPayment_Config_Helper::FIELD_AMOUNT,
        'currency_id' => IncomingPayment_Config_Helper::FIELD_AMOUNT_CURRENCY,
        'conversion_rate' => IncomingPayment_Config_Helper::FIELD_AMOUNT_RATE,
        'payamount_main' => IncomingPayment_Config_Helper::FIELD_AMOUNT_BASE,
        'paymentdate' => IncomingPayment_Config_Helper::FIELD_PAYMENT_DATE,
        'paymethod' => IncomingPayment_Config_Helper::FIELD_PAYMENT_METHOD,
        'transactionid' => IncomingPayment_Config_Helper::FIELD_TRANSACTION_ID,
        self::LEGACY_RELATION_COLUMN => IncomingPayment_Config_Helper::FIELD_RELATION,
        'associated_no' => IncomingPayment_Config_Helper::FIELD_REFERENCE_NO,
        self::LEGACY_RELATED_TO_COLUMN => IncomingPayment_Config_Helper::FIELD_RELATED_TO,
        'contactid' => IncomingPayment_Config_Helper::FIELD_CONTACT,
    ];

    /**
     * Hidden fields of the amount with its own currency; saved filters cannot use them.
     */
    private const AMOUNT_HIDDEN_FIELDS = [
        IncomingPayment_Config_Helper::FIELD_AMOUNT_CURRENCY,
        IncomingPayment_Config_Helper::FIELD_AMOUNT_RATE,
    ];

    /**
     * Tables of saved filters whose column values look like "table:column:field:label:type".
     */
    private const FILTER_TABLES = ['vtiger_cvcolumnlist', 'vtiger_cvadvfilter', 'vtiger_cvstdfilter'];

    /**
     * Layout settings that store field names of this module: related list columns with their sort field, and the
     * columns of the record selection popup.
     */
    private const LAYOUT_SETTINGS_TABLES = [
        Settings_LayoutEditor_RelatedListSettings_Model::TABLE => true,
        Settings_LayoutEditor_PopupSettings_Model::TABLE => false,
    ];

    /**
     * Final payment status of earlier installations, replaced by the status shared by all payment modules.
     */
    private const LEGACY_PAID_STATUS = 'Received';

    /**
     * Renames the fields and the final status of earlier installations before the tables and fields are updated;
     * a module update (mode=update) does not call migrate() and keeps the uitype of existing fields.
     *
     * @return void
     * @throws Exception
     */
    protected function preInstallTables(): void
    {
        $this->dropLegacyRelationIndex();
        $this->renameLegacyFields();
        $this->renameLayoutSettingsFields();
        $this->migratePaidStatus();
    }

    /**
     * Migrates an existing installation: removes the legacy fields and the legacy accounting block.
     * The core FieldEventHandler drops the column and the filter columns of a deleted field.
     *
     * @return void
     * @throws Exception
     */
    public function migrate(): void
    {
        foreach (self::LEGACY_FIELDS as $fieldName) {
            $this->deleteField($fieldName);
        }

        $this->renameLegacyFields();
        $this->renameLayoutSettingsFields();
        $this->migratePaidStatus();
        $this->migrateAccountingBlock();
        $this->migrateRelationField();
        $this->migrateStatusCron();
    }

    /**
     * Renames the fields of earlier installations to their current names (LEGACY_FIELD_NAMES) together with their
     * columns, the picklist table of the payment method and the saved filters, workflow conditions, picklist
     * dependencies and history that refer to them. Then the amount becomes the amount with its own currency
     * (uitype 35) and the former currency and rate fields its hidden fields, so createField() finds them with
     * their values. It runs before the fields are created (installTables(), also on a module update, which does
     * not call migrate()) and again in migrate(); a field already renamed is skipped.
     *
     * @return void
     * @throws Exception
     */
    private function renameLegacyFields(): void
    {
        $tabId = (int)getTabid($this->getModuleName());

        if (!$tabId) {
            return;
        }

        $isRenamed = false;

        foreach (self::LEGACY_FIELD_NAMES as $legacyName => $fieldName) {
            $legacyField = $this->fetchFieldRow($tabId, $legacyName);

            if (!$legacyField || $this->fetchFieldRow($tabId, $fieldName)) {
                continue;
            }

            $this->renameLegacyField($tabId, $legacyField, $fieldName);
            $isRenamed = true;
        }

        $this->convertAmountField($tabId);

        if ($isRenamed) {
            Vtiger_Cache::flush();
            Core_AmountCurrency_Model::clearCache();
        }
    }

    /**
     * Renames one field of an earlier installation and its column, and every stored reference to it.
     *
     * @param int    $tabId       Tab id of the module
     * @param array  $legacyField Row of vtiger_field with fieldid, fieldname and columnname
     * @param string $fieldName   Current field and column name
     * @return void
     * @throws Exception
     */
    private function renameLegacyField(int $tabId, array $legacyField, string $fieldName): void
    {
        $legacyName = (string)$legacyField['fieldname'];
        $legacyColumn = (string)$legacyField['columnname'];
        $table = $this->getTable(IncomingPayment_Config_Helper::TABLE, null);

        if (self::LEGACY_RELATED_TO_COLUMN === $legacyName && $table->isKeyExists(self::LEGACY_RELATED_TO_INDEX)) {
            $table->getDB()->pquery(sprintf('ALTER TABLE %s DROP INDEX %s', IncomingPayment_Config_Helper::TABLE, self::LEGACY_RELATED_TO_INDEX), []);
        }

        $table->renameColumn($legacyColumn, $fieldName);
        $this->db->pquery('UPDATE vtiger_field SET fieldname = ?, columnname = ? WHERE fieldid = ?', [$fieldName, $fieldName, (int)$legacyField['fieldid']]);

        if (IncomingPayment_Config_Helper::FIELD_PAYMENT_METHOD === $fieldName) {
            $this->renamePicklistTable($tabId, $legacyName, $fieldName);
        }

        $this->renameFilterColumns($legacyColumn, $legacyName, $fieldName);
        $this->renameWorkflowConditions($legacyName, $fieldName);
        $this->db->pquery('UPDATE vtiger_picklist_dependency SET sourcefield = ? WHERE tabid = ? AND sourcefield = ?', [$fieldName, $tabId, $legacyName]);
        $this->db->pquery('UPDATE vtiger_picklist_dependency SET targetfield = ? WHERE tabid = ? AND targetfield = ?', [$fieldName, $tabId, $legacyName]);
        $this->db->pquery(
            'UPDATE vtiger_modtracker_detail
            INNER JOIN vtiger_modtracker_basic ON vtiger_modtracker_basic.id = vtiger_modtracker_detail.id
            SET vtiger_modtracker_detail.fieldname = ?
            WHERE vtiger_modtracker_basic.module = ? AND vtiger_modtracker_detail.fieldname = ?',
            [$fieldName, $this->getModuleName(), $legacyName]
        );
    }

    /**
     * Makes the amount the amount with its own currency (uitype 35) and the former currency and rate fields its
     * hidden fields. A module update keeps the uitype and the layout flags of existing fields, so this is done here.
     * Saved filters lose the hidden fields, because the filter editor cannot offer them. Safe to run again.
     *
     * @param int $tabId Tab id of the module
     * @return void
     */
    private function convertAmountField(int $tabId): void
    {
        $this->db->pquery(
            'UPDATE vtiger_field SET uitype = ? WHERE tabid = ? AND fieldname = ?',
            [Vtiger_Field_Model::UITYPE_AMOUNT_CURRENCY, $tabId, IncomingPayment_Config_Helper::FIELD_AMOUNT]
        );

        foreach (self::AMOUNT_HIDDEN_FIELDS as $fieldName) {
            $this->db->pquery(
                'UPDATE vtiger_field SET displaytype = 3, quickcreate = 1, masseditable = 0, summaryfield = 0, headerfield = 0 WHERE tabid = ? AND fieldname = ?',
                [$tabId, $fieldName]
            );

            foreach (self::FILTER_TABLES as $filterTable) {
                $this->db->pquery(
                    sprintf('DELETE FROM %s WHERE columnname LIKE ?', $filterTable),
                    [$this->getFilterColumnPattern($fieldName, $fieldName)]
                );
            }
        }
    }

    /**
     * Renames the field in the columns and conditions of saved filters, also of the Payments list, whose filters
     * use the columns of this module.
     *
     * @param string $legacyColumn Former column name
     * @param string $legacyName   Former field name
     * @param string $fieldName    Current field and column name
     * @return void
     */
    private function renameFilterColumns(string $legacyColumn, string $legacyName, string $fieldName): void
    {
        $legacyPrefix = IncomingPayment_Config_Helper::TABLE . ':' . $legacyColumn . ':' . $legacyName . ':';
        $prefix = IncomingPayment_Config_Helper::TABLE . ':' . $fieldName . ':' . $fieldName . ':';

        foreach (self::FILTER_TABLES as $filterTable) {
            $this->db->pquery(
                sprintf('UPDATE %s SET columnname = CONCAT(?, SUBSTRING(columnname, ?)) WHERE columnname LIKE ?', $filterTable),
                [$prefix, strlen($legacyPrefix) + 1, $this->getFilterColumnPattern($legacyColumn, $legacyName)]
            );
        }
    }

    /**
     * Replaces the field names of earlier installations (LEGACY_FIELD_NAMES) in the related list and popup settings
     * of this module, which keep the names as text. Fields renamed before this step existed kept their old names
     * there, so the related list silently dropped e.g. the payment date and the amount. Safe to run again.
     *
     * @return void
     */
    private function renameLayoutSettingsFields(): void
    {
        $tabId = (int)getTabid($this->getModuleName());

        if (!$tabId) {
            return;
        }

        foreach (self::LAYOUT_SETTINGS_TABLES as $settingsTable => $hasSortField) {
            $row = $this->db->fetchByAssoc($this->db->pquery(sprintf('SELECT * FROM %s WHERE tabid = ?', $settingsTable), [$tabId]), -1, false);

            if (!$row) {
                continue;
            }

            $columns = array_map([$this, 'getCurrentFieldName'], explode(',', (string)$row['columnslist']));
            $values = ['columnslist' => implode(',', $columns)];

            if ($hasSortField) {
                $values['sortfield'] = $this->getCurrentFieldName((string)$row['sortfield']);
            }

            $this->db->pquery(
                sprintf('UPDATE %s SET %s = ? WHERE tabid = ?', $settingsTable, implode(' = ?, ', array_keys($values))),
                array_merge(array_values($values), [$tabId])
            );
        }
    }

    /**
     * Returns the current name of a field: the new name of a field of an earlier installation, otherwise the name.
     *
     * @param string $fieldName Field name
     * @return string
     */
    private function getCurrentFieldName(string $fieldName): string
    {
        return self::LEGACY_FIELD_NAMES[$fieldName] ?? $fieldName;
    }

    /**
     * Returns the LIKE pattern of the saved filter columns of a field of this module.
     *
     * @param string $columnName Column name
     * @param string $fieldName  Field name
     * @return string
     */
    private function getFilterColumnPattern(string $columnName, string $fieldName): string
    {
        $prefix = IncomingPayment_Config_Helper::TABLE . ':' . $columnName . ':' . $fieldName . ':';

        return addcslashes($prefix, '\\%_') . '%';
    }

    /**
     * Renames the field in the conditions of the workflows of this module, including conditions on a field of a
     * referenced record, e.g. "(relatedto : (Accounts) accountname)".
     *
     * @param string $legacyName Former field name
     * @param string $fieldName  Current field name
     * @return void
     */
    private function renameWorkflowConditions(string $legacyName, string $fieldName): void
    {
        $result = $this->db->pquery('SELECT workflow_id, test FROM com_vtiger_workflows WHERE module_name = ?', [$this->getModuleName()]);
        $referencePrefix = '(' . $legacyName . ' : (';

        while ($row = $this->db->fetchByAssoc($result, -1, false)) {
            $conditions = json_decode(decode_html((string)$row['test']), true);
            $isChanged = false;

            if (!is_array($conditions)) {
                continue;
            }

            foreach ($conditions as $index => $condition) {
                $conditionField = (string)($condition['fieldname'] ?? '');

                if ($legacyName === $conditionField) {
                    $conditions[$index]['fieldname'] = $fieldName;
                    $isChanged = true;
                } elseif (str_starts_with($conditionField, $referencePrefix)) {
                    $conditions[$index]['fieldname'] = '(' . $fieldName . substr($conditionField, strlen($legacyName) + 1);
                    $isChanged = true;
                }
            }

            if ($isChanged) {
                $this->db->pquery('UPDATE com_vtiger_workflows SET test = ? WHERE workflow_id = ?', [json_encode($conditions), (int)$row['workflow_id']]);
            }
        }
    }

    /**
     * Renames the picklist table of a renamed picklist field with its sequence table and picklist name, so the values,
     * their order, colors and role assignments stay. A picklist also used by a field of another module stays as it is.
     *
     * @param int    $tabId      Tab id of the module
     * @param string $legacyName Former field name
     * @param string $fieldName  Current field name
     * @return void
     * @throws Exception
     */
    private function renamePicklistTable(int $tabId, string $legacyName, string $fieldName): void
    {
        $legacyTable = 'vtiger_' . $legacyName;
        $tableName = 'vtiger_' . $fieldName;

        if (!Vtiger_Utils::CheckTable($legacyTable) || Vtiger_Utils::CheckTable($tableName) || $this->isPicklistUsedByOtherModule($tabId, $legacyName)) {
            self::logError('Picklist table not renamed: ' . $legacyTable);

            return;
        }

        $this->db->pquery(sprintf('RENAME TABLE %s TO %s', $legacyTable, $tableName), []);
        $this->getTable($tableName, null)
            ->renameColumn($legacyName . 'id', $fieldName . 'id')
            ->renameColumn($legacyName, $fieldName);

        if (Vtiger_Utils::CheckTable($legacyTable . '_seq') && !Vtiger_Utils::CheckTable($tableName . '_seq')) {
            $this->db->pquery(sprintf('RENAME TABLE %s_seq TO %s_seq', $legacyTable, $tableName), []);
        }

        $this->db->pquery('UPDATE vtiger_picklist SET name = ? WHERE name = ?', [$fieldName, $legacyName]);
    }

    /**
     * Returns the id, name and column of a field of this module, or an empty array.
     *
     * @param int    $tabId     Tab id of the module
     * @param string $fieldName Field name
     * @return array
     */
    private function fetchFieldRow(int $tabId, string $fieldName): array
    {
        $result = $this->db->pquery('SELECT fieldid, fieldname, columnname FROM vtiger_field WHERE tabid = ? AND fieldname = ?', [$tabId, $fieldName]);

        return $this->db->num_rows($result) ? (array)$this->db->fetchByAssoc($result) : [];
    }

    /**
     * Removes the former module cron; statuses are updated by the core cron PaymentStatus (Core_PaymentStatus_Model).
     *
     * @return void
     */
    private function migrateStatusCron(): void
    {
        Vtiger_Cron::deregister(self::LEGACY_STATUS_CRON);
    }

    /**
     * Drops the index of the former int reference column, because a text column cannot keep an index without a length.
     * Runs before installTables() changes the column type.
     *
     * @return void
     */
    private function dropLegacyRelationIndex(): void
    {
        $table = $this->getTable(IncomingPayment_Config_Helper::TABLE, null);

        if ($table->checkColumn(self::LEGACY_RELATION_COLUMN, IncomingPayment_Config_Helper::TABLE) && $table->isKeyExists(self::LEGACY_RELATION_INDEX)) {
            $table->getDB()->pquery(sprintf('ALTER TABLE %s DROP INDEX %s', IncomingPayment_Config_Helper::TABLE, self::LEGACY_RELATION_INDEX), []);
        }
    }

    /**
     * Turns the former single reference field (uitype 10) into the multi reference field (uitype 34) and stores
     * empty references kept as 0 by the former int column as NULL. Both steps are idempotent.
     *
     * @return void
     * @throws Exception
     */
    private function migrateRelationField(): void
    {
        $moduleInstance = Vtiger_Module::getInstance($this->getModuleName());
        $field = $moduleInstance ? Vtiger_Field::getInstance(IncomingPayment_Config_Helper::FIELD_RELATION, $moduleInstance) : false;

        if (!$field) {
            return;
        }

        if (Vtiger_Field_Model::UITYPE_MULTI_REFERENCE !== (int)$field->uitype) {
            $field->getFieldTable()->updateData(['uitype' => Vtiger_Field_Model::UITYPE_MULTI_REFERENCE], ['fieldid' => $field->id]);
        }

        $this->getTable(IncomingPayment_Config_Helper::TABLE, null)
            ->updateData([IncomingPayment_Config_Helper::FIELD_RELATION => null], [IncomingPayment_Config_Helper::FIELD_RELATION => '0']);
    }

    /**
     * Moves the fields out of the legacy accounting block and deletes the block. A module update keeps the block
     * and sequence of existing fields, so this is done here. It runs only while the legacy block exists, so later
     * migrations do not overwrite a field order set in the Layout Editor.
     *
     * @return void
     * @throws Exception
     */
    private function migrateAccountingBlock(): void
    {
        $moduleInstance = Vtiger_Module::getInstance($this->getModuleName());

        // The system migration calls migrate() of every module on disk, also of one that is not installed.
        if (!$moduleInstance) {
            return;
        }

        $legacyBlock = Vtiger_Block::getInstance(self::LEGACY_ACCOUNTING_BLOCK, $moduleInstance);
        $mainBlock = Vtiger_Block::getInstance(IncomingPayment_Config_Helper::MAIN_BLOCK, $moduleInstance);

        if (!$legacyBlock || !$mainBlock) {
            return;
        }

        $sequence = 0;

        foreach ($this->getBlocks()[IncomingPayment_Config_Helper::MAIN_BLOCK] as $fieldName => $fieldParams) {
            $sequence++;
            $field = Vtiger_Field::getInstance($fieldName, $moduleInstance);

            if ($field) {
                $field->getFieldTable()->updateData(
                    [
                        'block' => $mainBlock->id,
                        'sequence' => $sequence,
                        'displaytype' => $fieldParams['displaytype'] ?? 1,
                        'masseditable' => $fieldParams['masseditable'] ?? 1,
                    ],
                    [
                        'fieldid' => $field->id,
                    ],
                );
            }
        }

        $legacyBlock->delete(false);
    }

    /**
     * Replaces the final status Received of earlier installations with Paid, the final status shared by all payment
     * modules: the payments, the history and the conditions of saved filters and workflows, and last the picklist
     * value, renamed in place so its id, order, color and role assignments stay. Runs only while Received is in the
     * picklist, so a repeated run does nothing and an interrupted one is completed by the next run.
     *
     * @return void
     */
    private function migratePaidStatus(): void
    {
        $statusField = IncomingPayment_Config_Helper::FIELD_STATUS;
        $picklistTable = 'vtiger_' . $statusField;
        $paidStatus = IncomingPayment_Config_Helper::STATUS_PAID;

        if (!getTabid($this->getModuleName()) || !Vtiger_Utils::CheckTable($picklistTable) || !$this->hasStatusValue(self::LEGACY_PAID_STATUS)) {
            return;
        }

        $this->db->pquery(
            sprintf('UPDATE %s SET %s = ? WHERE %s = ?', IncomingPayment_Config_Helper::TABLE, $statusField, $statusField),
            [$paidStatus, self::LEGACY_PAID_STATUS]
        );

        foreach (['prevalue', 'postvalue'] as $column) {
            $this->db->pquery(
                sprintf(
                    'UPDATE vtiger_modtracker_detail
                    INNER JOIN vtiger_modtracker_basic ON vtiger_modtracker_basic.id = vtiger_modtracker_detail.id
                    SET vtiger_modtracker_detail.%1$s = ?
                    WHERE vtiger_modtracker_basic.module = ? AND vtiger_modtracker_detail.fieldname = ? AND vtiger_modtracker_detail.%1$s = ?',
                    $column
                ),
                [$paidStatus, $this->getModuleName(), $statusField, self::LEGACY_PAID_STATUS]
            );
        }

        $this->migratePaidStatusFilters();
        $this->migratePaidStatusWorkflows();

        // The picklist value is changed last: the check above runs again until every step is done.
        if ($this->hasStatusValue($paidStatus)) {
            $this->db->pquery(sprintf('DELETE FROM %s WHERE %s = ?', $picklistTable, $statusField), [self::LEGACY_PAID_STATUS]);
        } else {
            $this->db->pquery(sprintf('UPDATE %s SET %s = ? WHERE %s = ?', $picklistTable, $statusField, $statusField), [$paidStatus, self::LEGACY_PAID_STATUS]);
        }

        Vtiger_Cache::flushPicklistCache($statusField);
    }

    /**
     * Tells whether the payment status picklist has a value.
     *
     * @param string $value Picklist value
     * @return bool
     */
    private function hasStatusValue(string $value): bool
    {
        $statusField = IncomingPayment_Config_Helper::FIELD_STATUS;
        $result = $this->db->pquery(sprintf('SELECT 1 FROM vtiger_%s WHERE %s = ? LIMIT 1', $statusField, $statusField), [$value]);

        return 0 < $this->db->num_rows($result);
    }

    /**
     * Replaces the legacy final status in the conditions of saved filters on the payment status, also of the
     * Payments list, whose filters use the columns of this module. Values are comma separated.
     *
     * @return void
     */
    private function migratePaidStatusFilters(): void
    {
        $statusField = IncomingPayment_Config_Helper::FIELD_STATUS;
        $result = $this->db->pquery(
            'SELECT cvid, columnindex, value FROM vtiger_cvadvfilter WHERE columnname LIKE ?',
            [$this->getFilterColumnPattern($statusField, $statusField)]
        );

        while ($row = $this->db->fetchByAssoc($result, -1, false)) {
            $values = explode(',', (string)$row['value']);
            $newValues = $this->replacePaidStatus($values);

            if ($newValues !== $values) {
                $this->db->pquery(
                    'UPDATE vtiger_cvadvfilter SET value = ? WHERE cvid = ? AND columnindex = ?',
                    [implode(',', $newValues), (int)$row['cvid'], (int)$row['columnindex']]
                );
            }
        }
    }

    /**
     * Replaces the legacy final status in the workflow conditions on the payment status.
     *
     * @return void
     */
    private function migratePaidStatusWorkflows(): void
    {
        $statusField = IncomingPayment_Config_Helper::FIELD_STATUS;
        $result = $this->db->pquery('SELECT workflow_id, test FROM com_vtiger_workflows WHERE module_name = ?', [$this->getModuleName()]);

        while ($row = $this->db->fetchByAssoc($result, -1, false)) {
            $conditions = json_decode(decode_html((string)$row['test']), true);
            $isChanged = false;

            if (!is_array($conditions)) {
                continue;
            }

            foreach ($conditions as $index => $condition) {
                if ($statusField !== ($condition['fieldname'] ?? '') || !isset($condition['value'])) {
                    continue;
                }

                $values = explode(',', (string)$condition['value']);
                $newValues = $this->replacePaidStatus($values);

                if ($newValues !== $values) {
                    $conditions[$index]['value'] = implode(',', $newValues);
                    $isChanged = true;
                }
            }

            if ($isChanged) {
                $this->db->pquery('UPDATE com_vtiger_workflows SET test = ? WHERE workflow_id = ?', [json_encode($conditions), (int)$row['workflow_id']]);
            }
        }
    }

    /**
     * Replaces the legacy final status by the shared one in a list of status values.
     *
     * @param string[] $values Status values
     * @return string[]
     */
    private function replacePaidStatus(array $values): array
    {
        foreach ($values as $index => $value) {
            if (self::LEGACY_PAID_STATUS === trim($value)) {
                $values[$index] = IncomingPayment_Config_Helper::STATUS_PAID;
            }
        }

        return $values;
    }
}
