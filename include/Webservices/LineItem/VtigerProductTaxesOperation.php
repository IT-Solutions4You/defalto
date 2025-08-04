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

/**
 * Description of VtigerProductTaxesOperation
 */
class VtigerProductTaxesOperation extends VtigerActorOperation
{
    public function create($elementType, $element)
    {
        $db = PearDatabase::getInstance();
        $sql = 'SELECT * FROM vtiger_producttaxrel WHERE productid =? AND taxid=?';
        [$typeId, $productId] = vtws_getIdComponents($element['productid']);
        [$typeId, $taxId] = vtws_getIdComponents($element['taxid']);
        $params = [$productId, $taxId];
        $result = $db->pquery($sql, $params);
        $rowCount = $db->num_rows($result);
        if ($rowCount > 0) {
            $id = $db->query_result($result, 0, $this->meta->getObectIndexColumn());
            $meta = $this->getMeta();
            $element['id'] = vtws_getId($meta->getEntityId(), $id);

            return $this->update($element);
        } else {
            unset($element['id']);

            return parent::create($elementType, $element);
        }
    }
}