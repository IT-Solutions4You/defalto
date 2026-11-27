<?php
/** Defalto CRM. Licensed under the GNU AGPL v3 License. */
class Core_Checklist_Model extends Core_DatabaseData_Model
{
    public const UI_TYPE = 34;
    protected string $table = 'df_checklist';
    protected string $tableId = 'id';

    public static function getInstance(): self
    {
        return new self();
    }

    public function getSummaryWidgetField(Vtiger_Module_Model $module, int|string $fieldIdentifier): ?Vtiger_Field_Model
    {
        $field = ctype_digit((string)$fieldIdentifier)
            ? ($module->getFieldsById()[(int)$fieldIdentifier] ?? null)
            : $module->getField((string)$fieldIdentifier);

        return $field && (int)$field->get('uitype') === self::UI_TYPE && $field->isViewableInDetailView()
            ? $field : null;
    }

    public function getChecklistTable(): self
    {
        return $this->getTable($this->table, $this->tableId);
    }

    public function createTables(): void
    {
        $this->getChecklistTable()->createTable()
            ->createColumn('record_id', 'INT(19) NOT NULL')
            ->createColumn('field_id', 'INT(19) NOT NULL')
            ->createKey('UNIQUE KEY IF NOT EXISTS `record_field_unique` (`record_id`, `field_id`)')
            ->createKey('KEY IF NOT EXISTS `record_field_idx` (`record_id`, `field_id`)')
            ->createKey('CONSTRAINT `fk_checklist_record` FOREIGN KEY IF NOT EXISTS (`record_id`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE')
            ->createKey('CONSTRAINT `fk_checklist_field` FOREIGN KEY IF NOT EXISTS (`field_id`) REFERENCES `vtiger_field` (`fieldid`) ON DELETE CASCADE');

        (new Vtiger_Field_Model())->saveFieldType(self::UI_TYPE, 'checklist');
        $this->retrieveDB();
        (new VTEventsManager($this->getDB()))->registerHandler(
            'vtiger.entity.aftersave.final', 'modules/Core/handlers/Checklist.php', 'Core_Checklist_Handler'
        );
    }

    public function getContext(int $recordId, int $fieldId, bool $write = false): array
    {
        $this->retrieveDB();
        $result = $this->getDB()->pquery(
            'SELECT e.setype, f.fieldname FROM vtiger_crmentity e'
            . ' INNER JOIN vtiger_tab t ON t.name=e.setype INNER JOIN vtiger_field f ON f.tabid=t.tabid'
            . ' WHERE e.crmid=? AND e.deleted=0 AND f.fieldid=? AND f.uitype=?',
            [$recordId, $fieldId, self::UI_TYPE]
        );
        $row = $this->getDB()->num_rows($result) ? $this->getDB()->query_result_rowdata($result, 0) : null;

        if (!$row || !Users_Privileges_Model::isPermitted($row['setype'], 'DetailView', $recordId)) {
            throw new Exception(vtranslate('LBL_PERMISSION_DENIED'));
        }

        $module = Vtiger_Module_Model::getInstance($row['setype']);
        $field = $module->getField($row['fieldname']);

        if (!$module->isActive() || !$field || !$field->isViewableInDetailView()) {
            throw new Exception(vtranslate('LBL_PERMISSION_DENIED'));
        }

        $editable = $this->isFieldEditable($module->getName(), $recordId, $field);

        if ($write && !$editable) {
            throw new Exception(vtranslate('LBL_PERMISSION_DENIED'));
        }

        return [
            'record_id' => $recordId, 'field_id' => $fieldId, 'editable' => $editable,
            'title' => vtranslate($field->get('label'), $module->getName()),
            'storage' => $this->getFieldStorage($module->getName(), $field),
        ];
    }

    public function isFieldEditable(string $module, int $recordId, Vtiger_Field_Model $field): bool
    {
        return $field->isEditable()
            && Users_Privileges_Model::isPermitted($module, 'EditView', $recordId)
            && !Core_Readonly_Model::isReadonly($module, $recordId)
            && null === Core_RecordActionPermission_Model::getInstance()->getDenyReason(
                $recordId, Core_RecordActionPermission_Model::ACTION_FIELD_EDIT, $field->getName()
            );
    }

    public function isNewFieldEditable(Vtiger_Field_Model $field): bool
    {
        return $field->isEditable() && !$field->isRecordActionLocked()
            && Users_Privileges_Model::isPermitted($field->getModuleName(), 'CreateView');
    }

