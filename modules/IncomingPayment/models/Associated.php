<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

/**
 * Stores which document a payment is applied to and with which amount (df_incomingpayment_associated).
 */
class IncomingPayment_Associated_Model extends Core_DatabaseData_Model
{
    protected string $table = IncomingPayment_Config_Helper::TABLE_ASSOCIATED;
    protected string $tableId = 'id';

    /**
     * Returns a configured table object for the associated-documents table.
     *
     * @return self
     */
    private function getAssociatedTable(): self
    {
        return $this->getTable($this->table, $this->tableId);
    }

    /**
     * Creates an instance with an initialised database connection.
     *
     * @return self
     */
    public static function getInstance(): self
    {
        return (new self())->retrieveDB();
    }

    /**
     * Returns the ids of the documents a payment is applied to.
     *
     * @param int $paymentId Payment record id
     * @return array
     */
    public function fetchRecordIds(int $paymentId): array
    {
        $table = $this->getAssociatedTable();
        $result = $table->selectResult(['record_id'], ['payment_id' => $paymentId]);
        $recordIds = [];

        while ($row = $table->getDB()->fetchByAssoc($result)) {
            $recordIds[] = (int)$row['record_id'];
        }

        return $recordIds;
    }

    /**
     * Returns the related list query of the documents of one module that a payment is applied to.
     * Vtiger_Module_Model::getRelationQuery() keeps only the text up to a second FROM, so it must not contain a subquery.
     *
     * @param int    $paymentId      Payment record id
     * @param string $documentModule Document module name
     * @return string
     */
    public function getDocumentsRelationQuery(int $paymentId, string $documentModule): string
    {
        $document = CRMEntity::getInstance($documentModule);
        vtlib_setup_modulevars($documentModule, $document);
        $query = 'SELECT vtiger_crmentity.*, ' . $document->table_name . '.*
            FROM ' . $document->table_name . '
            INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = ' . $document->table_name . '.' . $document->table_index;

        foreach ($document->tab_name_index as $tableName => $tableIndex) {
            if (in_array($tableName, [$document->table_name, 'vtiger_crmentity'], true)) {
                continue;
            }

            $join = in_array($tableName, $document->tab_name_left_join, true) ? ' LEFT JOIN ' : ' INNER JOIN ';
            $query .= $join . $tableName . ' ON ' . $tableName . '.' . $tableIndex . ' = vtiger_crmentity.crmid';
        }

        return $query . '
            INNER JOIN ' . $this->table . ' ON ' . $this->table . '.record_id = vtiger_crmentity.crmid
            LEFT JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.assigned_user_id
            LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.assigned_user_id
            WHERE vtiger_crmentity.deleted = 0 AND ' . $this->table . '.payment_id = ' . $paymentId;
    }

    /**
     * Stores the documents a payment is applied to with their amounts, replacing the previous associations.
     *
     * @param int   $paymentId Payment record id
     * @param array $amounts   [document id => amount applied to the document in the payment currency]
     * @return void
     */
    public function saveForPayment(int $paymentId, array $amounts): void
    {
        if (!$amounts) {
            $this->deleteByPayment($paymentId);

            return;
        }

        $table = $this->getAssociatedTable();
        $recordIds = array_map('intval', array_keys($amounts));
        $this->getDB()->pquery(
            'DELETE FROM ' . $this->table . ' WHERE payment_id = ? AND record_id NOT IN (' . generateQuestionMarks($recordIds) . ')',
            array_merge([$paymentId], $recordIds)
        );

        foreach ($amounts as $recordId => $amount) {
            $existing = $table->selectData(['id'], ['payment_id' => $paymentId, 'record_id' => (int)$recordId]);

            if ($existing) {
                $table->updateData(['partial_amount' => $amount], ['id' => (int)$existing['id']]);
            } else {
                $table->insertData(['payment_id' => $paymentId, 'record_id' => (int)$recordId, 'partial_amount' => $amount]);
            }
        }
    }

    /**
     * Removes every association of a payment.
     *
     * @param int $paymentId Payment record id
     * @return void
     */
    public function deleteByPayment(int $paymentId): void
    {
        $this->getAssociatedTable()->deleteData(['payment_id' => $paymentId]);
    }
}
