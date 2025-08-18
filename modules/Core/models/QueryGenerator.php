<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Core_QueryGenerator_Model extends EnhancedQueryGenerator
{
    public const NOT_EMPTY = 'ny';
    public const EMPTY = 'y';

    public int $limit = 0;

    public static function getInstance($module, $user = false): self
    {
        if (!$user) {
            $user = Users_Record_Model::getCurrentUserModel();
        }

        $query = new self($module, $user);
        $query->setFields(['id']);

        return $query;
    }

    public function setLimit($value): void
    {
        $this->limit = $value;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * @return array
     */
    public function getRecords(): array
    {
        $records = [];
        $adb = PearDatabase::getInstance();
        $query = $this->getQuery() . sprintf(' LIMIT %s', $this->getLimit());
        $result = $adb->pquery($query);
        $index = $this->getBaseTableIndex();

        while ($row = $adb->fetchByAssoc($result)) {
            $recordId = (int)$row[$index];
            $records[$recordId] = Vtiger_Record_Model::getInstanceById($recordId);
        }

        return $records;
    }

    public function getBaseTableIndex()
    {
        $baseTable = $this->meta->getEntityBaseTable();
        $moduleTableIndexList = $this->meta->getEntityTableIndexList();

        return $moduleTableIndexList[$baseTable];
    }
}