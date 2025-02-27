<?php
/*
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Core_QueryGenerator_Model extends EnhancedQueryGenerator
{
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