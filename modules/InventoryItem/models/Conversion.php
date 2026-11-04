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
 * Describes how the inventory items of a source record are converted into a target record (for example Invoice -> CreditNote).
 * Installable modules register their conversions through registerConversion(); administrators then tune them.
 */
class InventoryItem_Conversion_Model extends Core_DatabaseData_Model
{
    public const DEFAULT_COLUMNS = ['quantity', 'price'];
    public const SUPPORTED_COLUMNS = ['quantity', 'price'];
    /**
     * Conversions between the standard inventory modules. They keep the plain create link until an administrator enables the item review modal.
     * Installable modules register their own conversions with registerConversion().
     */
    public const DEFAULT_CONVERSIONS = [
        ['Quotes', 'Invoice'],
        ['Quotes', 'SalesOrder'],
        ['Quotes', 'PurchaseOrder'],
        ['SalesOrder', 'Invoice'],
        ['SalesOrder', 'PurchaseOrder'],
        ['Invoice', 'PurchaseOrder'],
    ];
    /**
     * Header fields which depend on the copied items or on the currency of the items and are never edited in the modal.
     */
    protected const NOT_SELECTABLE_HEADER_FIELDS = [
        'overall_discount', 'margin', 'margin_combined', 'adjustment', 'grand_total', 'currency_id', 'conversion_rate', 'region_id', 'pricebookid', 'taxtype',
    ];
    protected const SESSION_KEY = 'inventoryitem_conversion';

    protected string $table = 'df_inventoryitem_conversions';
    protected string $tableId = 'conversion_id';

    /**
     * @return self
     */
    public function getConversionsTable(): self
    {
        return $this->getTable($this->table, $this->tableId);
    }

    /**
     * @throws Exception
     */
    public function createTables(): void
    {
        $this->getConversionsTable()
            ->createTable()
            ->createColumn('source_tabid', 'int(19) NOT NULL')
            ->createColumn('target_tabid', 'int(19) NOT NULL')
            ->createColumn('enabled', 'tinyint(1) NOT NULL DEFAULT 1')
            ->createColumn('use_modal', 'tinyint(1) NOT NULL DEFAULT 1')
            ->createColumn('columns', 'varchar(500) DEFAULT NULL')
            ->createColumn('header_fields', 'varchar(500) DEFAULT NULL')
            ->createKey('UNIQUE INDEX IF NOT EXISTS `conversion_modules_idx` (`source_tabid`, `target_tabid`)')
            ->createKey('CONSTRAINT `fk_1_df_inventoryitem_conversions` FOREIGN KEY IF NOT EXISTS (`source_tabid`) REFERENCES `vtiger_tab` (`tabid`) ON DELETE CASCADE')
            ->createKey('CONSTRAINT `fk_2_df_inventoryitem_conversions` FOREIGN KEY IF NOT EXISTS (`target_tabid`) REFERENCES `vtiger_tab` (`tabid`) ON DELETE CASCADE');
    }

    /**
     * Creates the table when it is missing and registers the standard conversions.
     *
     * @return void
     * @throws Exception
     */
    public static function registerDefaultConversions(): void
    {
        (new self())->createTables();

        foreach (self::DEFAULT_CONVERSIONS as [$sourceModule, $targetModule]) {
            self::registerConversion($sourceModule, $targetModule);
        }
    }

    /**
     * Registers the conversion when it does not exist yet. Existing settings are never overwritten, so a module update keeps the administrator's choices.
     *
     * @param string $sourceModule
     * @param string $targetModule
     * @param array  $options enabled (bool), use_modal (bool), columns (string[] item fields), header_fields (string[] fields of the target module)
     *
     * @return void
     * @throws Exception
     */
    public static function registerConversion(string $sourceModule, string $targetModule, array $options = []): void
    {
        $sourceTabId = getTabId($sourceModule);
        $targetTabId = getTabId($targetModule);

        if (!$sourceTabId || !$targetTabId) {
            throw new Exception('Unknown module in conversion: ' . $sourceModule . ' -> ' . $targetModule);
        }

        $table = (new self())->getConversionsTable();
        $table->retrieveIdByParams(['source_tabid' => $sourceTabId, 'target_tabid' => $targetTabId]);

        if ($table->getId()) {
            return;
        }

        $table->insertData([
            'source_tabid' => $sourceTabId,
            'target_tabid' => $targetTabId,
            'enabled'      => (int)($options['enabled'] ?? true),
            'use_modal'    => (int)($options['use_modal'] ?? true),
            'columns'      => implode(',', $options['columns'] ?? self::DEFAULT_COLUMNS),
            'header_fields' => implode(',', $options['header_fields'] ?? []),
        ]);
    }

