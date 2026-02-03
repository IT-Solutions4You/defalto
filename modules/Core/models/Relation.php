<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Core_Relation_Model extends Vtiger_Base_Model
{
    /**
     * @throws Exception
     */
    public static function saveEntityRelation(int $record, string $module, int $relationRecord, string $relationModule): bool
    {
        $table = (new self())->getEntityRelationTable();
        $insertData = ['crmid' => $record, 'module' => $module, 'relcrmid' => $relationRecord, 'relmodule' => $relationModule];
        $data = $table->selectData(['crmid', 'relcrmid'], $insertData);

        if (!empty($data['crmid'])) {
            return false;
        }

        $table->insertData($insertData);

        return true;
    }

    public function getEntityRelationTable(): Core_DatabaseData_Model
    {
        return Core_DatabaseData_Model::getTableInstance('vtiger_crmentityrel');
    }

    /**
     * @throws Exception
     */
    public static function deleteEntityRelation(int $record, string $module, int $relationRecord, string $relationModule): bool
    {
        $table = (new self())->getEntityRelationTable();
        $table->deleteData(['crmid' => $record, 'module' => $module, 'relcrmid' => $relationRecord, 'relmodule' => $relationModule]);
        $table->deleteData(['crmid' => $relationRecord, 'module' => $relationModule, 'relcrmid' => $record, 'relmodule' => $module]);

        return true;
    }

    public static function saveDependencies($id, $field, $referenceTable, $referenceIndex): bool
    {
        $db = PearDatabase::getInstance();
        $select = $db->pquery(
            sprintf('SELECT %s FROM %s WHERE %s=?', $referenceIndex, $referenceTable, $field),
            [$id]
        );
        $ids = [];

        while ($row = $db->fetchByAssoc($select)) {
            $ids[] = $row[$referenceIndex];
        }

        if(empty($ids)) {
            return false;
        }

        $db->pquery(
            'INSERT INTO vtiger_relatedlists_rb (entityid, action, rel_table, rel_column, ref_column, related_crm_ids) VALUES (?,?,?,?,?,?)',
            [$id, RB_RECORD_UPDATED, $referenceTable, $field, $referenceIndex, implode(',', $ids)]
        );
        $db->pquery(
            sprintf('UPDATE %s SET %s=null WHERE %s=?', $referenceTable, $field, $field),
            [$id]
        );

        return true;
    }

    public static function saveEntityDependencies($id, $table, $tableIndex, $relationTable, $relationIndex, $relationColumn): bool
    {
        $db = PearDatabase::getInstance();
        $tableKey = $table . '.' . $tableIndex;
        $relationKey = $relationTable . '.' . $relationIndex;
        $columnKey = $relationTable . '.' . $relationColumn;
        $select = sprintf(
            'SELECT %s FROM vtiger_crmentity INNER JOIN %s ON %s=vtiger_crmentity.crmid INNER JOIN %s ON %s=%s WHERE vtiger_crmentity.deleted=0 AND %s=?',
            $relationKey,
            $relationTable,
            $relationKey,
            $table,
            $tableKey,
            $columnKey,
            $columnKey,
        );
        $select = $db->pquery($select, [$id]);
        $ids = [];

        while ($row = $db->fetchByAssoc($select)) {
            $ids[] = $row[$relationIndex];
        }

        $ids = implode(',', $ids);
        $db->pquery(
            'INSERT INTO vtiger_relatedlists_rb VALUES (?,?,?,?,?,?)',
            [$id, RB_RECORD_UPDATED, 'vtiger_crmentity', 'deleted', 'crmid', $ids]
        );
        $db->pquery(
            sprintf('UPDATE vtiger_crmentity SET deleted = 1 WHERE crmid IN (%s)', $ids)
        );

        return true;
    }
}