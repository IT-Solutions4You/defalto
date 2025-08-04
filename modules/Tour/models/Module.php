<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Tour_Module_Model extends Vtiger_Module_Model
{
    protected string $fontIcon = 'fa-solid fa-lightbulb';

    /**
     * @return string
     */
    public function getDefaultUrl()
    {
        return 'index.php?module=Tour&view=Index';
    }

    /**
     * @return string
     */
    public function getListViewUrl()
    {
        return $this->getDefaultUrl();
    }
}