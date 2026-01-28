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
}