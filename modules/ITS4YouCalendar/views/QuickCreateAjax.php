<?php
/*
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class ITS4YouCalendar_QuickCreateAjax_View extends Vtiger_QuickCreateAjax_View
{
    /**
     * @param Vtiger_Request $request
     * @return void
     */
    public function process(Vtiger_Request $request)
    {
        ITS4YouCalendar_Module_Model::retrieveDefaultValuesForEdit($request);

        parent::process($request);
    }
}