<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Reporting_Module_Model extends Vtiger_Module_Model
{
    public function getModuleIcon($height = ''): string
    {
        return sprintf('<i class="fa-solid fa-chart-pie" style="font-size: %s"></i>', $height);
    }
}