    /** Identifiers come only from the registered field and its entity table mapping. */
    protected function getFieldStorage(string $module, Vtiger_Field_Model $field): array
    {
        $entity = CRMEntity::getInstance($module);
        $table = $field->get('table');
        $column = $field->get('column');
        $key = $entity->tab_name_index[$table] ?? '';

        foreach ([$table, $column, $key] as $identifier) {
            if (!is_string($identifier) || !preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/D', $identifier)) {
                throw new Exception(vtranslate('LBL_PERMISSION_DENIED'));
            }
        }

        return ['table' => $table, 'column' => $column, 'key' => $key];
    }

    protected function getFieldText(array $context): string
    {
        $storage = $context['storage'];
        $result = $this->getDB()->pquery(
            sprintf('SELECT `%s` AS checklist_text FROM `%s` WHERE `%s`=?', $storage['column'], $storage['table'], $storage['key']),
            [$context['record_id']]
        );
        $row = $this->getDB()->fetchByAssoc($result, -1, false);

        if (!$row) {
            throw new Exception(vtranslate('LBL_PERMISSION_DENIED'));
        }

        return (string)$row['checklist_text'];
    }

    protected function updateFieldText(array $context, string $text): void
    {
        $storage = $context['storage'];
        $this->getDB()->pquery(
            sprintf('UPDATE `%s` SET `%s`=? WHERE `%s`=?', $storage['table'], $storage['column'], $storage['key']),
            [$text, $context['record_id']]
        );
    }

    public function getLines(string $text): array
    {
        $lines = array_map('trim', preg_split('/\\r\\n|\\r|\\n/', $text));

        return array_values(array_filter($lines, static fn(string $line): bool => $line !== ''));
    }

    public function getChecklist(int $recordId, int $fieldId): array
    {
        $context = $this->getContext($recordId, $fieldId);

        return $this->getChecklistData($context);
    }

    protected function getChecklistData(array $context): array
    {
        $text = $this->getFieldText($context);
        $row = $this->getChecklistTable()->selectData(['id'], ['record_id' => $context['record_id'], 'field_id' => $context['field_id']]);
        $items = Core_ChecklistItem_Model::getInstance()->getItems((int)($row['id'] ?? 0), $this->getLines($text));
        $completed = count(array_filter($items, static fn(array $item): bool => $item['checked']));

        return [
            'id' => (int)($row['id'] ?? 0), 'title' => $context['title'], 'items' => $items,
            'revision' => hash('sha256', $text), 'completed' => $completed, 'total' => count($items),
            'percent' => $items ? (int)round(100 * $completed / count($items)) : 0,
        ];
    }

    /** Summarize already authorized projected records and visible Checklist fields without writes. */
    public function getRecordSummaries(array $records, array $fields): array
    {
        if (!$records || !$fields) {
            return [];
        }

        $table = $this->getChecklistTable();
        $result = $table->selectResult([], [
            'record_id' => array_keys($records),
            'field_id' => array_map(static fn(Vtiger_Field_Model $field): int => (int)$field->getId(), array_values($fields)),
        ]);
        $checklists = [];
        $checklistIds = [];

        while ($row = $table->getDB()->fetchByAssoc($result, -1, false)) {
            $checklists[(int)$row['record_id']][(int)$row['field_id']] = (int)$row['id'];
            $checklistIds[] = (int)$row['id'];
        }

        $itemModel = Core_ChecklistItem_Model::getInstance();
        $metadata = $itemModel->getChecklistMetadata($checklistIds);
        $summaries = [];

        foreach ($records as $id => $values) {
            $summaries[$id] = ['completed' => 0, 'total' => 0];

            foreach ($fields as $name => $field) {
                $checklistId = $checklists[$id][(int)$field->getId()] ?? 0;
                $items = $itemModel->getItemsFromMetadata(
                    $this->getLines((string)($values[$name] ?? '')), $metadata[$checklistId] ?? []
                );
                $summaries[$id]['total'] += count($items);
                $summaries[$id]['completed'] += count(array_filter($items, static fn(array $item): bool => $item['checked']));
            }
        }

        return $summaries;
    }

    public function validateText(mixed $text): string
    {
        if (!is_string($text) || trim($text) === '' || strpbrk($text, "\r\n") !== false || mb_strlen(trim($text), 'UTF-8') > 1000) {
            throw new Exception(vtranslate('LBL_CHECKLIST_INVALID_TEXT', 'Core'));
        }

        return trim($text);
    }

    public function validateFieldLength(string $text): void
    {
        if (strlen($text) > 16777215) {
            throw new Exception(vtranslate('LBL_CHECKLIST_INVALID_TEXT', 'Core'));
        }
    }

