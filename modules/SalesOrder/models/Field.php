<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class SalesOrder_Field_Model extends Vtiger_Field_Model
{
    /**
     * @return mixed
     */
    public function getDefaultFieldValue()
    {
        if ($this->getName() === 'terms_conditions') {
            return (string)Vtiger_Functions::getInventoryTermsAndCondition($this->getModuleName());
        }

        return $this->defaultvalue;
    }
}