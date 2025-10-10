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
    protected bool $groupByClauseRequired = false;
    protected array $groupByColumns = [];

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

    /**
     * @return string
     */
    public function getGroupByClause(): string
    {
        return ' GROUP BY ' . implode(', ', $this->getGroupByColumns());
    }

    /**
     * @param array $columns
     * @return void
     */
    public function setGroupByColumns(array $columns): self
    {
        $this->groupByColumns = $columns;

        return $this;
    }

    /**
     * @return array
     */
    public function getGroupByColumns(): array
    {
        return $this->groupByColumns;
    }

    /**
     * @return bool
     */
    public function isGroupByClauseRequired(): bool
    {
        return $this->groupByClauseRequired;
    }

    /**
     * @param bool $value
     * @return void
     */
    public function setGroupByClauseRequired(bool $value): self
    {
        $this->groupByClauseRequired = $value;

        return $this;
    }

    public function getQuery(bool $sortClause = false): string
    {
        if (empty($this->query)) {
            $conditionedReferenceFields = [];
            $allFields = array_merge($this->fields, (array)$this->whereFields);
            foreach ($allFields as $fieldName) {
                if (in_array($fieldName, $this->referenceFieldList)) {
                    $moduleList = $this->referenceFieldInfoList[$fieldName];
                    foreach ($moduleList as $module) {
                        if (empty($this->moduleNameFields[$module])) {
                            $meta = $this->getMeta($module);
                        }
                    }
                } elseif (in_array($fieldName, $this->ownerFields)) {
                    $meta = $this->getMeta('Users');
                    $meta = $this->getMeta('Groups');
                }
            }

            $query = "SELECT ";
            $query .= $this->getSelectClauseColumnSQL();
            $query .= $this->getFromClause();
            $query .= $this->getWhereClause();

            if ($this->isGroupByClauseRequired()) {
                $query .= $this->getGroupByClause();
            }

            if ($this->isOrderByClauseRequired()) {
                $query .= $this->getOrderByClause();
            }

            $this->query = $query;

            return $query;
        } else {
            return $this->query;
        }
    }
}