<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class InventoryItem_SaveFromPopup_Action extends Vtiger_SaveAjax_Action
{
    /**
     * @inheritDoc
     */
    public function checkPermission(Vtiger_Request $request): bool
    {
        parent::checkPermission($request);

        $sourceRecordId = (int)$request->get('source_record');
        InventoryItem_ParentEntity_Model::requireEditableParent($sourceRecordId, (string)$request->get('source_module'));

        if ($request->get('record')) {
            InventoryItem_ParentEntity_Model::requireItemsOfParent([$request->get('record')], $sourceRecordId);
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function process(Vtiger_Request $request)
    {
        $response = new Vtiger_Response();

        try {
            $moduleName = 'InventoryItem';

            if ($request->has('insert_after_sequence') && !empty($request->get('insert_after_sequence'))) {
                $allItems = InventoryItem_Module_Model::fetchItemsForId((int)$request->get('source_record'), true);

                foreach ($allItems as $item) {
                    $itemModel = Vtiger_Record_Model::getInstanceById($item['inventoryitemid'], $moduleName);

                    if ($itemModel->get('sequence') > $request->get('insert_after_sequence')) {
                        $itemModel->set('sequence', $itemModel->get('sequence') + 1);
                        $itemModel->set('mode', 'edit');
                        $itemModel->save();
                    }

                    unset($itemModel);
                }

                $request->set('sequence', (int)$request->get('insert_after_sequence') + 1);
            }

            $itemModel = Vtiger_Record_Model::getCleanInstance($moduleName);

            if ($request->get('record')) {
                $itemModel = Vtiger_Record_Model::getInstanceById($request->get('record'), $moduleName);
                $itemModel->set('mode', 'edit');
            }

            $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
            $fieldModelList = $moduleModel->getFields();

            foreach ($fieldModelList as $fieldName => $fieldModel) {
                $fieldValue = $request->get($fieldName, null);
                $fieldValue = $fieldModel->getUITypeModel()->getRequestValue($fieldValue);

                if (null !== $fieldValue) {
                    $itemModel->set($fieldName, $fieldValue);
                }
            }

            $itemModel->set('parentid', $request->get('source_record'));
            $itemModel->set('sequence', $this->decideSequence((int)$request->get('sequence'), (int)$request->get('source_record')));
            $this->setReadOnlyPrices($itemModel, $request);
            $itemModel->save();
            $itemModel->saveTaxId((int)$request->get('taxid'));

            InventoryItem_ParentEntity_Model::updateTotals((int)$request->get('source_record'));

            $entity = $itemModel->getEntity();
            $response->setResult($entity->column_fields->getColumnFields());
        } catch (Exception $e) {
            $response->setError($e->getMessage());
        }

        $response->emit();
    }

    /**
     * Unit price and purchase cost the user's profile does not allow to edit are set on the server:
     * - price: catalogue price of the item; when the catalogue price is 0 (price on request) the entered price is kept,
     * - purchase cost: purchase cost of the item.
     * An existing item keeps its stored values unless the product was changed.
     * CRMEntity saves only fields editable by the profile, so the save is allowed to write these values.
     *
     * @param Vtiger_Record_Model $itemModel
     * @param Vtiger_Request      $request
     *
     * @return void
     * @throws Exception
     */
    protected function setReadOnlyPrices(Vtiger_Record_Model $itemModel, Vtiger_Request $request): void
    {
        $productId = (int)$itemModel->get('productid');

        if (!$productId) {
            return;
        }

        $moduleModel = $itemModel->getModule();
        $priceField = $moduleModel->getField('price');
        $purchaseCostField = $moduleModel->getField('purchase_cost');
        $protectPrice = $priceField && !$priceField->isEditable();
        $protectPurchaseCost = $purchaseCostField && !$purchaseCostField->isEditable();

        if (!$protectPrice && !$protectPurchaseCost) {
            return;
        }

        $storedItem = $itemModel->getId() ? Vtiger_Record_Model::getInstanceById($itemModel->getId(), 'InventoryItem') : null;
        $productChanged = !$storedItem || (int)$storedItem->get('productid') !== $productId;
        $sourceRecordModel = Vtiger_Record_Model::getInstanceById($request->get('source_record'), $request->get('source_module'));
        $currencyId = (int)$sourceRecordModel->get('currency_id');

        if ($protectPrice) {
            $priceData = InventoryItem_Utils_Helper::decideItemPriceAndPriceBook($productId, $currencyId, (int)$sourceRecordModel->get('pricebookid'));
            $catalogPrice = (float)$priceData['price'];

            if ($catalogPrice > 0) {
                $itemModel->set('price', $productChanged ? $catalogPrice : $storedItem->get('price'));
            }
        }

        if ($protectPurchaseCost) {
            $itemModel->set('purchase_cost', $productChanged ? $this->getItemPurchaseCost($productId, $currencyId) : $storedItem->get('purchase_cost'));
        }

        $itemModel->getEntity()->isWorkFlowFieldUpdate = true;
    }

    /**
     * Purchase cost of the product or service converted to the currency of the parent record
     *
     * @param int $itemId
     * @param int $currencyId
     *
     * @return float
     * @throws Exception
     */
    protected function getItemPurchaseCost(int $itemId, int $currencyId): float
    {
        $recordModel = Vtiger_Record_Model::getInstanceById($itemId);
        $purchaseCost = (float)$recordModel->get('purchase_cost');
        $itemCurrencyId = method_exists($recordModel, 'fetchCurrencyId') ? (int)$recordModel->fetchCurrencyId() : $currencyId;

        if ($itemCurrencyId && $itemCurrencyId !== $currencyId) {
            $currenciesConversionTable = InventoryItem_Utils_Helper::getCurrenciesConversionTable();
            $purchaseCost = $purchaseCost / $currenciesConversionTable[$itemCurrencyId] * $currenciesConversionTable[$currencyId];
        }

        return round($purchaseCost, getCurrencyDecimalPlaces());
    }

    /**
     * If the record comes with sequence, return this sequence
     * If there are some lines in the parent record, get the max sequence + 1 and return it
     * Otherwise return 1 - supposing it is the newly created first line
     *
     * @param int $sequence
     * @param int $parentId
     *
     * @return int
     */
    protected function decideSequence(int $sequence, int $parentId): int
    {
        if ($sequence > 0) {
            return $sequence;
        }

        $db = PearDatabase::getInstance();
        $sql = 'SELECT COALESCE(MAX(sequence)+1, 1) AS new_sequence 
                FROM df_inventoryitem
                INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = df_inventoryitem.inventoryitemid AND vtiger_crmentity.deleted = 0
                WHERE parentid = ?';
        $res = $db->pquery($sql, [$parentId]);

        if ($db->num_rows($res)) {
            $row = $db->fetchByAssoc($res);

            return $row['new_sequence'];
        }

        return 1;
    }
}