    /**
     * Removes registered conversions. At least one module has to be defined.
     *
     * @param string $sourceModule
     * @param string $targetModule
     *
     * @return void
     * @throws Exception
     */
    public static function deregisterConversion(string $sourceModule = '', string $targetModule = ''): void
    {
        $search = [];

        if ($sourceModule) {
            $search['source_tabid'] = getTabId($sourceModule);
        }

        if ($targetModule) {
            $search['target_tabid'] = getTabId($targetModule);
        }

        if (empty($search) || in_array(false, $search, true) || in_array(0, $search, true)) {
            return;
        }

        (new self())->getConversionsTable()->deleteData($search);
    }

    /**
     * @param string $sourceModule
     * @param string $targetModule
     *
     * @return self|null null when the conversion is not registered
     * @throws Exception
     */
    public static function getConversion(string $sourceModule, string $targetModule): ?self
    {
        $sourceTabId = getTabId($sourceModule);
        $targetTabId = getTabId($targetModule);

        // The table is created by the module update; until then all conversions behave as plain links.
        if (!$sourceTabId || !$targetTabId || !Vtiger_Utils::CheckTable('df_inventoryitem_conversions')) {
            return null;
        }

        $data = (new self())->getConversionsTable()->selectData([], ['source_tabid' => $sourceTabId, 'target_tabid' => $targetTabId]);

        if (empty($data)) {
            return null;
        }

        $instance = new self();
        $instance->setData($data);
        $instance->set('source_module', $sourceModule);
        $instance->set('target_module', $targetModule);

        return $instance;
    }

    /**
     * Checks that the modal conversion is allowed for the record and the current user.
     *
     * @param int $sourceRecord
     *
     * @return void
     * @throws Exception
     */
    public function checkAccess(int $sourceRecord): void
    {
        $sourceModule = (string)$this->get('source_module');
        $targetModule = (string)$this->get('target_module');

        if (
            !$this->isModalEnabled()
            || getSalesEntityType($sourceRecord) !== $sourceModule
            || !Users_Privileges_Model::isPermitted($sourceModule, 'DetailView', $sourceRecord)
            || !Users_Privileges_Model::isPermitted($targetModule, 'CreateView')
        ) {
            throw new Exception(vtranslate('LBL_PERMISSION_DENIED'));
        }

        // A record rule may deny converting this record (Core_RecordActionPermission_Model)
        $denyReason = Core_RecordActionPermission_Model::getInstance()->getDenyReason($sourceRecord, Core_RecordActionPermission_Model::ACTION_CONVERT);

        if (null !== $denyReason) {
            throw new Exception($denyReason);
        }
    }

    /**
     * Validates the rows posted from the conversion modal. A row that is not posted or is marked as removed is not converted.
     *
     * @param int   $sourceRecord
     * @param array $postedItems [inventoryitemid => ['remove' => 1] | ['quantity' => '1', 'price' => '2.5']]
     *
     * @return array [inventoryitemid => [column => float]] of the rows which are converted
     * @throws Exception
     */
    public function validateItems(int $sourceRecord, array $postedItems): array
    {
        $editableColumns = array_intersect(self::SUPPORTED_COLUMNS, $this->getEditableColumns());
        $sourceItems = [];

        foreach (InventoryItem_Module_Model::fetchItemsForId($sourceRecord) as $sourceItem) {
            $sourceItems[(int)$sourceItem['inventoryitemid']] = $sourceItem;
        }

        $items = [];

        foreach ($postedItems as $itemId => $values) {
            $itemId = (int)$itemId;

            if (!isset($sourceItems[$itemId]) || !is_array($values)) {
                throw new Exception(vtranslate('LBL_CONVERSION_INVALID_ITEM', 'InventoryItem'));
            }

            if (!empty($values['remove'])) {
                continue;
            }

            foreach ($editableColumns as $column) {
                $value = str_replace(',', '.', trim((string)($values[$column] ?? $sourceItems[$itemId][$column])));

                if (!is_numeric($value)) {
                    throw new Exception(vtranslate('LBL_CONVERSION_INVALID_NUMBER', 'InventoryItem'));
                }

                $items[$itemId][$column] = (float)$value;
            }

            $items[$itemId] ??= [];
        }

        if (empty($items)) {
            throw new Exception(vtranslate('LBL_CONVERSION_NO_ITEMS', 'InventoryItem'));
        }

        return $items;
    }

