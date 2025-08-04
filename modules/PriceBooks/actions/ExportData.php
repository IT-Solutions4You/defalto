<?php
/**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
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

class PriceBooks_ExportData_Action extends Vtiger_ExportData_Action
{
    /**
     * this function takes in an array of values for an user and sanitizes it for export
     *
     * @param array $arr - the array of values
     */
    function sanitizeValues($arr)
    {
        $relatedto = $arr['relatedto'];
        $listPrice = $arr['listprice'];

        unset($arr['relatedto']);
        unset($arr['listprice']);

        $arr = parent::sanitizeValues($arr);
        if ($relatedto) {
            $relatedModule = getSalesEntityType($relatedto);
            $result = getEntityName($relatedModule, $relatedto, false);
            $relatedToValue = $relatedModule . '::::' . $result[$relatedto];
        }
        $arr['relatedto'] = $relatedToValue;
        $arr['listprice'] = $listPrice;
        $relatedToValue = $relatedto = $listPrice = null;

        return $arr;
    }

    public function getHeaders()
    {
        if (!$this->headers) {
            $translatedHeaders = parent::getHeaders();
            $fieldList = ['Related To', 'ListPrice'];
            foreach ($fieldList as $fieldName) {
                $translatedHeaders[] = $fieldName;
            }
            $this->headers = $translatedHeaders;
        }

        return $this->headers;
    }
}