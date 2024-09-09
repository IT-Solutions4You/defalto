<?php
/*
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Core_TaxRecord_Model extends Core_DatabaseData_Model
{
    protected array $columns = ['record_id', 'tax_id', 'percentage', 'region_id'];
    protected int $recordId = 0;
    protected string $table = 'df_taxes_records';
    protected string $tableId = 'tax_record_id';
    protected string $tableName = 'tax_id';

    /**
     * @throws AppException
     */
    public function createTables(): void
    {
        $this->getTaxRecordTable()
            ->createTable()
            ->createColumn('record_id', 'int(19)')
            ->createColumn('tax_id', 'int(19)')
            ->createColumn('percentage', 'decimal(7,3)')
            ->createColumn('region_id', 'int(19)')
            ->createKey('CONSTRAINT `fk_taxes_records_record_id` FOREIGN KEY IF NOT EXISTS (record_id) REFERENCES vtiger_crmentity(crmid) ON DELETE CASCADE')
            ->createKey('CONSTRAINT `fk_taxes_records_tax_id` FOREIGN KEY IF NOT EXISTS (tax_id) REFERENCES df_taxes(tax_id) ON DELETE CASCADE')
            ->createKey('CONSTRAINT `fk_taxes_records_region_id` FOREIGN KEY IF NOT EXISTS (region_id) REFERENCES df_taxes_regions(region_id) ON DELETE CASCADE');
    }

    /**
     * @throws AppException
     */
    public function deleteTax(int $recordId, int $taxId): void
    {
        $this->getTaxRecordTable()->deleteData(['record_id' => $recordId, 'tax_id' => $taxId]);
    }

    public static function getInstance($recordId = 0): self
    {
        $instance = new self();
        $instance->setRecordId((int)$recordId);

        return $instance;
    }

    public function getRecordId(): int
    {
        return $this->recordId;
    }

    public function getSaveParams(): array
    {
        return [
            'percentage' => $this->get('percentage'),
            'record_id' => $this->get('record_id'),
            'tax_id' => $this->get('tax_id'),
            'region_id' => $this->get('region_id') ?? null,
        ];
    }

    public function getTaxRecordTable(): self
    {
        return $this->getTable($this->table, $this->tableId);
    }

    /**
     * @throws AppException
     */
    public function getTaxes(): array
    {
        $taxesModels = Core_Tax_Model::getAllTaxes();
        $taxesInfo = $this->getTaxesInfo();
        $taxes = [];

        foreach ($taxesModels as $tax) {
            /** @var Core_Tax_Model $tax */
            if (!$tax->isActive()) {
                continue;
            }

            $taxId = $tax->getId();

            if (isset($taxesInfo[$taxId]['default'])) {
                $tax->setPercentage($taxesInfo[$taxId]['default']);
                $tax->setActiveForRecord(true);
            }

            $regions = array_filter($tax->getRegions());

            foreach ($regions as $region) {
                $regionId = $region->getId();

                if (isset($taxesInfo[$taxId][$regionId])) {
                    $region->setPercentage($taxesInfo[$taxId][$regionId]);
                }
            }

            $taxes[$taxId] = $tax;
        }

        return $taxes;
    }

    /**
     * @throws AppException
     */
    public static function getActiveTaxes($recordId): array
    {
        $taxes = [];
        $taxRecord = self::getInstance($recordId);
        $taxModels = $taxRecord->getTaxes();

        foreach ($taxModels as $taxModel) {
            $taxId = $taxModel->getId();

            if ($taxModel->isActive()) {
                $taxes[$taxId] = $taxModel;
            }
        }

        return $taxes;
    }

    /**
     * @throws AppException
     */
    public static function getActiveTaxesForRecord($recordId): array
    {
        $taxRecord = self::getInstance($recordId);
        $taxModels = $taxRecord->getTaxes();
        $taxes = [];

        foreach ($taxModels as $taxModel) {
            $taxId = $taxModel->getId();

            if ($taxModel->isActiveForRecord()) {
                $taxes[$taxId] = $taxModel;
            }
        }

        return $taxes;
    }


    public function getTaxesInfo(): array
    {
        $recordId = $this->getRecordId();
        $info = [];
        $this->retrieveDB();
        $result = $this->db->pquery('SELECT * FROM df_taxes_records WHERE record_id=?', [$recordId]);

        while ($row = $this->db->fetchByAssoc($result)) {
            $taxId = $row['tax_id'];
            $regionId = $row['region_id'] ?? 'default';

            $info[$taxId][$regionId] = $row['percentage'];
        }

        return $info;
    }

    /**
     * @throws AppException
     * @throws Exception
     */
    public function retrieveId(): void
    {
        $this->retrieveDB();
        $sql = 'SELECT tax_record_id FROM df_taxes_records WHERE record_id=? AND tax_id=? ';
        $params = [$this->get('record_id'), $this->get('tax_id')];

        if (!$this->isEmpty('region_id')) {
            $sql .= 'AND region_id=?';
            $params[] = $this->get('region_id');
        } else {
            $sql .= 'AND region_id IS NULL';
        }

        $result = $this->db->pquery($sql, $params);
        $id = (int)$this->db->query_result($result, 0, 'tax_record_id');

        $this->setId($id);
    }

    /**
     * @throws AppException
     */
    public function saveFromRequest(Vtiger_Request $request): bool
    {
        if ($request->isEmpty('taxes_data')) {
            return false;
        }

        $taxesData = $request->get('taxes_data');
        $recordId = $this->getRecordId();
        $this->set('record_id', $recordId);

        foreach ($taxesData as $taxId => $taxData) {
            $this->set('tax_id', (int)$taxId);
            $this->set('region_id', null);
            $this->set('percentage', $taxData['percentage']);

            if (!empty($taxData['checked'])) {
                $this->retrieveId();
                $this->save();

                if (!empty($taxData['regions'])) {
                    foreach ($taxData['regions'] as $regionId => $regionValue) {
                        if (empty((int)$regionId)) {
                            continue;
                        }

                        $this->set('region_id', (int)$regionId);
                        $this->set('percentage', $regionValue);
                        $this->retrieveId();
                        $this->save();
                    }
                }
            } else {
                $this->deleteTax($recordId, $taxId);
            }
        }

        return true;
    }

    public function setRecordId(int $value): void
    {
        $this->recordId = $value;
    }
}