<?php
/*
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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