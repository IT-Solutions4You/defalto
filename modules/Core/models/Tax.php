<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

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
     * @var array
     */
    protected array $regions = [];
    protected static array $all_taxes = [];

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

    protected bool $activeForRecord = false;

    /**
     * @throws Exception
     */
    public function createLinks(): void
    {
        $menu = Settings_Vtiger_Menu_Model::createMenu('LBL_INVENTORY');

        Settings_Vtiger_MenuItem_Model::createItem('LBL_TAXES', 'index.php?module=Core&parent=Settings&view=Taxes&mode=taxes', $menu);
    }

    public function clearLinks(): void
    {
        $menu = Settings_Vtiger_Menu_Model::getInstance('LBL_INVENTORY');

        if ($menu) {
            $link = Settings_Vtiger_MenuItem_Model::getInstance('LBL_TAX_SETTINGS', $menu);

            if ($link) {
                $link->delete();
            }
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
     * @throws Exception
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
     * @throws Exception
     */
    public static function getAllTaxes(): array
    {
        if (!empty(self::$all_taxes)) {
            return self::$all_taxes;
        }

        $adb = PearDatabase::getInstance();
        $result = $adb->pquery('SELECT tax_id FROM df_taxes WHERE deleted != 1');
        $taxes = [];

        while ($row = $adb->fetchByAssoc($result)) {
            $taxId = $row['tax_id'];
            $taxes[$taxId] = self::getInstanceById($taxId);
        }

        self::$all_taxes = $taxes;

        return $taxes;
    }

    /**
     * @param string $taxName
     *
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
     *
     * @return self|false
     * @throws Exception
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
     * @throws Exception
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
     * @return float
     */
    public function getPercentage(): float
    {
        return (float)$this->get('percentage');
    }

    /**
     * @return array|mixed|string
     */
    public function getRegionsInfo()
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
            'tax_label'   => $this->get('tax_label'),
            'percentage'  => $this->get('percentage'),
            'method'      => $this->get('method'),
            'compound_on' => $this->get('compound_on'),
            'regions'     => $this->get('regions'),
            'deleted'     => $this->get('deleted'),
            'active'      => $this->get('active'),
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
     * @throws Exception
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
     *
     * @return void
     */
    public function setLabel($value): void
    {
        $this->set('tax_label', $value);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function delete(): void
    {
        $this->set('deleted', 1);
        $this->set('active', 0);
        $this->save();
    }

    /**
     * @param $taxId
     *
     * @return bool
     */
    public function isSelectedCompoundOn($taxId)
    {
        return in_array($taxId, $this->getTaxesOnCompound());
    }

    /**
     * @throws Exception
     */
    public function migrateData()
    {
        $this->retrieveDB();
        $columnNames = array_merge(
            $this->db->getColumnNames('vtiger_taxregions'),
            $this->db->getColumnNames('vtiger_inventorytaxinfo'),
            $this->db->getColumnNames('vtiger_producttaxrel'),
        );
        $regions = [];
        $taxes = [];

        if (empty($columnNames)) {
            return;
        }

        $regionResult = $this->db->pquery('SELECT * FROM vtiger_taxregions');

        while ($row = $this->db->fetchByAssoc($regionResult)) {
            $regionId = $row['regionid'];
            $regionName = decode_html($row['name']);
            $region = Core_TaxRegion_Model::getInstance($regionName);
            $region->setName($regionName);
            $region->save();

            $regions[$regionId] = $region->getId();
        }

        $taxResult = $this->db->pquery('SELECT * FROM vtiger_inventorytaxinfo ORDER BY method DESC ');

        while ($row = $this->db->fetchByAssoc($taxResult)) {
            $taxId = $row['taxid'];
            $taxName = decode_html($row['taxlabel']);

            $tax = Core_Tax_Model::getInstance($taxName);
            $tax->setName($taxName);
            $tax->set('percentage', decode_html($row['percentage']));
            $tax->set('active', 1 === intval($row['deleted']) ? 0 : 1);
            $tax->set('deleted', 0);
            $tax->set('method', $row['method']);
            $tax->set('regions', json_encode($this->migrateRegions($row, $regions)));
            $tax->set('compound_on', json_encode($this->migrateCompoundOn($row, $taxes)));
            $tax->save();

            $taxes[$taxId] = $tax->getId();
        }

        $recordResult = $this->db->pquery('SELECT * FROM vtiger_producttaxrel');

        while ($row = $this->db->fetchByAssoc($recordResult)) {
            $recordId = $row['productid'];
            $taxId = $taxes[$row['taxid']];
            $taxPercentage = $row['taxpercentage'];
            $regionInfo = json_decode(decode_html($row['regions']), true);

            $record = Core_TaxRecord_Model::getInstance($recordId);
            $record->set('record_id', $recordId);
            $record->set('tax_id', $taxId);
            $record->set('percentage', $taxPercentage);
            $record->set('region_id', null);
            $record->retrieveId();
            $record->save();

            if (!empty($regionInfo) && !empty($regionInfo[0]['list'])) {
                foreach ($regionInfo as $region) {
                    foreach ($region['list'] as $regionId) {
                        $regionId = $regions[$regionId];

                        $record->set('percentage', $region['value']);
                        $record->set('region_id', $regionId);
                        $record->retrieveId();
                        $record->save();
                    }
                }
            }
        }
    }

    /**
     * @param array $data
     * @param array $regions
     *
     * @return array
     */
    public function migrateRegions(array $data, array $regions): array
    {
        $values = json_decode(decode_html($data['regions']), true);
        $newValues = [];

        foreach ($values as $value) {
            if (empty($value['list'])) {
                continue;
            }

            $newRegionIds = [];

            foreach ($value['list'] as $regionListId) {
                $newRegionIds[] = $regions[$regionListId];
            }

            foreach ($newRegionIds as $newRegionId) {
                $newValues[] = [
                    'region_id' => $newRegionId,
                    'value'     => $value['value'],
                ];
            }
        }

        return $newValues;
    }

    /**
     * @param array $data
     * @param array $taxes
     *
     * @return array
     */
    public function migrateCompoundOn(array $data, array $taxes): array
    {
        $values = (array)json_decode(decode_html($data['compoundon']), true);
        $newValues = [];

        foreach ($values as $value) {
            $newValues[] = $taxes[$value];
        }

        return $newValues;
    }

    /**
     * @throws Exception
     */
    public function getRegions(): array
    {
        if (!empty($this->regions)) {
            return $this->regions;
        }

        foreach ($this->getRegionsInfo() as $regionInfo) {
            $id = (int)$regionInfo['region_id'];
            $region = $this->getRegion($id);

            if ($region) {
                $region->setPercentage($regionInfo['value']);

                $this->regions[$id] = $region;
            }
        }

        return $this->regions;
    }

    /**
     * @param int $id
     *
     * @return bool|object
     * @throws Exception
     */
    public function getRegion(int $id): bool|object
    {
        return Core_TaxRegion_Model::getInstanceById($id);
    }

    public function setPercentage($value)
    {
        $this->set('percentage', $value);
    }

    /**
     * @return bool
     */
    public function isActiveForRecord(): bool
    {
        return $this->activeForRecord;
    }

    /**
     * @param bool $value
     *
     * @return void
     */
    public function setActiveForRecord(bool $value): void
    {
        $this->activeForRecord = $value;
    }

    /**
     * @throws Exception
     */
    public function updateRecordTaxes(): void
    {
        $this->retrieveDB();
        $result = $this->db->pquery('SELECT record_id FROM df_taxes_records WHERE tax_id=? AND region_id IS NULL', [$this->getId()]);

        while ($row = $this->db->fetchByAssoc($result)) {
            $recordId = $row['record_id'];
            $record = Core_TaxRecord_Model::getInstance($recordId);
            $record->set('record_id', $recordId);
            $record->set('tax_id', $this->getId());
            $record->set('region_id', null);
            $record->set('percentage', $this->getPercentage());
            $record->retrieveId();
            $record->save();

            foreach ($this->getRegions() as $region) {
                $record->set('percentage', $region->getPercentage());
                $record->set('region_id', $region->getId());
                $record->retrieveId();
                $record->save();
            }
        }

        $this->deleteUnusedRegions();
    }

    /**
     * @throws Exception
     */
    public function deleteUnusedRegions(): void
    {
        $regionIds = array_keys($this->getRegions());
        $this->db->pquery(
            'DELETE FROM df_taxes_records WHERE tax_id=? AND region_id IS NOT NULL AND region_id NOT IN (' . generateQuestionMarks($regionIds) . ')',
            [$this->getId(), $regionIds]
        );
    }
}