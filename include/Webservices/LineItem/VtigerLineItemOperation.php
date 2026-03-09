<?php
/************************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ***********************************************************************************/
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * Modifications and additions by IT-Solutions4You (ITS4YOU) are Copyright (c) IT-Solutions4You s.r.o.
 *
 * These contributions are licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

require_once "include/Webservices/VtigerActorOperation.php";
require_once "include/Webservices/LineItem/VtigerInventoryOperation.php";
require_once("include/events/include.inc");
require_once 'modules/com_vtiger_workflow/VTEntityCache.inc';
require_once 'data/CRMEntity.php';
require_once 'include/events/SqlResultIterator.inc';
require_once 'include/Webservices/LineItem/VtigerLineItemMeta.php';
require_once 'include/Webservices/Retrieve.php';
require_once 'include/Webservices/Update.php';
require_once 'include/Webservices/Utils.php';
require_once 'include/utils/InventoryUtils.php';
require_once 'include/Webservices/LineItem/InventoryItemSync.php';

/**
 * Description of VtigerLineItemOperation
 */
class VtigerLineItemOperation extends VtigerActorOperation
{
    private static $lineItemCache = [];
    private $newId = null;
    private $taxList = null;
    private $inActiveTaxList = null;
    private static $parentCache = [];

    public function __construct($webserviceObject, $user, $adb, $log)
    {
        $this->user = $user;
        $this->log = $log;
        $this->webserviceObject = $webserviceObject;
        $this->pearDB = $adb;
        $this->entityTableName = $this->getActorTables();

        if ($this->entityTableName === null) {
            throw new WebServiceException(WebServiceErrorCode::$UNKOWNENTITY, 'Entity is not associated with any tables');
        }

        $this->meta = new VtigerLineItemMeta($this->entityTableName, $webserviceObject, $adb, $user);
        $this->moduleFields = null;
        $this->taxList = [];
        $this->inActiveTaxList = [];
    }

    protected function getNextId($elementType, $element)
    {
        $sql = 'SELECT MAX(' . $this->meta->getIdColumn() . ') as maxvalue_lineitem_id FROM ' . $this->entityTableName;
        $result = $this->pearDB->pquery($sql, []);
        $numOfRows = $this->pearDB->num_rows($result);

        for ($i = 0; $i < $numOfRows; $i++) {
            $row = $this->pearDB->query_result($result, $i, 'maxvalue_lineitem_id');
        }

        $id = $row + 1;

        return $id;
    }

    public function recreate($lineItem, $parent)
    {
        if (!empty($parent['id'])) {
            $lineItem['parent_id'] = $parent['id'];
        }

        return $this->create('LineItem', $lineItem);
    }

    /**
     * Function gives all the line items related to inventory records
     *
     * @param $parentId - record id or array of the inventory record id's
     *
     * @return <Array> - list of line items
     * @throws WebServiceException - Database error
     */
    public function getAllLineItemForParent($parentId)
    {
        if (!is_array($parentId)) {
            $parentId = [$parentId];
        }

        $lineItemList = [];

        foreach ($parentId as $pid) {
            $pid = (int)$pid;
            $items = InventoryItem_Module_Model::fetchItemsForId($pid, true);

            foreach ($items as $item) {
                $itemModel = Vtiger_Record_Model::getInstanceById($item['inventoryitemid'], 'InventoryItem');
                $lineItemList[] = $this->formatInventoryItemForWs($itemModel, $pid);
            }
        }

        return $lineItemList;
    }

    public function cleanLineItemList($parentId)
    {
        $components = vtws_getIdComponents($parentId);
        $pId = (int)$components[1];
        $items = InventoryItem_Module_Model::fetchItemsForId($pId, true);

        foreach ($items as $item) {
            $itemModel = Vtiger_Record_Model::getInstanceById($item['inventoryitemid'], 'InventoryItem');
            $itemModel->delete();
        }

        InventoryItem_ParentEntity_Model::updateTotals($pId);
    }

    public function setLineItems($elementType, $lineItemList, $parent)
    {
        $parentId = vtws_getIdComponents($parent['id'])[1];
        $moduleName = getSalesEntityType((int)$parentId);
        InventoryItem_Webservice_Sync::syncLineItems($moduleName, (int)$parentId, $lineItemList, $parent);
    }

