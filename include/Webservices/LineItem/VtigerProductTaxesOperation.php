<?php
/*********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *********************************************************************************/


/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * Modifications and additions by IT-Solutions4You (ITS4YOU) are Copyright (c) IT-Solutions4You s.r.o.
 *
 * These contributions are licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

require_once 'include/Webservices/VtigerActorOperation.php';
require_once 'include/Webservices/LineItem/InventoryItemHelpers.php';

/**
 * Description of VtigerProductTaxesOperation
 */
class VtigerProductTaxesOperation extends VtigerActorOperation
{
    public function create($elementType, $element)
    {
        $productRaw = $element['productid'] ?? $element['record_id'] ?? null;
        $taxRaw = $element['taxid'] ?? $element['tax_id'] ?? null;
        $productId = InventoryItem_Webservice_Helpers::getCrmIdFromWsId($productRaw);
        $taxId = InventoryItem_Webservice_Helpers::getCrmIdFromWsId($taxRaw);
        if (empty($productId) || empty($taxId)) {
            throw new WebServiceException(WebServiceErrorCode::$MANDFIELDSMISSING, 'productid and taxid are required');
        }
        $percentage = $element['taxpercentage'] ?? $element['percentage'] ?? null;
        if ($percentage === null) {
            $taxModel = Core_Tax_Model::getInstanceById((int)$taxId);
            if ($taxModel) {
                $percentage = $taxModel->getTax();
            }
        }

        $record = Core_TaxRecord_Model::getInstance((int)$productId);
        $record->set('record_id', (int)$productId);
        $record->set('tax_id', (int)$taxId);
        $record->set('percentage', $percentage ?? 0);
        $record->set('region_id', $element['region_id'] ?? null);
        $record->retrieveId();
        $record->save();

        $meta = $this->getMeta();
        $element['id'] = vtws_getId($meta->getEntityId(), (int)$record->getId());

        return $this->retrieve($element['id']);
    }
}