    /**
     * Keeps the validated rows until the target record is created.
     *
     * @param string $targetModule
     * @param int    $sourceRecord
     * @param array  $items
     *
     * @return void
     */
    public static function storeItems(string $targetModule, int $sourceRecord, array $items): void
    {
        $_SESSION[self::SESSION_KEY][$targetModule][$sourceRecord] = $items;
    }

    /**
     * @param string $targetModule
     * @param int    $sourceRecord
     *
     * @return array|null null when the user did not review the items in the modal
     */
    public static function getStoredItems(string $targetModule, int $sourceRecord): ?array
    {
        return $_SESSION[self::SESSION_KEY][$targetModule][$sourceRecord] ?? null;
    }

    /**
     * @param string $targetModule
     * @param int    $sourceRecord
     *
     * @return void
     */
    public static function clearStoredItems(string $targetModule, int $sourceRecord): void
    {
        unset($_SESSION[self::SESSION_KEY][$targetModule][$sourceRecord]);
    }

    /**
     * @return self[] all conversions between active modules, ordered by the module labels
     * @throws Exception
     */
    public static function getAllConversions(): array
    {
        if (!Vtiger_Utils::CheckTable('df_inventoryitem_conversions')) {
            return [];
        }

        $table = (new self())->getConversionsTable();
        $result = $table->selectResult([], []);
        $conversions = [];

        while ($row = $table->getDB()->fetchByAssoc($result)) {
            $sourceModule = vtlib_getModuleNameById($row['source_tabid']);
            $targetModule = vtlib_getModuleNameById($row['target_tabid']);

            if (!vtlib_isModuleActive($sourceModule) || !vtlib_isModuleActive($targetModule)) {
                continue;
            }

            $conversion = new self();
            $conversion->setData($row);
            $conversion->set('source_module', $sourceModule);
            $conversion->set('target_module', $targetModule);
            $conversions[] = $conversion;
        }

        return $conversions;
    }

    /**
     * @param int $conversionId
     *
     * @return self|null null when the conversion does not exist or one of its modules is not active
     * @throws Exception
     */
    public static function getConversionById(int $conversionId): ?self
    {
        foreach (self::getAllConversions() as $conversion) {
            if ($conversion->getId() === $conversionId) {
                return $conversion;
            }
        }

        return null;
    }

    /**
     * Fields of the target module which an administrator can offer for editing in the modal.
     *
     * @param string $targetModule
     *
     * @return Vtiger_Field_Model[] indexed by the field name
     * @throws Exception
     */
    public static function getSelectableHeaderFields(string $targetModule): array
    {
        $fields = [];

        foreach (Vtiger_Module_Model::getInstance($targetModule)->getFields() as $fieldName => $fieldModel) {
            if (self::isSelectableHeaderField($fieldModel)) {
                $fields[$fieldName] = $fieldModel;
            }
        }

        return $fields;
    }

    /**
     * Header fields which depend on the copied items or on the currency of the items are never edited in the modal and never mapped.
     *
     * @param string $fieldName
     *
     * @return bool
     */
    public static function isCalculatedHeaderField(string $fieldName): bool
    {
        return in_array($fieldName, array_merge(self::NOT_SELECTABLE_HEADER_FIELDS, InventoryItem_Field_Model::totalFields), true);
    }

