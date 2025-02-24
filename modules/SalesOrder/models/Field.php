<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class SalesOrder_Field_Model extends Vtiger_Field_Model
{
    /**
     * @inheritDoc
     */
    public function isAjaxEditable()
    {
        if ($this->getName() === 'account_id') {
            return false;
        }

        return parent::isAjaxEditable();
    }
}