    public function create($elementType, $element)
    {
        $parentId = InventoryItem_Webservice_Helpers::getCrmIdFromWsId($element['parent_id'] ?? null);

        if (empty($parentId)) {
            throw new WebServiceException(WebServiceErrorCode::$MANDFIELDSMISSING, 'parent_id is required');
        }

        $parentRecord = Vtiger_Record_Model::getInstanceById($parentId, getSalesEntityType($parentId));
        $assignedUserId = InventoryItem_Webservice_Helpers::getCrmIdFromWsId($element['assigned_user_id'] ?? null);

        if (!$assignedUserId) {
            $assignedUserId = (int)$parentRecord->get('assigned_user_id');
        }

        $priceBookId = InventoryItem_Webservice_Helpers::getCrmIdFromWsId($element['pricebookid'] ?? null);

        if (!$priceBookId) {
            $priceBookId = (int)$parentRecord->get('pricebookid');
        }

        $sequenceValue = $element['sequence_no'] ?? $element['sequence'] ?? null;
        if ($sequenceValue === null || $sequenceValue === '') {
            $sequenceValue = $this->getNextSequence($parentId);
        }

        $itemData = $this->createOrUpdateItemModel(null, $parentId, $element, $assignedUserId, $priceBookId, (int)$sequenceValue);
        InventoryItem_ParentEntity_Model::updateTotals($parentId);

        return $this->formatInventoryItemForWs($itemData['model'], $parentId);
    }

    public function retrieve($id)
    {
        $itemId = InventoryItem_Webservice_Helpers::getCrmIdFromWsId($id);
        $itemModel = Vtiger_Record_Model::getInstanceById($itemId, 'InventoryItem');
        $parentId = (int)$itemModel->get('parentid');

        return $this->formatInventoryItemForWs($itemModel, $parentId);
    }

    public function update($element)
    {
        $itemId = InventoryItem_Webservice_Helpers::getCrmIdFromWsId($element['id'] ?? null);

        if (empty($itemId)) {
            throw new WebServiceException(WebServiceErrorCode::$MANDFIELDSMISSING, 'id is required');
        }

        $itemModel = Vtiger_Record_Model::getInstanceById($itemId, 'InventoryItem');
        $parentId = (int)$itemModel->get('parentid');
        $parentRecord = Vtiger_Record_Model::getInstanceById($parentId, getSalesEntityType($parentId));

        $assignedUserId = InventoryItem_Webservice_Helpers::getCrmIdFromWsId($element['assigned_user_id'] ?? null);

        if (!$assignedUserId) {
            $assignedUserId = (int)$parentRecord->get('assigned_user_id');
        }

        $priceBookId = InventoryItem_Webservice_Helpers::getCrmIdFromWsId($element['pricebookid'] ?? null);

        if (!$priceBookId) {
            $priceBookId = (int)$parentRecord->get('pricebookid');
        }

        $sequenceValue = $element['sequence_no'] ?? $element['sequence'] ?? $itemModel->get('sequence');
        $itemData = $this->createOrUpdateItemModel($itemModel, $parentId, $element, $assignedUserId, $priceBookId, (int)$sequenceValue);
        InventoryItem_ParentEntity_Model::updateTotals($parentId);

        return $this->formatInventoryItemForWs($itemData['model'], $parentId);
    }

    public function delete($id)
    {
        $itemId = InventoryItem_Webservice_Helpers::getCrmIdFromWsId($id);
        $itemModel = Vtiger_Record_Model::getInstanceById($itemId, 'InventoryItem');
        $parentId = (int)$itemModel->get('parentid');
        $itemModel->delete();
        InventoryItem_ParentEntity_Model::updateTotals($parentId);

        return ['id' => $id];
    }

    public function updateParent($createdElement, $parent)
    {
        $parentId = vtws_getIdComponents($parent['id'])[1];
        InventoryItem_ParentEntity_Model::updateTotals((int)$parentId);
    }

    public function getParentById($parentId)
    {
        if (empty(self::$parentCache[$parentId])) {
            self::$parentCache[$parentId] = Vtiger_Functions::jsonEncode(vtws_retrieve($parentId, $this->user));
        }

        return json_decode(self::$parentCache[$parentId], true);
    }

    public function setParent($parentId, $parent)
    {
        if (is_array($parent) || is_object($parent)) {
            $parent = Vtiger_Functions::jsonEncode($parent);
        }
        self::$parentCache[$parentId] = $parent;
    }

    function setCache($parentId, $updatedList)
    {
        self::$lineItemCache[$parentId] = $updatedList;
    }

    public function __create($elementType, $element)
    {
        $element['id'] = $element['parent_id'];
        unset($element['parent_id']);
        $success = parent::__create($elementType, $element);

        return $success;
    }

    protected function getElement()
    {
        if (!empty($this->element['id'])) {
            $this->element['parent_id'] = $this->element['id'];
        }

        return $this->element;
    }

    public function describe($elementType)
    {
        $describe = parent::describe($elementType);
        foreach ($describe['fields'] as $key => $list) {
            if ($list["name"] == 'description') {
                unset($describe['fields'][$key]);
            }
        }
        // unset will retain array index in the result, we should remove
        $describe['fields'] = array_values($describe['fields']);

        return $describe;
    }

