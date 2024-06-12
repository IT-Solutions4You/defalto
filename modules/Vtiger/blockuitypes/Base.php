<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Vtiger_Base_BlockUIType extends Vtiger_Base_Model implements Vtiger_IF_BlockUIType
{
    /**
     * Returns the Template name for the current Block UI Type Object
     *
     * @return string
     */
    public function getTemplateName(): string
    {
        return 'blockuitypes/Base.tpl';
    }

}