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
 * Payments of a source document counted on the documents created from it, as Cashflow did. The payments stay linked
 * to the source document.
 * - A field on the target (SOURCE_FIELDS): the received payments of a sales order count on its invoices
 *   (Invoice.salesorder_id). The net received amount of the source is split over its target documents with the same
 *   organisation by due date (the earliest first, then by creation): each target gets at most what its own payments
 *   leave open, the last one gets the rest, so an overpayment stays on the last target and nothing is counted twice.
 * - A field on the source (SOURCE_LINK_FIELDS): a proforma invoice points to its tax document and to its final
 *   invoice. The tax document confirms the received advance and counts it up to the proforma total (LINK_RECEIPT).
 *   The final invoice already deducts the proforma total by a minus item (ProformaInvoice extension), so it shows the
 *   advance but counts only what was paid above the proforma total (LINK_DEDUCTED); while the advance is paid, the
 *   final invoice counts as paid when its own balance is within the underpayment threshold.
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
     * The target counts the payments of the source up to the source total (a tax document of a proforma invoice).
     */
    public const LINK_RECEIPT = 'receipt';

    /**
     * The target deducted the source total by an item and counts only the payments above it (a final invoice).
     */
    public const LINK_DEDUCTED = 'deducted';

    /**
     * Source document modules and their fields that point to target documents:
     * [source => [field => [target module, LINK_RECEIPT or LINK_DEDUCTED]]].
     */
    public const SOURCE_LINK_FIELDS = [
        'ProformaInvoice' => [
            'tax_document_id' => ['Invoice', self::LINK_RECEIPT],
            'final_invoice_id' => ['Invoice', self::LINK_DEDUCTED],
        ],
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
        if (isset(self::SOURCE_LINK_FIELDS[$moduleName])) {
            return true;
        }

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
     * counted on the target and the net amount paid on the source, both in the target currency. A final invoice
     * lists the paid advance of its proforma even when nothing of it is counted.
     *
     * @param Vtiger_Record_Model $targetRecord Target document
     * @param bool|null           $isReceived   true for received payments only, null for all payments
     * @return array<int, array{id: int, module: string, amount: float, paid: float, link: string}>
     * @throws Exception
     */
    public function getSourceAllocations(Vtiger_Record_Model $targetRecord, ?bool $isReceived): array
    {
        $targetId = (int)$targetRecord->getId();
        $allocations = [];

        foreach ($this->getSourceRecordIds($targetRecord) as $sourceId => $sourceModule) {
            $amount = $this->getAllocation($sourceModule, $sourceId, $isReceived)[$targetId] ?? 0.0;

            if (!Core_PaymentCalculation_Helper::isSameAmount($amount, 0.0)) {
                $allocations[] = ['id' => $sourceId, 'module' => $sourceModule, 'amount' => $amount, 'paid' => $amount, 'link' => ''];
            }
        }

        foreach ($this->getLinkedSources($targetRecord) as [$sourceRecord, $link]) {
            $allocation = $this->getLinkAllocation($sourceRecord, $link, $targetRecord, $isReceived);

            if (!Core_PaymentCalculation_Helper::isSameAmount($allocation['amount'], 0.0) || !Core_PaymentCalculation_Helper::isSameAmount($allocation['paid'], 0.0)) {
                $allocations[] = $allocation;
            }
        }

        return $allocations;
    }

    /**
     * Returns the payments of a source document that a target linked from its field counts, in the target currency:
     * a tax document counts them up to the source total, a final invoice only the part above the source total,
     * because it deducted the source total by an item.
     *
     * @param Vtiger_Record_Model $sourceRecord Source document (proforma invoice)
     * @param string              $link         LINK_RECEIPT or LINK_DEDUCTED
     * @param Vtiger_Record_Model $targetRecord Target document
     * @param bool|null           $isReceived   true for received payments only, null for all payments
     * @return array{id: int, module: string, amount: float, paid: float, link: string}
     * @throws Exception
     */
    public function getLinkAllocation(Vtiger_Record_Model $sourceRecord, string $link, Vtiger_Record_Model $targetRecord, ?bool $isReceived): array
    {
        $sourceRate = Core_PaymentCalculation_Helper::getRecordRate($sourceRecord);
        $targetRate = Core_PaymentCalculation_Helper::getRecordRate($targetRecord);
        $paid = Core_PaymentDocumentBalance_Model::getInstance()->getOwnNetAmount($sourceRecord, $isReceived);
        $total = $this->getDocumentTotal($sourceRecord);
        $counted = self::LINK_RECEIPT === $link ? min($paid, $total) : $paid - $total;

        return [
            'id' => (int)$sourceRecord->getId(),
            'module' => $sourceRecord->getModuleName(),
            'amount' => Core_PaymentCalculation_Helper::convertBetweenCurrencies(max(0.0, $counted), $sourceRate, $targetRate),
            'paid' => Core_PaymentCalculation_Helper::convertBetweenCurrencies($paid, $sourceRate, $targetRate),
            'link' => $link,
        ];
    }

    /**
     * Tells whether a final invoice is covered by its advances: it deducted at least one proforma invoice and every
     * such proforma is paid. The final invoice then counts as paid while its own balance is within the threshold,
     * also when its total after the deduction is zero.
     *
     * @param Vtiger_Record_Model $targetRecord Target document
     * @return bool
     * @throws Exception
     */
    public function isCoveredByAdvance(Vtiger_Record_Model $targetRecord): bool
    {
        $hasAdvance = false;

        foreach ($this->getLinkedSources($targetRecord) as [$sourceRecord, $link]) {
            if (self::LINK_DEDUCTED !== $link) {
                continue;
            }

            $hasAdvance = true;
            $paid = Core_PaymentDocumentBalance_Model::getInstance()->getOwnNetAmount($sourceRecord, true);

            if (!$this->isDocumentPaid($sourceRecord, $paid)) {
                return false;
            }
        }

        return $hasAdvance;
    }

    /**
     * Returns the not deleted source documents that point to a target through a field of the source, with the kind
     * of the link: [[source record, LINK_RECEIPT or LINK_DEDUCTED], ...].
     *
     * @param Vtiger_Record_Model $targetRecord Target document
     * @return array<int, array{0: Vtiger_Record_Model, 1: string}>
     * @throws Exception
     */
    protected function getLinkedSources(Vtiger_Record_Model $targetRecord): array
    {
        $targetModule = $targetRecord->getModuleName();
        $sources = [];

        foreach (self::SOURCE_LINK_FIELDS as $sourceModule => $fields) {
            if (!vtlib_isModuleActive($sourceModule)) {
                continue;
            }

            foreach ($fields as $fieldName => [$linkedModule, $link]) {
                if ($linkedModule !== $targetModule) {
                    continue;
                }

                foreach ($this->retrieveTargetIds($sourceModule, $fieldName, (int)$targetRecord->getId()) as $sourceId) {
                    $sources[] = [Vtiger_Record_Model::getInstanceById($sourceId, $sourceModule), $link];
                }
            }
        }

        return $sources;
    }

    /**
     * Tells whether a document is paid by the given net amount with the underpayment threshold of its paying module.
     *
     * @param Vtiger_Record_Model $recordModel Document record
     * @param float               $paid        Net paid amount in the document currency
     * @return bool
     * @throws Exception
     */
    protected function isDocumentPaid(Vtiger_Record_Model $recordModel, float $paid): bool
    {
        $payingModule = Core_PaymentDocumentBalance_Model::getPayingModule($recordModel->getModuleName());

        if ($payingModule === '') {
            return false;
        }

        $total = $this->getDocumentTotal($recordModel);
        $threshold = Core_PaymentConfig_Helper::getInstance($payingModule)->getSettingModel()->getUnderpaymentThreshold();

        return Core_PaymentDocument_Helper::isPaid($paid, $total - $paid, $total, $threshold);
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

            foreach ($this->getLinkedTargetIds($sourceModule, (int)$sourceId) as $targetId => $targetModule) {
                if (isRecordExists($targetId)) {
                    $numbers[] = Core_PaymentValues_Helper::getRecordNumber(Vtiger_Record_Model::getInstanceById($targetId, $targetModule));
                }
            }
        }

        return array_values(array_unique($numbers));
    }

    /**
     * Returns the targets a source document points to through its own fields (also of a deleted source):
     * [target record id => target module].
     *
     * @param string $sourceModule Source document module
     * @param int    $sourceId     Source document record id
     * @return array<int, string>
     */
    protected function getLinkedTargetIds(string $sourceModule, int $sourceId): array
    {
        $targetIds = [];

        foreach (self::SOURCE_LINK_FIELDS[$sourceModule] ?? [] as $fieldName => [$targetModule]) {
            $targetId = (int)$this->retrieveFieldValue($sourceModule, $sourceId, $fieldName);

            if ($targetId > 0 && getSalesEntityType($targetId) === $targetModule) {
                $targetIds[$targetId] = $targetModule;
            }
        }

        return $targetIds;
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

            foreach ($this->getLinkedTargetIds($sourceModule, $sourceId) as $targetId => $targetModule) {
                if (isRecordExists($targetId)) {
                    $balanceModel->recalculateDocument($targetModule, $targetId);
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
        if (Core_PaymentDocumentBalance_Model::getPayingModule($targetRecord->getModuleName()) === '') {
            return 0.0;
        }

        return max(0.0, $this->getDocumentTotal($targetRecord) - Core_PaymentDocumentBalance_Model::getInstance()->getOwnNetAmount($targetRecord, $isReceived));
    }

    /**
     * Returns the total of a document from the total field of its paying module, in the document currency;
     * 0 when no active payment module pays it.
     *
     * @param Vtiger_Record_Model $recordModel Document record
     * @return float
     * @throws Exception
     */
    protected function getDocumentTotal(Vtiger_Record_Model $recordModel): float
    {
        $moduleName = $recordModel->getModuleName();
        $payingModule = Core_PaymentDocumentBalance_Model::getPayingModule($moduleName);

        if ($payingModule === '') {
            return 0.0;
        }

        $config = Core_PaymentConfig_Helper::getInstance($payingModule)->getIntegrationModel()->retrieveByModule($moduleName);
        $totalField = (string)($config['total_field'] ?? '');

        return $totalField !== '' ? Core_PaymentCalculation_Helper::parseAmount($recordModel->get($totalField)) : 0.0;
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
