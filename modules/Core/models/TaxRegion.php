<?php

class Core_TaxRegion_Model extends Core_DatabaseData_Model
{
    protected string $table = 'df_taxes_regions';
    protected string $tableId = 'region_id';
    protected string $tableName = 'name';
    protected array $columns = ['name'];

    /**
     * @return void
     * @throws Exception
     */
    public function createLinks(): void
    {
        $menu = Settings_Vtiger_Menu_Model::getInstance('LBL_TAX_MANAGEMENT');

        if (!$menu) {
            $menu = Settings_Vtiger_Menu_Model::getInstanceFromArray(['label' => 'LBL_TAX_MANAGEMENT']);
            $menu->save();
        }

        $link = Settings_Vtiger_MenuItem_Model::getInstance('LBL_REGIONS', $menu);

        if (!$link) {
            $link = Settings_Vtiger_MenuItem_Model::getInstanceFromArray(['name' => 'LBL_REGIONS', 'blockid' => $menu->getId(), 'linkto' => 'index.php?module=Core&parent=Settings&view=Taxes&mode=regions']);
            $link->save();
        }
    }

    /**
     * @return void
     * @throws AppException
     */
    public function createTables(): void
    {
        $this->getTable($this->table, $this->tableId)
            ->createTable()
            ->createColumn('name', 'VARCHAR(200)');
    }

    /**
     * @return array
     * @throws AppException
     */
    public static function getAllRegions(): array
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery('SELECT region_id FROM df_taxes_regions');
        $regions = [];

        while ($row = $adb->fetchByAssoc($result)) {
            $region = self::getInstanceById($row['region_id']);
            $regions[$region->getId()] = $region;
        }

        return $regions;
    }

    /**
     * @throws AppException
     */
    public static function getInstance($name = ''): self
    {
        $instance = new self();

        if (!empty($name)) {
            $instance->setName($name);
            $instance->retrieveDataByName();
        }

        return $instance;
    }

    /**
     * @param int $recordId
     * @return bool|self
     * @throws AppException
     */
    public static function getInstanceById(int $recordId): bool|self
    {
        $instance = self::getInstance();
        $instance->setId($recordId);
        $instance->retrieveDataById();

        if ($instance->isEmpty($instance->tableName)) {
            return false;
        }

        return $instance;
    }

    /**
     * @throws AppException
     */
    public static function getInstanceFromRequest(Vtiger_Request $request): bool|self
    {
        if (!$request->isEmpty('record')) {
            return self::getInstanceById($request->get('record'));
        }

        return self::getInstance($request->get('name', ''));
    }

    /**
     * @param mixed $values
     * @return bool
     */
    public function isSelectedRegion(mixed $values): bool
    {
        return in_array($this->getId(), (array)$values);
    }

    /**
     * @return array
     */
    public function getSaveParams(): array
    {
        return ['name' => $this->getName()];
    }
}