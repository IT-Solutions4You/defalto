<?php
/*
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Core_RelatedBlock_Model extends Core_DatabaseData_Model
{
    /**
     * @var string
     */
    protected string $table = 'df_related_block';
    /**
     * @var string
     */
    protected string $tableId = 'id';
    /**
     * @var string
     */
    protected string $tableName = 'name';
    /**
     * @var object
     */
    protected object $module;
    /**
     * @var object|bool
     */
    protected object|bool $related_module = false;
    protected Vtiger_Record_Model $sourceRecord;
    protected object|null $queryGenerator = null;

    /**
     * @param string $moduleName
     * @return self
     * @throws AppException
     */
    public static function getCleanInstance(string $moduleName): self
    {
        $className = Vtiger_Loader::getComponentClassName('Model', 'RelatedBlock', $moduleName);

        if (class_exists($className)) {
            $instance = new $className();
        } else {
            $instance = new self();
        }

        return $instance;
    }

    /**
     * @param string $moduleName
     * @return Core_RelatedBlock_Model
     * @throws AppException
     */
    public static function getInstance(string $moduleName): self
    {
        $instance = self::getCleanInstance($moduleName);
        $instance->set('module_name', $moduleName);

        return $instance;
    }

    /**
     * @throws AppException
     */
    public static function getInstanceById($recordId, $moduleName): self
    {
        $instance = self::getInstance($moduleName);
        $instance->setId($recordId);
        $instance->retrieveDataById();
        $instance->decodeData();

        return $instance;
    }

    /**
     * @return void
     */
    public function decodeData()
    {
        $fields = ['filters', 'related_fields', 'sorting', 'content', 'name'];

        foreach ($fields as $field) {
            $this->set($field, decode_html($this->get($field)));
        }
    }

    /**
     * @return false|object|Vtiger_Module_Model
     */
    public function getModule()
    {
        if (empty($this->module)) {
            $this->module = Vtiger_Module_Model::getInstance($this->getModuleName());
        }

        return $this->module;
    }

    /**
     * @return string
     */
    public function getModuleName(): string
    {
        return (string)$this->get('module_name');
    }

    /**
     * @return string[]
     */
    public function getRelatedFields()
    {
        return explode(';', $this->get('related_fields'));
    }

    /**
     * @param $id
     * @return array|string
     */
    public function getSort($id = null): array|string
    {
        $data = explode(';', decode_html($this->get('sorting')));

        if (is_int($id)) {
            return (string)$data[$id];
        }

        return $data;
    }

    /**
     * @throws Exception
     */
    public function getRelatedFieldsOptions(): array
    {
        $options = [];
        $moduleModel = $this->getRelatedModule();

        if (!$moduleModel) {
            return $options;
        }

        /** @var $field Vtiger_Field_Model */
        foreach ($moduleModel->getFields() as $field) {
            $options[$field->getName()] = vtranslate($field->get('label'), $field->getModuleName());
        }

        return $options;
    }

    /**
     * @return bool|object|Vtiger_Module_Model
     */
    public function getRelatedModule()
    {
        if (empty($this->related_module)) {
            $this->related_module = Vtiger_Module_Model::getInstance($this->getRelatedModuleName());
        }

        return $this->related_module;
    }

    /**
     * @return string
     */
    public function getRelatedModuleName(): string
    {
        return (string)$this->get('related_module');
    }

    /**
     * @throws Exception
     */
    public function getRelatedModuleOptions(): array
    {
        $options = [];
        $moduleModel = $this->getModule();
        $relations = Vtiger_Relation_Model::getAllRelations($moduleModel);

        foreach ($relations as $relation) {
            /**
             * @var $relation Vtiger_Relation_Model
             * @var $relationModule Vtiger_Module_Model
             */
            $relationModule = $relation->getRelationModuleModel();
            $relationModuleName = $relationModule->getName();

            $options[$relationModuleName] = vtranslate($relationModule->get('label'), $relationModuleName);
        }

        return $options;
    }

    /**
     * @return array
     * @throws Exception
     */
    public function getRelatedModules(): array
    {
        return array_keys(self::getRelatedModuleOptions());
    }

    /**
     * @return array|bool|mixed
     */
    public function getRelatedRecordStructure()
    {
        return Vtiger_RecordStructure_Model::getInstanceForModule($this->getRelatedModule())->getStructure();
    }

    /**
     * @return array
     */
    public function getRelatedModuleSortOptions()
    {
        $options = [];
        $fields = $this->getRelatedModule()->getFIelds();

        foreach ($fields as $field) {
            $options[$field->getName()] = $field->get('label');
        }

        return $options;
    }

    public function getSourceRecord(): Vtiger_Record_Model
    {
        return $this->sourceRecord;
    }

    /**
     * @param $value
     * @return bool
     */
    public function isSelectedRelatedField($value)
    {
        return in_array($value, $this->getRelatedFields());
    }

    /**
     * @param string $value
     * @return bool
     */
    public function isSelectedRelatedModule(string $value): bool
    {
        return $this->get('related_module') === $value;
    }

    /**
     * @param Vtiger_Request $request
     * @return void
     */
    public function retrieveFromRequest($request): void
    {
        $fields = [
            'related_module',
            'related_fields',
            'content',
            'name',
            'sort_by',
            'filters',
        ];

        $requestData = $request->getAll();

        foreach ($fields as $field) {
            if (array_key_exists($field, $requestData)) {
                $value = $request->get($field);

                if ('sort_by' === $field) {
                    $field = 'sorting';
                    $sort = [];
                    $sortBy = $request->get('sort_by');
                    $sortOrder = $request->get('sort_order');

                    foreach ($sortBy as $sortKey => $sortField) {
                        $sort[] = $sortField . ' ' . $sortOrder[$sortKey];
                    }

                    $value = implode(';', $sort);
                } elseif('filters' === $field) {
                    if (is_array($value)) {
                        $value = json_encode($value);
                    }
                }

                $this->set($field, $value);
            }
        }
    }

    /**
     * @return mixed|string
     */
    public function getContent()
    {
        return decode_html($this->get('content'));
    }

    /**
     * @return Core_RelatedBlock_Model
     */
    public function getRelatedBlockTable()
    {
        return $this->getTable('df_related_block', 'id');
    }

    /**
     * @return void
     * @throws AppException
     */
    public function createTables(): void
    {
        $this->getRelatedBlockTable()
            ->createTable()
            ->createColumn('name', 'VARCHAR(200)')
            ->createColumn('module', 'VARCHAR(100)')
            ->createColumn('related_module', 'VARCHAR(100)')
            ->createColumn('related_fields', 'VARCHAR(100)')
            ->createColumn('filters', 'TEXT')
            ->createColumn('sorting', 'VARCHAR(200)')
            ->createColumn('content', 'TEXT')
        ;
    }

    /**
     * @return array
     */
    public function getSaveParams(): array
    {
        return [
            'name' => $this->get('name'),
            'module' => $this->get('module_name'),
            'related_module' => $this->get('related_module'),
            'related_fields' => $this->get('related_fields'),
            'filters' => htmlentities($this->get('filters')),
            'sorting' => $this->get('sorting'),
            'content' => $this->get('content'),
        ];
    }

    /**
     * @param string $value
     * @param string $type
     * @return bool
     */
    public function isCheckedSort(string $value, string $type): bool
    {
        return str_ends_with($value, $type);
    }

    /**
     * @param string $value
     * @param string $field
     * @return bool
     */
    public function isSelectedSort(string $value, string $field): bool
    {
        return str_starts_with($value, $field);
    }

    /**
     * @return mixed|string
     */
    public function getFiltersJSON()
    {
        return decode_html($this->get('filters'));
    }

    /**
     * @return array[]|mixed
     */
    public function getFilters()
    {
        if ($this->isEmpty('filters')) {
            return [
                0 => [],
                1 => [],
            ];
        }

        return json_decode(decode_html($this->get('filters')), true);
    }

    /**
     * @return array[]
     */
    public function getAdvanceCriteria(): array
    {
        return $this->getFilters();
    }

    /**
     * @return string
     */
    public function getEditViewUrl(): string
    {
        return 'index.php?module=' . $this->get('module_name') . '&view=RelatedBlock&mode=edit&record=' . $this->getId();
    }

    /**
     * @throws AppException
     */
    public static function getAll(string $moduleName): array
    {
        $table = (new self())->getRelatedBlockTable();
        $result = $table->selectResult(['id'], ['module' => $moduleName]);
        $instances = [];

        while ($row = $table->getDB()->fetchByAssoc($result)) {
            $instances[] = self::getInstanceById($row['id'], $moduleName);
        }

        return $instances;
    }

    /**
     * @param string $moduleName
     * @return array
     * @throws AppException
     */
    public static function getAllOptions(string $moduleName): array
    {
        $table = (new self())->getRelatedBlockTable();
        $result = $table->selectResult(['id', 'name'], ['module' => $moduleName]);
        $options = [];

        while ($row = $table->getDB()->fetchByAssoc($result)) {
            $options[$row['id']] = $row['name'];
        }

        return $options;
    }

    public function setSourceRecord(Vtiger_Record_Model $sourceRecord): void
    {
        $this->sourceRecord = $sourceRecord;
    }

    public function setSourceRecordId(int $value): void
    {
        $this->set('source_record_id', $value);
    }

    public function retrieveSourceRecord(): void
    {
        $this->setSourceRecord(Vtiger_Record_Model::getInstanceById($this->get('source_record_id')));
    }

    public function getTemplateContent()
    {
        $content = $this->getContent();
        $content = str_replace(
            [
                '#RELATED_BLOCK_START#',
                '#RELATED_BLOCK_END#',
            ],
            [
                sprintf('#RELATED_BLOCK_%s_START#', $this->getId()),
                sprintf('#RELATED_BLOCK_%s_END#', $this->getId()),
            ],
            $content,
        );

        return $content;
    }

    /**
     * @param Vtiger_Record_Model $recordModel
     * @param string $content
     * @return string
     * @throws AppException
     * @throws Exception
     */
    public static function replaceAll(Vtiger_Record_Model $recordModel, string $content): string
    {
        $moduleName = $recordModel->getModuleName();
        $regex = '/#RELATED_BLOCK_([0-9]+)_START#/m';

        preg_match_all($regex, $content, $matches);

        $newContent = '';
        $afterRelatedBlock = '';
        $oldContent = $content;

        foreach ($matches[1] as $relatedBlockId) {
            $relatedBlock = Core_RelatedBlock_Model::getInstanceById($relatedBlockId, $moduleName);
            $relatedBlock->setSourceRecord($recordModel);
            $relatedBlock->setSourceRecordId($recordModel->getId());
            $oldContent = $relatedBlock->replaceLabels($oldContent);

            [$beforeRelatedBlock, $relatedBlockContent] = explode('#RELATED_BLOCK_' . $relatedBlockId . '_START#', $oldContent, 2);
            [$relatedBlockContent, $afterRelatedBlock] = explode('#RELATED_BLOCK_' . $relatedBlockId . '_END#', $relatedBlockContent, 2);

            $newContent .= $beforeRelatedBlock;
            $newContent .= '#HIDETR#';
            $newContent .= $relatedBlock->replaceRecords($relatedBlockContent);
            $newContent .= '#HIDETR#';
            $oldContent = $afterRelatedBlock;
        }

        $newContent .= $afterRelatedBlock;

        return $newContent;
    }

    public function getRelatedModuleJoin()
    {
        if ('Documents' === $this->getRelatedModuleName()) {
            return [
                'senotesrel',
                'vtiger_senotesrel',
                'INNER JOIN vtiger_senotesrel',
                sprintf('vtiger_senotesrel.notesid=vtiger_crmentity.crmid AND vtiger_senotesrel.crmid=%d', $this->getSourceRecord()->getId()),
            ];
        }

        if ('ModComments' === $this->getRelatedModuleName()) {
            return [
                'modcommentsrel',
                'vtiger_modcommentsrel',
                'INNER JOIN vtiger_modcomments',
                sprintf('vtiger_modcommentsrel.modcommentsid=vtiger_crmentity.crmid AND vtiger_modcommentsrel.related_to=%d', $this->getSourceRecord()->getId()),
            ];
        }

        return [
            'crmentityrel',
            'vtiger_crmentityrel',
            'INNER JOIN vtiger_crmentityrel',
            sprintf('vtiger_crmentityrel.relcrmid=vtiger_crmentity.crmid AND vtiger_crmentityrel.crmid=%d', $this->getSourceRecord()->getId()),
        ];
    }

    /**
     * @throws Exception
     */
    public function replaceRecords($content): string
    {
        $relatedModule = $this->getRelatedModule();

        if (!$relatedModule) {
            return $content;
        }

        $relatedModuleName = $relatedModule->getName();
        $relatedModuleFields = $this->getRelatedFields();
        $query = $this->getQuery();

        $this->retrieveDB();
        $adb = $this->getDB();
        $result = $this->getDB()->pquery($query);

        $newContent = '';
        $relatedRecord = Vtiger_Record_Model::getCleanInstance($relatedModuleName);

        while ($row = $adb->fetchByAssoc($result)) {
            $relatedRecord->setData($row);

            $relatedRecordContent = $content;

            foreach ($relatedModuleFields as $fieldName) {
                $relatedRecordContent = str_replace($this->getVariable($fieldName), $relatedRecord->getDisplayValue($fieldName), $relatedRecordContent);
            }

            if(!empty($newContent)) {
                $newContent .= '#HIDETR#';
            }

            $newContent .= $relatedRecordContent;
        }

        return $newContent;
    }

    /**
     * @throws Exception
     */
    public function getQuery(): string
    {
        $relatedModuleJoin = $this->getRelatedModuleJoin();

        $queryGenerator = $this->getQueryGenerator();
        $queryGenerator->setConvertColumnToName(true);
        $queryGenerator->setOrderByClauseRequired(true);

        $queryGenerator->setFields($this->getRelatedFields());
        $queryGenerator->setCustomTableJoins($relatedModuleJoin[0], $relatedModuleJoin[1], $relatedModuleJoin[2], $relatedModuleJoin[3]);
        $queryGenerator->parseAdvFilterList($this->getFilters());
        $queryGenerator->setOrderByColumns($this->getOrderByColumns());

        return $queryGenerator->getQuery();
    }

    /**
     * @return EnhancedQueryGenerator
     */
    public function getQueryGenerator(): EnhancedQueryGenerator
    {
        $currentUser = Users_Record_Model::getCurrentUserModel();

        if (empty($this->queryGenerator)) {
            $this->queryGenerator = new EnhancedQueryGenerator($this->getRelatedModuleName(), $currentUser);
        }

        return $this->queryGenerator;
    }

    /**
     * @throws Exception
     */
    public function replaceLabels($content): string
    {
        $options = $this->getRelatedFieldsOptions();

        foreach ($options as $optionKey => $optionLabel) {
            $content = str_replace($this->getVariableLabel($optionKey), $optionLabel, $content);
        }

        return $content;
    }

    public function getVariableLabel($fieldName): string
    {
        return '%' . $this->getVariableName($fieldName) . '%';
    }

    public function getVariableName($fieldName): string
    {
        return strtoupper(sprintf('RB_%s_%s', $this->getRelatedModuleName(), $fieldName));
    }

    public function getVariable($fieldName): string
    {
        return '$' . $this->getVariableName($fieldName) . '$';
    }

    /**
     * @return array
     * @throws Exception
     */
    public function getOrderByColumns(): array
    {
        $sortFields = explode(';', $this->getSorting());
        $data = [];

        foreach ($sortFields as $sortField) {
            [$orderBy, $sortOrder] = explode(' ', $sortField);

            if(empty($orderBy) || empty($sortOrder)) {
                continue;
            }

            $field = $this->getRelatedModule()->getField($orderBy);

            if ($field) {
                $data[$field->get('table') . '.' . $field->get('column')] = $sortOrder;
            }
        }

        return $data;
    }

    public function getSorting()
    {
        return decode_html($this->get('sorting'));
    }
}