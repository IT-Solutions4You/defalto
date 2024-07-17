<?php
/*
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Core_TemplateContent_Helper extends Vtiger_Base_Model
{
    /**
     * @param $finalDetails
     * @return mixed
     */
    public function getTotalWithVat($finalDetails)
    {
        if ('individual' === $finalDetails['taxtype']) {
            return $finalDetails['hdnSubTotal'];
        }

        return $finalDetails['preTaxTotal'] + $finalDetails['tax_totalamount'];
    }
}