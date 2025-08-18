<?php
/*
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Core_InventoryItemsBlock_Model extends Core_RelatedBlock_Model
{
    public string $variableLabelModule = 'EMAILMaker';
    protected array $decodeFields = ['related_fields', 'content', 'name'];
    /**
     * @var string
     */
    protected string $table = 'df_inventoryitems_block';
    /**
     * @var string
     */
    protected string $tableId = 'id';
    protected string $variablePrefix = 'IB';

    public function getArticleOptions(): array
    {
        return [
            '' => vtranslate('LBL_PLS_SELECT', $this->variableLabelModule),
            vtranslate('LBL_PRODUCTS_AND_SERVICES', $this->variableLabelModule) => [
                'INVENTORY_BLOCK_START' => vtranslate('LBL_ARTICLE_START', $this->variableLabelModule),
                'INVENTORY_BLOCK_END' => vtranslate('LBL_ARTICLE_END', $this->variableLabelModule),
            ],
            vtranslate('LBL_PRODUCTS_ONLY', $this->variableLabelModule) => [
                'INVENTORY_BLOCK_PRODUCTS_START' => vtranslate('LBL_ARTICLE_START', $this->variableLabelModule),
                'INVENTORY_BLOCK_PRODUCTS_END' => vtranslate('LBL_ARTICLE_END', $this->variableLabelModule),
            ],
            vtranslate('LBL_SERVICES_ONLY', $this->variableLabelModule) => [
                'INVENTORY_BLOCK_SERVICES_START' => vtranslate('LBL_ARTICLE_START', $this->variableLabelModule),
                'INVENTORY_BLOCK_SERVICES_END' => vtranslate('LBL_ARTICLE_END', $this->variableLabelModule),
            ],
        ];
    }

    /**
     * @param string $moduleName
     *
     * @return self
     * @throws Exception
     */
    public static function getCleanInstance(string $moduleName): self
    {
        $className = Vtiger_Loader::getComponentClassName('Model', 'InventoryItemsBlock', $moduleName);

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
     * @return Core_InventoryItemsBlock_Model
     * @throws Exception
     */
    public static function getInstance(string $moduleName): self
    {
        $instance = self::getCleanInstance($moduleName);
        $instance->set('module_name', $moduleName);
        $instance->set('related_module', 'InventoryItem');

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
     * @param string $moduleName
     *
     * @return array
     * @throws Exception
     */
    public static function getAllOptions(string $moduleName = ''): array
    {
        $table = (new self())->getInventoryItemBlockTable();

        if (!empty($moduleName)) {
            $search = ['module' => $moduleName];
        } else {
            $search = [];
        }

        $result = $table->selectResult(['content', 'name'], $search);
        $options = [];

        while ($row = $table->getDB()->fetchByAssoc($result)) {
            $options[$row['content']] = $row['name'];
        }

        return $options;
    }

    /**
     * @param Vtiger_Record_Model $recordModel
     * @param string $content
     *
     * @return string
     * @throws Exception
     */
    public static function replaceAll(Vtiger_Record_Model $recordModel, string $content): string
    {
        $moduleName = $recordModel->getModuleName();
        $relatedBlock = Core_InventoryItemsBlock_Model::getInstance($moduleName);
        $relatedBlock->setSourceRecord($recordModel);
        $relatedBlock->setSourceRecordId($recordModel->getId());
        $relatedBlock->retrieveFieldsFromContent($content);

        $content = $relatedBlock->replaceLabels($content);
        $content = $relatedBlock->replaceInventoryBlock($content);

        foreach (['Products' => 'productname', 'Services' => 'servicename'] as $moduleName => $fieldName) {
            $blockName = 'INVENTORY_BLOCK_' . strtoupper($moduleName);
            $queryGenerator = $relatedBlock->getNewQueryGenerator();
            $queryGenerator->addCondition(sprintf('(productid ; (%s) %s)', $moduleName, $fieldName), '', $queryGenerator::NOT_EMPTY);
            $content = $relatedBlock->replaceInventoryBlock($content, $blockName);
        }

        return $content;
    }

    /**
     * @throws Exception
     */
    public function getVariableValues(Vtiger_Record_Model $recordModel): array
    {
        $values = parent::getVariableValues($recordModel);

        $recordId = (int)$recordModel->get('productid');
        $fields = [
            'Products' => [
                'fields' => (array)$this->get('product_fields'),
                'record' => Vtiger_Record_Model::getCleanInstance('Products'),
            ],
            'Services' => [
                'fields' => (array)$this->get('service_fields'),
                'record' => Vtiger_Record_Model::getCleanInstance('Services'),
            ],
        ];

        if (!empty($recordId) && isRecordExists($recordId)) {
            $recordModel = Vtiger_Record_Model::getInstanceById($recordId);
            $fields[$recordModel->getModuleName()]['record'] = $recordModel;
        } else {
            $recordModel = $fields['Products']['record'];
        }

        foreach ($fields as $moduleName => $fieldInfo) {
            foreach ($fieldInfo['fields'] as $fieldName) {
                $values[$this->getVariable($fieldName, $moduleName)] = $fieldInfo['record']->getRelatedBlockDisplayValue($fieldName);
            }
        }

        $values['$PS_ID$'] = $recordModel->getId();
        $values['$PS_NO$'] = $recordModel->getNumber();
        $values['$PS_NAME$'] = $recordModel->getName() . '<br>' . $recordModel->getDescription();
        $values['$PS_TITLE$'] = $recordModel->getName();
        $values['$PS_DESCRIPTION$'] = $recordModel->getDescription();

        return $values;
    }

    public function getSorting(): string
    {
        return 'sequence ASC';
    }

    /**
     * @return void
     * @throws Exception
     */
    public function createTables(): void
    {
        $this->getInventoryItemBlockTable()
            ->createTable()
            ->createColumn('name', 'VARCHAR(200)')
            ->createColumn('module', 'VARCHAR(100)')
            ->createColumn('related_fields', 'TEXT')
            ->createColumn('content', 'TEXT');
    }

    public function getSaveParams(): array
    {
        return [
            'name' => $this->get('name'),
            'module' => $this->get('module_name'),
            'related_fields' => $this->get('related_fields'),
            'content' => $this->get('content'),
        ];
    }

    /**
     * @return string
     */
    public function getCreateViewUrl(): string
    {
        return 'index.php?module=' . $this->get('module_name') . '&view=InventoryItemsBlock&mode=edit';
    }

    public function getRelatedModuleName(): string
    {
        return 'InventoryItem';
    }

    public function getRelatedFieldName(): string
    {
        return 'parentid';
    }

    public function retrieveFromRequest($request): void
    {
        $fields = [
            'related_module',
            'related_field',
            'related_fields',
            'content',
            'name',
        ];

        $request->set('related_module', $this->getRelatedModuleName());
        $request->set('related_field', $this->getRelatedFieldName());
        $requestData = $request->getAll();

        foreach ($fields as $field) {
            if (array_key_exists($field, $requestData)) {
                $value = $request->get($field);
                $this->set($field, $value);
            }
        }
    }

    public function getFieldsFromContentForModule(string $content, string $moduleName): array
    {
        $regex = sprintf('/\$IB_%s_([a-zA-Z0-9_-]+)\$/m', strtoupper($moduleName));
        preg_match_all($regex, $content, $matches);
        $fieldNames = $matches[1];
        $relatedFields = [];

        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);

        foreach ($moduleModel->getFields() as $fieldModel) {
            if (in_array(strtoupper($fieldModel->getName()), $fieldNames)) {
                $relatedFields[] = $fieldModel->getName();
            }
        }

        return $relatedFields;
    }

    /**
     * @return self
     */
    public function getInventoryItemBlockTable(): self
    {
        return $this->getTable('df_inventoryitems_block', 'id');
    }

    public function getProductVariableOptions(): array
    {
        $options = $this->getVariableOptionsForModule('Products');

        return $this->translateOptions($options);
    }

    public function getServiceVariableOptions(): array
    {
        $options = $this->getVariableOptionsForModule('Services');

        return $this->translateOptions($options);
    }

    public function getVariableOptions(): array
    {
        $customOptions = [
            vtranslate('LBL_CUSTOM_INFORMATION', $this->variableLabelModule) => [
                'PS_ID' => 'LBL_RECORD_ID',
                'PS_NO' => 'LBL_PS_NO',
                'PS_NAME' => 'LBL_VARIABLE_PRODUCTNAME',
                'PS_TITLE' => 'LBL_VARIABLE_PRODUCTTITLE',
                'PS_DESCRIPTION' => 'LBL_VARIABLE_PRODUCTDESCRIPTION',
            ],
        ];
        $options = array_merge_recursive($this->getVariableOptionsForModule('InventoryItem'), $customOptions);

        return $this->translateOptions($options);
    }

    public function getVariableOptionsForModule(string $moduleName): array
    {
        $options = [
            '' => 'LBL_PLS_SELECT',
        ];
        $module = Vtiger_Module_Model::getInstance($moduleName);

        foreach ($module->getBlocks() as $block) {
            $blockLabel = vtranslate($block->get('label'), $module->getName());

            foreach ($block->getFields() as $field) {
                if ($field->isViewable() && $field->isActiveField()) {
                    $options[$blockLabel][$this->getVariableName($field->getName(), $module->getName())] = vtranslate($field->get('label'), $module->getName());
                }
            }
        }

        return $options;
    }

    /**
     * @throws Exception
     */
    public function replaceInventoryBlock($content, $blockName = 'INVENTORY_BLOCK'): string
    {
        $blockVariable = '#' . strtoupper($blockName) . '_START#';
        $blockVariableEnd = '#' . strtoupper($blockName) . '_END#';

        if (!str_contains($content, $blockVariable)) {
            return $content;
        }

        [$contentStart, $inventoryBlock] = explode($blockVariable, $content, 2);
        [$inventoryBlock, $contentEnd] = explode($blockVariableEnd, $inventoryBlock, 2);

        if (!empty($contentEnd)) {
            $content = $contentStart . $this->replaceRecords($inventoryBlock) . $contentEnd;
        }

        if (str_contains($content, $blockVariable)) {
            $content = $this->replaceInventoryBlock($content);
        }

        return $content;
    }

    public function retrieveFieldsFromContent(string $content): void
    {
        $relatedFields = array_merge(['parentid', 'productid'], $this->getFieldsFromContentForModule($content, $this->getRelatedModuleName()));

        $this->set('related_fields', implode(';', array_unique($relatedFields)));
        $this->set('product_fields', array_unique($this->getFieldsFromContentForModule($content, 'Products')));
        $this->set('service_fields', array_unique($this->getFieldsFromContentForModule($content, 'Services')));
    }

    public function translateOptions($options)
    {
        foreach ($options as $key => $value) {
            if (is_array($value)) {
                $options[$key] = $this->translateOptions($value);
            } else {
                $options[$key] = vtranslate($value, $this->variableLabelModule);
            }
        }

        return $options;
    }
}