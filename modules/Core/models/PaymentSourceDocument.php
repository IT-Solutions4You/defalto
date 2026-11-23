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
 * Payments of a source document counted on the documents created from it, as Cashflow did: the received payments
 * of a sales order count on its invoices (Invoice.salesorder_id). The payments stay linked to the source document.
 * The net received amount of the source is split over its target documents with the same organisation by due date
 * (the earliest first, then by creation): each target gets at most what its own payments leave open, the last one
 * gets the rest, so an overpayment stays on the last target and nothing is counted twice.
 */
class Core_PaymentSourceDocument_Model
{
    /**
     * Target document modules and the fields that point to their source documents: [target => [field => source]].
     */
    public const SOURCE_FIELDS = [
        'Invoice' => ['salesorder_id' => 'SalesOrder'],
    ];

    /**
     * Field holding the organisation; only targets with the organisation of the source get its payments.
     */
    public const ACCOUNT_FIELD = 'account_id';

    /**
     * Source documents whose targets are being recalculated, so a target save does not start it again.
     *
     * @var array<int, true>
     */
    private static array $processing = [];

    /**
     * Returns a new instance.
     *
     * @return self
     */
    public static function getInstance(): self
    {
        return new self();
    }

    /**
     * Tells whether a module is a source document module, e.g. SalesOrder.
     *
     * @param string $moduleName Module name
     * @return bool
     */
    public static function isSourceModule(string $moduleName): bool
    {
        foreach (self::SOURCE_FIELDS as $sources) {
            if (in_array($moduleName, $sources, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns the existing source documents of a target document: [source record id => source module].
     *
     * @param Vtiger_Record_Model $targetRecord Target document
     * @return array<int, string>
     */
    public function getSourceRecordIds(Vtiger_Record_Model $targetRecord): array
    {
        $sourceIds = [];

        foreach (self::SOURCE_FIELDS[$targetRecord->getModuleName()] ?? [] as $fieldName => $sourceModule) {
            $sourceId = (int)$targetRecord->get($fieldName);

            if ($sourceId > 0 && isRecordExists($sourceId) && getSalesEntityType($sourceId) === $sourceModule) {
                $sourceIds[$sourceId] = $sourceModule;
            }
        }

        return $sourceIds;
    }

    /**
     * Returns the amount of the source payments that falls on a target document, in the target currency.
     *
     * @param Vtiger_Record_Model $targetRecord Target document
     * @param bool|null           $isReceived   true for received payments only, null for all payments
     * @return float
     * @throws Exception
     */
    public function getAllocatedAmount(Vtiger_Record_Model $targetRecord, ?bool $isReceived): float
    {
        $amount = 0.0;

        foreach ($this->getSourceAllocations($targetRecord, $isReceived) as $allocation) {
            $amount += $allocation['amount'];
        }

        return $amount;
    }

    /**
     * Returns the source payments that fall on a target document, one row per source document with the amount
     * in the target currency.
     *
     * @param Vtiger_Record_Model $targetRecord Target document
     * @param bool|null           $isReceived   true for received payments only, null for all payments
     * @return array<int, array{id: int, module: string, amount: float}>
     * @throws Exception
     */
    public function getSourceAllocations(Vtiger_Record_Model $targetRecord, ?bool $isReceived): array
    {
        $targetId = (int)$targetRecord->getId();
        $allocations = [];

        foreach ($this->getSourceRecordIds($targetRecord) as $sourceId => $sourceModule) {
            $amount = $this->getAllocation($sourceModule, $sourceId, $isReceived)[$targetId] ?? 0.0;

            if (!Core_PaymentCalculation_Helper::isSameAmount($amount, 0.0)) {
                $allocations[] = ['id' => $sourceId, 'module' => $sourceModule, 'amount' => $amount];
            }
        }

        return $allocations;
    }

    /**
     * Splits the net payments of a source document over its target documents by due date.
     *
     * @param string    $sourceModule Source document module
     * @param int       $sourceId     Source document record id
     * @param bool|null $isReceived   true for received payments only, null for all payments
     * @return array<int, float> [target record id => amount in the target currency]
     * @throws Exception
     */
    public function getAllocation(string $sourceModule, int $sourceId, ?bool $isReceived): array
    {
        if (!isRecordExists($sourceId)) {
            return [];
        }

        $balanceModel = Core_PaymentDocumentBalance_Model::getInstance();
        $sourceRecord = Vtiger_Record_Model::getInstanceById($sourceId, $sourceModule);
        $sourceRate = Core_PaymentCalculation_Helper::getRecordRate($sourceRecord);
        $remaining = $balanceModel->getOwnNetAmount($sourceRecord, $isReceived);
        $targets = $this->getTargetRecords($sourceRecord);
        $lastIndex = count($targets) - 1;
        $allocation = [];

        foreach ($targets as $index => $targetRecord) {
            if ($remaining <= Core_PaymentCalculation_Helper::EPSILON) {
                break;
            }

            $targetRate = Core_PaymentCalculation_Helper::getRecordRate($targetRecord);
            $available = Core_PaymentCalculation_Helper::convertBetweenCurrencies($remaining, $sourceRate, $targetRate);
            $amount = $index === $lastIndex ? $available : min($available, $this->getOpenAmount($targetRecord, $isReceived));

            if ($amount <= Core_PaymentCalculation_Helper::EPSILON) {
                continue;
            }

            $allocation[(int)$targetRecord->getId()] = $amount;
            $remaining -= Core_PaymentCalculation_Helper::convertBetweenCurrencies($amount, $targetRate, $sourceRate);
        }

        return $allocation;
    }

    /**
     * Returns the record numbers of the target documents that count the payments of the given source documents,
     * e.g. the invoices of sales orders being deleted.
     *
     * @param string $sourceModule Source document module
     * @param int[]  $sourceIds    Source document record ids
     * @return string[]
     * @throws Exception
     */
    public function getTargetNumbers(string $sourceModule, array $sourceIds): array
    {
        $numbers = [];

        foreach ($sourceIds as $sourceId) {
            if (!isRecordExists((int)$sourceId) || getSalesEntityType((int)$sourceId) !== $sourceModule) {
                continue;
            }

            foreach ($this->getTargetRecords(Vtiger_Record_Model::getInstanceById((int)$sourceId, $sourceModule)) as $targetRecord) {
                $numbers[] = Core_PaymentValues_Helper::getRecordNumber($targetRecord);
            }
        }

        return array_values(array_unique($numbers));
    }

    /**
     * Recalculates the target documents of a source document, e.g. after a payment of the source changed or the
     * source was deleted (its payments then no longer count). Targets of another organisation are recalculated too,
     * because they may have lost the payments when their organisation changed. The core clears the source field of
     * the targets when the source is deleted and keeps their ids for the restore (vtiger_relatedlists_rb), so the
     * targets of a deleted source are taken from there.
     *
     * @param string $sourceModule Source document module
     * @param int    $sourceId     Source document record id
     * @return void
     * @throws Exception
     */
    public function recalculateTargets(string $sourceModule, int $sourceId): void
    {
        if (isset(self::$processing[$sourceId])) {
            return;
        }

        self::$processing[$sourceId] = true;

        try {
            $balanceModel = Core_PaymentDocumentBalance_Model::getInstance();

            $isSourceDeleted = !isRecordExists($sourceId);

            foreach (self::SOURCE_FIELDS as $targetModule => $sourceFields) {
                foreach (array_keys($sourceFields, $sourceModule, true) as $fieldName) {
                    $targetIds = $this->retrieveTargetIds($targetModule, $fieldName, $sourceId);

                    if ($isSourceDeleted) {
                        $targetIds = array_merge($targetIds, $this->retrieveUnlinkedTargetIds($targetModule, $fieldName, $sourceId));
                    }

                    foreach (array_unique($targetIds) as $targetId) {
                        if (isRecordExists($targetId)) {
                            $balanceModel->recalculateDocument($targetModule, $targetId);
                        }
                    }
                }
            }
        } finally {
            unset(self::$processing[$sourceId]);
        }
    }

    /**
     * Recalculates the other targets of the source documents of a target document after the target was saved,
     * deleted or restored, also of the source it pointed to before the save.
     *
     * @param string $targetModule Target document module
     * @param int    $targetId     Target document record id
     * @return void
     * @throws Exception
     */
    public function recalculateSiblings(string $targetModule, int $targetId): void
    {
        $sourceFields = self::SOURCE_FIELDS[$targetModule] ?? [];

        if (!$sourceFields) {
            return;
        }

        require_once 'data/VTEntityDelta.php';

        $sourceIds = [];
        $entityDelta = new VTEntityDelta();

        foreach ($sourceFields as $fieldName => $sourceModule) {
            $sourceIds[(int)$this->retrieveFieldValue($targetModule, $targetId, $fieldName)] = $sourceModule;

            if ($entityDelta->hasChanged($targetModule, $targetId, $fieldName)) {
                $sourceIds[(int)$entityDelta->getOldValue($targetModule, $targetId, $fieldName)] = $sourceModule;
            }
        }

        foreach ($sourceIds as $sourceId => $sourceModule) {
            if ($sourceId > 0 && getSalesEntityType($sourceId) === $sourceModule) {
                $this->recalculateTargets($sourceModule, $sourceId);
            }
        }
    }

    /**
     * Returns the target documents of a source document with its organisation, in allocation order:
     * due date (the earliest first, without a due date last), then creation time.
     *
     * @param Vtiger_Record_Model $sourceRecord Source document
     * @return Vtiger_Record_Model[]
     * @throws Exception
     */
    protected function getTargetRecords(Vtiger_Record_Model $sourceRecord): array
    {
        $sourceModule = $sourceRecord->getModuleName();
        $accountId = (int)$sourceRecord->get(self::ACCOUNT_FIELD);
        $targets = [];

        if ($accountId <= 0) {
            return [];
        }

        foreach (self::SOURCE_FIELDS as $targetModule => $sourceFields) {
            foreach (array_keys($sourceFields, $sourceModule, true) as $fieldName) {
                foreach ($this->retrieveTargetIds($targetModule, $fieldName, (int)$sourceRecord->getId()) as $targetId) {
                    $targetRecord = Vtiger_Record_Model::getInstanceById($targetId, $targetModule);

                    if ((int)$targetRecord->get(self::ACCOUNT_FIELD) === $accountId) {
                        $targets[] = $targetRecord;
                    }
                }
            }
        }

        return $targets;
    }

    /**
     * Returns the amount a target document still misses after its own payments, in its currency; at least 0.
     *
     * @param Vtiger_Record_Model $targetRecord Target document
     * @param bool|null           $isReceived   true for received payments only, null for all payments
     * @return float
     * @throws Exception
     */
    protected function getOpenAmount(Vtiger_Record_Model $targetRecord, ?bool $isReceived): float
    {
        $targetModule = $targetRecord->getModuleName();
        $payingModule = Core_PaymentDocumentBalance_Model::getPayingModule($targetModule);

        if ($payingModule === '') {
            return 0.0;
        }

        $config = Core_PaymentConfig_Helper::getInstance($payingModule)->getIntegrationModel()->retrieveByModule($targetModule);
        $totalField = (string)($config['total_field'] ?? '');
        $total = $totalField !== '' ? Core_PaymentCalculation_Helper::parseAmount($targetRecord->get($totalField)) : 0.0;

        return max(0.0, $total - Core_PaymentDocumentBalance_Model::getInstance()->getOwnNetAmount($targetRecord, $isReceived));
    }

    /**
     * Returns the ids of the not deleted target documents pointing to a source document through a field,
     * ordered by due date (without a due date last) and creation time.
     *
     * @param string $targetModule Target document module
     * @param string $fieldName    Field of the target pointing to the source
     * @param int    $sourceId     Source document record id
     * @return int[]
     */
    protected function retrieveTargetIds(string $targetModule, string $fieldName, int $sourceId): array
    {
        $moduleModel = Vtiger_Module_Model::getInstance($targetModule);
        $field = $moduleModel ? $moduleModel->getField($fieldName) : false;

        if (!$field) {
            return [];
        }

        $db = PearDatabase::getInstance();
        $table = (string)$moduleModel->get('basetable');
        $tableId = (string)$moduleModel->get('basetableid');
        $dueDateField = $moduleModel->getField(Core_PaymentIntegrationFields_Helper::getFirstField($moduleModel, Core_PaymentConfig_Helper::DOCUMENT_DUE_DATE_FIELDS));
        $order = 'vtiger_crmentity.createdtime, vtiger_crmentity.crmid';

        if ($dueDateField && $dueDateField->get('table') === $table) {
            $dueDateColumn = $table . '.' . $dueDateField->get('column');
            $order = $dueDateColumn . ' IS NULL, ' . $dueDateColumn . ', ' . $order;
        }

        $result = $db->pquery(
            'SELECT ' . $table . '.' . $tableId . ' AS id FROM ' . $table
            . ' INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = ' . $table . '.' . $tableId
            . ' WHERE vtiger_crmentity.deleted = 0 AND ' . $field->get('table') . '.' . $field->get('column') . ' = ?'
            . ' ORDER BY ' . $order,
            [$sourceId]
        );
        $ids = [];

        while ($row = $db->fetchByAssoc($result)) {
            $ids[] = (int)$row['id'];
        }

        return $ids;
    }

    /**
     * Returns the target documents whose source field the core cleared when the source was deleted
     * (Core_Relation_Model::saveDependencies() keeps them in vtiger_relatedlists_rb for the restore).
     *
     * @param string $targetModule Target document module
     * @param string $fieldName    Field of the target pointing to the source
     * @param int    $sourceId     Deleted source document record id
     * @return int[]
     */
    protected function retrieveUnlinkedTargetIds(string $targetModule, string $fieldName, int $sourceId): array
    {
        $moduleModel = Vtiger_Module_Model::getInstance($targetModule);
        $field = $moduleModel ? $moduleModel->getField($fieldName) : false;

        if (!$field) {
            return [];
        }

        $db = PearDatabase::getInstance();
        $result = $db->pquery(
            'SELECT related_crm_ids FROM vtiger_relatedlists_rb WHERE entityid = ? AND rel_table = ? AND rel_column = ?',
            [$sourceId, $field->get('table'), $field->get('column')]
        );
        $ids = [];

        while ($row = $db->fetchByAssoc($result)) {
            foreach (explode(',', (string)$row['related_crm_ids']) as $id) {
                if ((int)$id > 0) {
                    $ids[] = (int)$id;
                }
            }
        }

        return $ids;
    }

    /**
     * Reads a field of a record directly, also of a deleted one.
     *
     * @param string $moduleName Module name
     * @param int    $recordId   Record id
     * @param string $fieldName  Field name
     * @return string
     */
    protected function retrieveFieldValue(string $moduleName, int $recordId, string $fieldName): string
    {
        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
        $field = $moduleModel ? $moduleModel->getField($fieldName) : false;

        if (!$field || $field->get('table') !== $moduleModel->get('basetable')) {
            return '';
        }

        $db = PearDatabase::getInstance();
        $result = $db->pquery(
            'SELECT ' . $field->get('column') . ' AS value FROM ' . $moduleModel->get('basetable') . ' WHERE ' . $moduleModel->get('basetableid') . ' = ?',
            [$recordId]
        );

        return (string)$db->query_result($result, 0, 'value');
    }
}