    /**
     * @param Vtiger_Record_Model|null $itemModel
     * @param int $parentId
     * @param array $lineItem
     * @param int|null $assignedUserId
     * @param int|null $priceBookId
     * @param int $sequenceValue
     * @return array
     * @throws WebServiceException
     */
    private function createOrUpdateItemModel(
        ?Vtiger_Record_Model $itemModel,
        int $parentId,
        array $lineItem,
        ?int $assignedUserId,
        ?int $priceBookId,
        int $sequenceValue
    ): array {
        if (!$itemModel) {
            $itemModel = Vtiger_Record_Model::getCleanInstance('InventoryItem');
        }

        $productId = InventoryItem_Webservice_Helpers::getCrmIdFromWsId($lineItem['productid'] ?? null);
        $itemText = InventoryItem_Webservice_Helpers::resolveItemText($lineItem, $productId);

        if (empty($productId) && empty($itemText)) {
            throw new WebServiceException(WebServiceErrorCode::$MANDFIELDSMISSING, 'Line item requires productid or item_text');
        }

        $price = $lineItem['listprice'] ?? $lineItem['price'] ?? null;
        $quantity = $lineItem['quantity'] ?? null;
        $description = $lineItem['description'] ?? $lineItem['comment'] ?? null;
        $unit = $lineItem['unit'] ?? null;

        if (!empty($itemText)) {
            $itemModel->set('item_text', $itemText);
        }

        if ($productId) {
            $itemModel->set('productid', $productId);
        }

        if ($quantity !== null) {
            $itemModel->set('quantity', $quantity);
        }

        if ($price !== null) {
            $itemModel->set('price', $price);
        }

        if ($unit !== null) {
            $itemModel->set('unit', $unit);
        }

        if ($description !== null) {
            $itemModel->set('description', $description);
        }

        if ($sequenceValue !== null) {
            $itemModel->set('sequence', $sequenceValue);
        }

        if ($priceBookId) {
            $itemModel->set('pricebookid', $priceBookId);
        }

        if ($assignedUserId) {
            $itemModel->set('assigned_user_id', $assignedUserId);
        }

        $taxData = InventoryItem_Webservice_Helpers::resolveTaxForLineItem($lineItem, $productId);

        if ($taxData['percentage'] !== null) {
            $itemModel->set('tax', $taxData['percentage']);
        }

        InventoryItem_Webservice_Helpers::applyDiscountData($itemModel, $lineItem);

        $itemModel->set('parentid', $parentId);

        if (!empty($lineItem['parentitemid'])) {
            $itemModel->set('parentitemid', InventoryItem_Webservice_Helpers::getCrmIdFromWsId($lineItem['parentitemid']));
        }

        $itemModel->save();

        if (!empty($taxData['taxId'])) {
            $itemModel->saveTaxId((int)$taxData['taxId']);
        } elseif (!empty($taxData['percentage'])) {
            try {
                $taxes = InventoryItem_TaxesForItem_Model::fetchTaxes((int)$itemModel->getId(), (int)$productId, $parentId);
                if (!empty($taxes)) {
                    $taxId = array_key_first($taxes);
                    if (!empty($taxId)) {
                        $itemModel->saveTaxId((int)$taxId);
                    }
                }
            } catch (Exception $e) {
                // ignore tax fallback failure
            }
        }

        return [
            'model' => $itemModel,
            'tax' => $taxData,
        ];
    }

    /**
     * @param Vtiger_Record_Model $itemModel
     * @param int $parentId
     * @return array
     */
    private function formatInventoryItemForWs(Vtiger_Record_Model $itemModel, int $parentId): array
    {
        $itemId = (int)$itemModel->getId();
        $productId = (int)$itemModel->get('productid');
        $entityType = '';
        $productName = '';
        $productWsId = null;

        if ($productId) {
            $entityType = getSalesEntityType($productId);
            $productName = getEntityName($entityType, $productId)[$productId] ?? '';
            $productWsId = vtws_getWebserviceEntityId($entityType, $productId);
        }

        $parentModule = getSalesEntityType($parentId);

        return [
            'id' => vtws_getId($this->meta->getEntityId(), $itemId),
            'parent_id' => vtws_getWebserviceEntityId($parentModule, $parentId),
            'productid' => $productWsId,
            'product_name' => $productName,
            'entity_type' => $entityType ?: 'Text',
            'item_text' => $itemModel->get('item_text'),
            'description' => $itemModel->get('description'),
            'quantity' => $itemModel->get('quantity'),
            'listprice' => $itemModel->get('price'),
            'tax1' => $itemModel->get('tax'),
            'sequence_no' => $itemModel->get('sequence'),
        ];
    }

    /**
     * @param array $lineItem
     * @param int|null $productId
     * @return string|null
     */
    private function getNextSequence(int $parentId): int
    {
        $items = InventoryItem_Module_Model::fetchItemsForId($parentId, true);
        $max = 0;
        foreach ($items as $item) {
            $seq = (int)$item['sequence'];
            if ($seq > $max) {
                $max = $seq;
            }
        }

        return $max + 1;
    }

}