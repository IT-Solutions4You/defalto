<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Reporting_Module_Model extends Vtiger_Module_Model
{
    public function getModuleIcon($height = ''): string
    {
        return sprintf('<i class="fa-solid fa-chart-pie" style="font-size: %s"></i>', $height);
    }
}