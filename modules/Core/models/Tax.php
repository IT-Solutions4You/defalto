<?php

class Core_Tax_Model extends Core_DatabaseData_Model
{
    /**
     * @var array|string[]
     */
    protected array $columns = [
        'tax_label',
        'percentage',
        'method',
        'compound_on',
        'regions',
        'deleted',
        'active',
    ];
    /**
     * @var string
     */
    protected string $table = 'df_taxes';
    /**
     * @var string
     */
    protected string $tableId = 'tax_id';
    /**
     * @var string
     */
    protected string $tableName = 'tax_label';

    /**
     * @throws Exception
     */
    public function createLinks(): void
    {
        $menu = Settings_Vtiger_Menu_Model::getInstance('LBL_TAX_MANAGEMENT');

        if (!$menu) {
            $menu = Settings_Vtiger_Menu_Model::getInstanceFromArray(['label' => 'LBL_TAX_MANAGEMENT']);
            $menu->save();
        }

        $link = Settings_Vtiger_MenuItem_Model::getInstance('LBL_TAXES', $menu);

        if (!$link) {
            $link = Settings_Vtiger_MenuItem_Model::getInstanceFromArray(['name' => 'LBL_TAXES', 'blockid' => $menu->getId(), 'linkto' => 'index.php?module=Core&parent=Settings&view=Taxes&mode=taxes']);
            $link->save();
        }
    }

    /**
     * @return self
     */
    public function getTaxTable(): self
    {
        return $this->getTable($this->table, $this->tableId);
    }

    /**
     * @throws AppException
     */
    public function createTables(): void
    {
        $this->getTaxTable()
            ->createTable()
            ->createColumn('tax_label', 'varchar(50)')
            ->createColumn('percentage', 'decimal(7,3)')
            ->createColumn('method', 'varchar(10)')
            ->createColumn('compound_on', 'varchar(400)')
            ->createColumn('regions', 'text')
            ->createColumn('deleted', 'int(1)')
            ->createColumn('active', 'int(1)');
    }

    /**
     * @return array
     * @throws AppException
     */
    public static function getAllTaxes(): array
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery('SELECT tax_id FROM df_taxes WHERE deleted != 1');
        $taxes = [];

        while ($row = $adb->fetchByAssoc($result)) {
            $taxes[] = self::getInstanceById($row['tax_id']);
        }

        return $taxes;
    }

    /**
     * @param string $taxName
     * @return self
     */
    public static function getInstance(string $taxName = ''): self
    {
        $instance = new self();

        if (!empty($taxName)) {
            $instance->setLabel($taxName);
            $instance->set('deleted', 0);
            $instance->set('active', 1);
            $instance->retrieveDataByName();
        }

        return $instance;
    }

    /**
     * @param int $recordId
     * @return self|false
     * @throws AppException
     */
    public static function getInstanceById(int $recordId): bool|self
    {
        $instance = new self();
        $instance->setId($recordId);
        $instance->retrieveDataById();

        if ($instance->isEmpty('tax_label')) {
            return false;
        }

        return $instance;
    }

    /**
     * @throws AppException
     */
    public static function getInstanceFromRequest(Vtiger_Request $request): self|bool
    {
        $record = $request->get('record');

        if (!empty($record)) {
            return Core_Tax_Model::getInstanceById($record);
        }

        return Core_Tax_Model::getInstance($request->get('tax_label', ''));
    }

    /**
     * @return mixed
     */
    public function getLabel()
    {
        return $this->get('tax_label');
    }

    /**
     * @return mixed
     */
    public function getPercentage()
    {
        return $this->get('percentage');
    }

    /**
     * @return array|mixed|string
     */
    public function getRegionTaxes()
    {
        $regions = $this->get('regions');

        if ($regions) {
            return array_values((array)Zend_Json::decode($regions));
        }

        return [];
    }

    /**
     * @return array
     */
    public function getSaveParams(): array
    {
        return [
            'tax_label' => $this->get('tax_label'),
            'percentage' => $this->get('percentage'),
            'method' => $this->get('method'),
            'compound_on' => $this->get('compound_on'),
            'regions' => $this->get('regions'),
            'deleted' => $this->get('deleted'),
            'active' => $this->get('active'),
        ];
    }

    /**
     * @return mixed
     */
    public function getTax()
    {
        return $this->get('percentage');
    }

    /**
     * @return mixed
     */
    public function getTaxMethod()
    {
        return $this->get('method');
    }

    /**
     * @return mixed
     */
    public function getTaxesOnCompound()
    {
        $value = $this->get('compound_on');

        if (!empty($value)) {
            return (array)json_decode($value);
        }

        return [];
    }

    /**
     * @return array
     * @throws AppException
     */
    public static function getSimpleTaxes(): array
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery('SELECT tax_id FROM df_taxes WHERE deleted != 1 AND active = 1 AND method=?', ['Simple']);
        $taxes = [];

        while ($row = $adb->fetchByAssoc($result)) {
            $taxes[] = self::getInstanceById($row['tax_id']);
        }

        return $taxes;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return 1 === (int)$this->get('active');
    }

    /**
     * @return bool
     */
    public function isDeleted(): bool
    {
        return 1 === (int)$this->get('deleted');
    }


    /**
     * @param $value
     * @return void
     */
    public function setLabel($value): void
    {
        $this->set('tax_label', $value);
    }

    /**
     * @return void
     * @throws AppException
     */
    public function delete(): void
    {
        $this->set('deleted', 1);
        $this->set('active', 0);
        $this->save();
    }

    public function isSelectedCompoundOn($taxId)
    {
        return in_array($taxId, $this->getTaxesOnCompound());
    }
}