    /**
     * Simple editable fields can be edited in the modal. Reference, text and special fields need their own edit scripts and are not offered.
     *
     * @param Vtiger_Field_Model $fieldModel
     *
     * @return bool
     */
    public static function isSelectableHeaderField(Vtiger_Field_Model $fieldModel): bool
    {
        return $fieldModel->isEditable()
            && 1 === (int)$fieldModel->get('displaytype')
            && !$fieldModel->isReferenceField()
            && !self::isCalculatedHeaderField($fieldModel->getName())
            && !in_array((int)$fieldModel->get('uitype'), [28, 30, 53, 56, 69, 83], true)
            && !in_array($fieldModel->getFieldDataType(), ['text', 'multireference'], true);
    }

    /**
     * Mandatory fields of the target module are always offered in the modal, so the record can be saved without opening its edit view.
     * Item totals, special and file fields are never offered.
     *
     * @param Vtiger_Field_Model $fieldModel
     *
     * @return bool
     */
    public static function isMandatoryModalField(Vtiger_Field_Model $fieldModel): bool
    {
        return $fieldModel->isEditable()
            && $fieldModel->isMandatory()
            && 1 === (int)$fieldModel->get('displaytype')
            && !self::isCalculatedHeaderField($fieldModel->getName())
            && !in_array((int)$fieldModel->get('uitype'), [28, 69, 83], true);
    }

    /**
     * Saves the settings of the conversion. Unsupported columns and fields are dropped.
     *
     * @param array $settings use_modal, columns (string[]), header_fields (string[])
     *
     * @return void
     * @throws Exception
     */
    public function saveSettings(array $settings): void
    {
        $columns = array_values(array_intersect((array)($settings['columns'] ?? []), self::SUPPORTED_COLUMNS));
        $headerFields = array_values(array_intersect((array)($settings['header_fields'] ?? []), array_keys(self::getSelectableHeaderFields((string)$this->get('target_module')))));

        $this->getConversionsTable()->updateData([
            'use_modal'     => (int)!empty($settings['use_modal']),
            'columns'       => implode(',', $columns),
            'header_fields' => implode(',', $headerFields),
        ], ['conversion_id' => $this->getId()]);
    }

    /**
     * Decides the url of the conversion link in the detail view of the source record.
     * Conversions which are not registered keep their plain create url.
     *
     * @param string $sourceModule
     * @param string $targetModule
     * @param int    $sourceRecord
     * @param string $createUrl create view url of the target record
     *
     * @return string|false false when the action is disabled or denied for the record and the link must not be displayed
     * @throws Exception
     */
    public static function getLinkUrl(string $sourceModule, string $targetModule, int $sourceRecord, string $createUrl): string|false
    {
        // A record rule may deny converting this record (Core_RecordActionPermission_Model); the save checks it as well
        if (!Core_RecordActionPermission_Model::getInstance()->isActionPermitted($sourceRecord, Core_RecordActionPermission_Model::ACTION_CONVERT)) {
            return false;
        }

        $conversion = self::getConversion($sourceModule, $targetModule);

        if (empty($conversion)) {
            return $createUrl;
        }

        if (!$conversion->isActionEnabled()) {
            return false;
        }

        if (!$conversion->isModalEnabled()) {
            return $createUrl;
        }

        $flags = JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG;

        return sprintf(
            'javascript:InventoryItem_Conversion_Js.showModal(%s,%s,%d)',
            json_encode($sourceModule, $flags),
            json_encode($targetModule, $flags),
            $sourceRecord,
        );
    }

    /**
     * Returns whether the conversion action should be displayed.
     *
     * @return bool
     */
    public function isActionEnabled(): bool
    {
        return (bool)$this->get('enabled');
    }

    /**
     * Returns whether the user has to review the items in a modal before the target record is created.
     *
     * @return bool
     */
    public function isModalEnabled(): bool
    {
        return $this->isActionEnabled() && (bool)$this->get('use_modal');
    }

    /**
     * @return string[] fields of the target module the user can change in the modal
     */
    public function getHeaderFields(): array
    {
        return array_values(array_filter(explode(',', (string)$this->get('header_fields'))));
    }

    /**
     * @return string[] item fields the user can change in the modal
     */
    public function getEditableColumns(): array
    {
        $columns = array_filter(explode(',', (string)$this->get('columns')));

        return empty($columns) ? self::DEFAULT_COLUMNS : array_values($columns);
    }
}
