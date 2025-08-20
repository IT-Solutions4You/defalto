<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Core_RelatedBlock_Model extends Core_DatabaseData_Model
{
    public static array $currencyUserConfig = [];
    public static array $numberUserConfig = [];

    protected string $variablePrefix = 'RB';
    protected string $recordPrefix = 'RB';

    protected string $templateModule = 'Core';

    /**
     * @var array
     */
    public static array $relatedModuleJoin = [
        'Documents'   => [
            'senotesrel',
            'vtiger_senotesrel',
            'INNER JOIN vtiger_senotesrel',
            'vtiger_senotesrel.notesid=vtiger_crmentity.crmid AND vtiger_senotesrel.crmid=$SOURCE_RECORD$',
        ],
        'ModComments' => [
            'modcommentsrel',
            'vtiger_modcommentsrel',
            'INNER JOIN vtiger_modcomments',
            'vtiger_modcommentsrel.modcommentsid=vtiger_crmentity.crmid AND vtiger_modcommentsrel.related_to=$SOURCE_RECORD$',
        ],
        'Products'    => [
            'seproductsrel',
            'vtiger_seproductsrel',
            'INNER JOIN vtiger_seproductsrel',
            'vtiger_seproductsrel.productid=vtiger_crmentity.crmid AND vtiger_seproductsrel.crmid=$SOURCE_RECORD$',
        ],
    ];

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
    protected object|bool $related_field = false;
    protected Vtiger_Record_Model $sourceRecord;
    protected object|null $queryGenerator = null;
    protected array $decodeFields = ['filters', 'related_field', 'related_fields', 'sorting', 'content', 'name'];

    /**
     * @param string $moduleName
     *
     * @return self
     * @throws Exception
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
     *
     * @return Core_RelatedBlock_Model
     * @throws Exception
     */
    public static function getInstance(string $moduleName): self
    {
        $instance = self::getCleanInstance($moduleName);
        $instance->set('module_name', $moduleName);

        return $instance;
    }

    /**
     * @throws Exception
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
        foreach ($this->decodeFields as $field) {
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
     *
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
     * @return bool|Vtiger_Module_Model
     */
    public function getRelatedModule(): Vtiger_Module_Model|bool
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
             * @var $relation       Vtiger_Relation_Model
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
    public function getRelatedRecordStructure(): mixed
    {
        return Vtiger_RecordStructure_Model::getInstanceForModule($this->getRelatedModule())->getStructure();
    }

    /**
     * @return array
     */
    public function getRelatedModuleSortOptions()
    {
        $options = [];
        $fields = $this->getRelatedModule()->getFields();

        foreach ($fields as $field) {
            $options[$field->getName()] = $field->get('label');
        }

        return $options;
    }

    /**
     * @return Vtiger_Record_Model
     */
    public function getSourceRecord(): Vtiger_Record_Model
    {
        return $this->sourceRecord;
    }

    /**
     * @param $value
     *
     * @return bool
     */
    public function isSelectedRelatedFields($value): bool
    {
        return in_array($value, $this->getRelatedFields());
    }

    /**
     * @param string $value
     *
     * @return bool
     */
    public function isSelectedRelatedModule(string $value): bool
    {
        return $this->get('related_module') === $value;
    }

    /**
     * @param Vtiger_Request $request
     *
     * @return void
     */
    public function retrieveFromRequest($request): void
    {
        $fields = [
            'related_module',
            'related_field',
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
                } elseif ('filters' === $field) {
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
     * @throws Exception
     */
    public function createTables(): void
    {
        $this->getRelatedBlockTable()
            ->createTable()
            ->createColumn('name', 'VARCHAR(200)')
            ->createColumn('module', 'VARCHAR(100)')
            ->createColumn('related_module', 'VARCHAR(100)')
            ->createColumn('related_field', 'VARCHAR(100)')
            ->createColumn('related_fields', 'TEXT')
            ->createColumn('filters', 'TEXT')
            ->createColumn('sorting', 'VARCHAR(200)')
            ->createColumn('content', 'TEXT');
    }

    /**
     * @return array
     */
    public function getSaveParams(): array
    {
        return [
            'name'           => $this->get('name'),
            'module'         => $this->get('module_name'),
            'related_module' => $this->get('related_module'),
            'related_field'  => $this->get('related_field'),
            'related_fields' => $this->get('related_fields'),
            'filters'        => htmlentities($this->get('filters')),
            'sorting'        => $this->get('sorting'),
            'content'        => $this->get('content'),
        ];
    }

    /**
     * @param string $value
     * @param string $type
     *
     * @return bool
     */
    public function isCheckedSort(string $value, string $type): bool
    {
        return str_ends_with($value, $type);
    }

    /**
     * @param string $value
     * @param string $field
     *
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
                1 => [
                    'columns' => [],
                ],
                2 => [
                    'columns' => [],
                ],
            ];
        }

        return json_decode(decode_html($this->get('filters')), true);
    }

    /**
     * @return array
     */
    public function getFormatedFilters(): array
    {
        $filters = $this->getFilters();

        if (empty($filters[2]['columns'])) {
            $filters[1]['condition'] = '';
        }

        return $filters;
    }

    /**
     * @return array
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
        return $this->getCreateViewUrl() . '&record=' . $this->getId();
    }

    /**
     * @return string
     */
    public function getCreateViewUrl(): string
    {
        return 'index.php?module=' . $this->get('module_name') . '&view=RelatedBlock&mode=edit';
    }

    /**
     * @throws Exception
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
     *
     * @return array
     * @throws Exception
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

    /**
     * @param Vtiger_Record_Model $sourceRecord
     *
     * @return void
     */
    public function setSourceRecord(Vtiger_Record_Model $sourceRecord): void
    {
        $this->sourceRecord = $sourceRecord;
    }

    /**
     * @param int $value
     *
     * @return void
     */
    public function setSourceRecordId(int $value): void
    {
        $this->set('source_record_id', $value);
    }

    /**
     * @return void
     */
    public function retrieveSourceRecord(): void
    {
        $this->setSourceRecord(Vtiger_Record_Model::getInstanceById($this->get('source_record_id')));
    }

    /**
     * @return string
     */
    public function getTemplateContent(): string
    {
        $content = $this->getContent();

        return str_replace(
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
    }

    /**
     * @param Vtiger_Record_Model $recordModel
     * @param string              $content
     *
     * @return string
     * @throws Exception
     */
    public static function replaceAll(Vtiger_Record_Model $recordModel, string $content, string $templateModule = 'EMAILMaker'): string
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
            $relatedBlock->setTemplateModule($templateModule);

            [$beforeRelatedBlock, $relatedBlockContent] = explode('#RELATED_BLOCK_' . $relatedBlockId . '_START#', $oldContent, 2);
            [$relatedBlockContent, $afterRelatedBlock] = explode('#RELATED_BLOCK_' . $relatedBlockId . '_END#', $relatedBlockContent, 2);

            $newContent .= $beforeRelatedBlock;
            $newContent .= '#HIDETR#';
            $newContent .= $relatedBlock->replaceRecords($relatedBlockContent);
            $newContent .= '#HIDETR#';
            $oldContent = $afterRelatedBlock;
            $oldContent = $relatedBlock->replaceLabels($oldContent);
        }

        $newContent .= $afterRelatedBlock;

        return $newContent;
    }

    /**
     * @throws Exception
     */
    public function getRelatedModuleJoin(): array
    {
        if (!empty($this->getRelatedFieldName())) {
            $field = $this->getRelatedField();
            $column = $field->get('column');
            $table = $field->get('table');
            $tableId = $this->getRelatedTableIndex($table);
            $tableRel = $table . '_rel';

            return [
                $column . '_rel',
                $tableRel,
                'INNER JOIN ' . $table,
                sprintf('%s.%s=vtiger_crmentity.crmid AND %s.%s=%d', $tableRel, $tableId, $tableRel, $column, $this->getSourceRecord()->getId()),
            ];
        }

        $relatedModule = $this->getRelatedModuleName();

        if (isset(self::$relatedModuleJoin[$relatedModule])) {
            $data = self::$relatedModuleJoin[$relatedModule];
            $data[3] = str_replace(['$SOURCE_RECORD$'], [$this->getSourceRecord()->getId()], $data[3]);

            return $data;
        }

        return [
            'crmentityrel',
            'vtiger_crmentityrel',
            'INNER JOIN vtiger_crmentityrel',
            sprintf('vtiger_crmentityrel.relcrmid=vtiger_crmentity.crmid AND vtiger_crmentityrel.crmid=%d', $this->getSourceRecord()->getId()),
        ];
    }

    /**
     * @return void
     */
    public function retrieveNumberUsers(): void
    {
        global $number_user, $currency_user;

        $currentUser = Users_Record_Model::getCurrentUserModel();
        $currency_user = clone $currentUser;
        $number_user = clone $currentUser;

        foreach (self::$currencyUserConfig as $key => $value) {
            $currency_user->$key = $value;
        }

        foreach (self::$numberUserConfig as $key => $value) {
            $number_user->$key = $value;
        }
    }

    /**
     * @throws Exception
     */
    public function replaceRecords(string $content): string
    {
        $this->retrieveNumberUsers();

        $relatedModule = $this->getRelatedModule();

        if (!$relatedModule) {
            return $content;
        }

        $relatedModuleName = $relatedModule->getName();
        $query = $this->getQuery();

        $this->retrieveDB();
        $adb = $this->getDB();
        $result = $this->getDB()->pquery($query);

        $newContent = '#HIDETR#';
        $relatedRecord = Vtiger_Record_Model::getCleanInstance($relatedModuleName);
        $recordLineBreak = Core_RecordLineBreak_Helper::getInstance()
            ->setRelatedBlock($this);

        while ($row = $adb->fetchByAssoc($result)) {
            $relatedRecord->setData($row);
            $breakLine = $recordLineBreak
                ->plusSequence()
                ->setRecord($relatedRecord)
                ->retrieveData()
                ->getContent();
            $relatedRecordContent = $content;

            foreach ($this->getVariableValues($relatedRecord) as $variableKey => $variableValue) {
                $relatedRecordContent = str_replace($variableKey, $variableValue, $relatedRecordContent);
            }

            $newContent .= $relatedRecordContent . $breakLine;
        }

        return $newContent;
    }

    /**
     * @throws Exception
     */
    public function getVariableValues(Vtiger_Record_Model $recordModel): array
    {
        $relatedModuleName = $this->getRelatedModuleName();
        $relatedFields = $this->getRelatedFields();
        $values = [];

        foreach ($relatedFields as $fieldName) {
            $values[$this->getVariable($fieldName, $relatedModuleName)] = $recordModel->getRelatedBlockDisplayValue($fieldName);
        }

        return $values;
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
        $queryGenerator->parseAdvFilterList($this->getFormatedFilters());
        $queryGenerator->setOrderByColumns($this->getOrderByColumns());

        return $queryGenerator->getQuery();
    }

    /**
     * @return object
     */
    public function getQueryGenerator(): object
    {
        if (!empty($this->queryGenerator)) {
            return $this->queryGenerator;
        }

        return $this->getNewQueryGenerator();
    }

    public function getNewQueryGenerator(): Core_QueryGenerator_Model
    {
        $currentUser = Users_Record_Model::getCurrentUserModel();
        $this->queryGenerator = new Core_QueryGenerator_Model($this->getRelatedModuleName(), $currentUser);

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

    /**
     * @param $fieldName
     *
     * @return string
     */
    public function getVariableLabel($fieldName, $moduleName = null): string
    {
        $moduleName = $moduleName ?: $this->getRelatedModuleName();

        return '%' . $this->getVariableName($fieldName, $moduleName) . '%';
    }

    /**
     * @param $fieldName
     * @param null $moduleName
     * @return string
     */
    public function getVariableName($fieldName, $moduleName = null): string
    {
        $moduleName = $moduleName ?: $this->getRelatedModuleName();

        return strtoupper(sprintf('%s_%s_%s', $this->variablePrefix, $moduleName, $fieldName));
    }

    /**
     * @param $fieldName
     *
     * @return string
     */
    public function getVariable($fieldName, $moduleName = null): string
    {
        $moduleName = $moduleName ?: $this->getRelatedModuleName();

        return '$' . $this->getVariableName($fieldName, $moduleName) . '$';
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

            if (empty($orderBy) || empty($sortOrder)) {
                continue;
            }

            $field = $this->getRelatedModule()->getField($orderBy);

            if ($field) {
                $data[$field->get('table') . '.' . $field->get('column')] = $sortOrder;
            }
        }

        return $data;
    }

    /**
     * @return string
     */
    public function getSorting(): string
    {
        return decode_html($this->get('sorting'));
    }

    /**
     * @return array|mixed
     */
    public function getRelatedModuleFieldOptions(): array
    {
        $options = [
            '' => vtranslate('LBL_RELATION_LIST', $this->getModuleName()),
        ];
        $module = $this->getRelatedModule();

        if (empty($module)) {
            return $options;
        }

        $fields = $module->getFieldsByType('reference');

        foreach ($fields as $field) {
            $list = $field->getReferenceList();

            if (in_array($this->getModuleName(), $list)) {
                $label = vtranslate($field->get('label'), $module->getName());
                $options = [$field->get('name') => $label] + $options;
            }
        }

        return $options;
    }

    /**
     * @param string $value
     *
     * @return bool
     */
    public function isSelectedRelatedField(string $value): bool
    {
        return $value === $this->getRelatedFieldName();
    }

    /**
     * @return string
     */
    public function getRelatedFieldName(): string
    {
        return (string)$this->get('related_field');
    }

    /**
     * @return bool|Vtiger_Field_Model
     */
    public function getRelatedField(): Vtiger_Field_Model|bool
    {
        if (empty($this->related_field)) {
            $this->related_field = Vtiger_Field_Model::getInstance($this->getRelatedFieldName(), $this->getRelatedModule());
        }

        return $this->related_field;
    }

    /**
     * @param string $table
     *
     * @return string
     */
    public function getRelatedTableIndex(string $table): string
    {
        $focus = CRMEntity::getInstance($this->getRelatedModuleName());

        return $focus->tab_name_index[$table];
    }

    public function setTemplateModule(string $moduleName): void
    {
        $this->templateModule = $moduleName;
    }

    public function getTemplateModule(): string
    {
        return $this->templateModule;
    }

    public function getHeaderFields()
    {
        return $this->get('header_fields');
    }
}