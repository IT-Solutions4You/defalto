<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Core_TaxRegion_Model extends Core_DatabaseData_Model
{
    use Core_Tax_Trait;

    protected string $table = 'df_taxes_regions';
    protected string $tableId = 'region_id';
    protected string $tableName = 'name';
    protected array $columns = ['name'];
    protected static array $all_regions = [];
    public float $percentage = 0;

    /**
     * @return void
     * @throws Exception
     */
    public function createLinks(): void
    {
        $menu = Settings_Vtiger_Menu_Model::createMenu('LBL_TAX_MANAGEMENT');

        Settings_Vtiger_MenuItem_Model::createItem('LBL_REGIONS', 'index.php?module=Core&parent=Settings&view=Taxes&mode=regions', $menu);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function createTables(): void
    {
        $this->getTable($this->table, $this->tableId)
            ->createTable()
            ->createColumn('name', 'VARCHAR(200)');
    }

    /**
     * @return array
     * @throws Exception
     */
    public static function getAllRegions(): array
    {
        if (!empty(self::$all_regions)) {
            return self::$all_regions;
        }

        $adb = PearDatabase::getInstance();
        $result = $adb->pquery('SELECT region_id FROM df_taxes_regions');
        $regions = [];

        while ($row = $adb->fetchByAssoc($result)) {
            $region = self::getInstanceById($row['region_id']);
            $regions[$region->getId()] = $region;
        }

        self::$all_regions = $regions;

        return $regions;
    }

    /**
     * @throws Exception
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
     *
     * @return bool|self
     * @throws Exception
     */
    public static function getInstanceById(int $recordId): bool|self
    {
        $instance = self::getInstance();
        $instance->setId($recordId);
        $instance->retrieveDataById();
        $instance->retrieveDefaultData();

        if ($instance->isEmpty($instance->tableName)) {
            return false;
        }

        return $instance;
    }

    /**
     * @throws Exception
     */
    public static function getInstanceFromRequest(Vtiger_Request $request): bool|self
    {
        if (!$request->isEmpty('record')) {
            return self::getInstanceById($request->get('record'));
        }

        return self::getInstance($request->get('name', ''));
    }

    /**
     * @param int $regionId
     *
     * @return bool
     */
    public function isSelectedRegion(mixed $regionId): bool
    {
        return $this->getId() === (int)$regionId;
    }

    /**
     * @return array
     */
    public function getSaveParams(): array
    {
        return ['name' => $this->getName()];
    }
}