    public function getValidatedItemTexts(mixed $text): array
    {
        if (!is_string($text) || trim($text) === '') {
            throw new Exception(vtranslate('LBL_CHECKLIST_INVALID_TEXT', 'Core'));
        }

        $lines = [];

        foreach ($this->getLines($text) as $line) {
            $lines[] = $this->validateText($line);
        }

        return $lines;
    }

    public function updateChecklist(int $recordId, int $fieldId, string $operation, int $itemId, string $revision, mixed $text, mixed $checked): array
    {
        $context = $this->getContext($recordId, $fieldId, true);
        $db = $this->getDB();
        // PearDatabase::startTransaction() enables autocommit in this application.
        // Explicit SQL keeps text and metadata atomic; WebUI exposes errors and
        // closing the request connection rolls back an unfinished transaction.
        $db->pquery('START TRANSACTION');
        $db->pquery('SELECT crmid FROM vtiger_crmentity WHERE crmid=? FOR UPDATE', [$recordId]);
        $storage = $context['storage'];
        $db->pquery(sprintf('SELECT `%s` FROM `%s` WHERE `%s`=? FOR UPDATE', $storage['column'], $storage['table'], $storage['key']), [$recordId]);
        $context = $this->getContext($recordId, $fieldId, true);
        $data = $this->getChecklistData($context);

        if (!hash_equals($data['revision'], $revision)) {
            throw new Exception(vtranslate('LBL_CHECKLIST_CHANGED', 'Core'));
        }

        $items = $data['items'];
        $index = $itemId - 1;

        if ($operation === 'createItem') {
            foreach ($this->getValidatedItemTexts($text) as $line) {
                $items[] = ['text' => $line, 'checked' => false];
            }
        } else {
            if (!isset($items[$index])) {
                throw new Exception(vtranslate('LBL_PERMISSION_DENIED'));
            }

            if ($operation === 'renameItem') {
                $items[$index]['text'] = $this->validateText($text);
            } elseif ($operation === 'checkItem') {
                if (!in_array($checked, [0, 1, '0', '1'], true)) {
                    throw new Exception(vtranslate('LBL_PERMISSION_DENIED'));
                }

                $items[$index]['checked'] = (bool)$checked;
            } elseif ($operation === 'deleteItem') {
                array_splice($items, $index, 1);
            } else {
                throw new Exception(vtranslate('LBL_PERMISSION_DENIED'));
            }
        }

        $table = $this->getChecklistTable();
        $checklistId = $data['id'];

        if (!$checklistId) {
            $checklistId = (int)$table->insertData(['record_id' => $recordId, 'field_id' => $fieldId]);
        }

        if ($operation !== 'checkItem') {
            $fieldText = implode("\n", array_column($items, 'text'));
            $this->validateFieldLength($fieldText);
            $this->updateFieldText($context, $fieldText);
        }

        Core_ChecklistItem_Model::getInstance()->saveItems($checklistId, $items);
        $result = $this->getChecklistData($context);
        $db->pquery('COMMIT');

        return $result;
    }

    /** Reconcile ordinary form/import/workflow saves without writing text or checking interactive permissions. */
    public function saveRecordMetadata(int $recordId, string $moduleName): void
    {
        $module = Vtiger_Module_Model::getInstance($moduleName);

        foreach ($module->getFields() as $field) {
            if ((int)$field->get('uitype') !== self::UI_TYPE) {
                continue;
            }

            $context = [
                'record_id' => $recordId, 'field_id' => (int)$field->getId(),
                'title' => '', 'storage' => $this->getFieldStorage($moduleName, $field),
            ];
            $this->retrieveDB();
            $db = $this->getDB();
            $db->pquery('START TRANSACTION');
            $db->pquery('SELECT crmid FROM vtiger_crmentity WHERE crmid=? FOR UPDATE', [$recordId]);
            $storage = $context['storage'];
            $db->pquery(sprintf('SELECT `%s` FROM `%s` WHERE `%s`=? FOR UPDATE', $storage['column'], $storage['table'], $storage['key']), [$recordId]);
            $data = $this->getChecklistData($context);
            $checklistId = $data['id'];

            if (!$checklistId && $data['items']) {
                $checklistId = (int)$this->getChecklistTable()->insertData(['record_id' => $recordId, 'field_id' => $context['field_id']]);
            }

            if ($checklistId) {
                Core_ChecklistItem_Model::getInstance()->saveItems($checklistId, $data['items']);
            }

            $db->pquery('COMMIT');
        }
    }
}
