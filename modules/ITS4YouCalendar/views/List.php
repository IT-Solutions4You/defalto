<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class ITS4YouCalendar_List_View extends Vtiger_List_View
{
    public function preProcess(Vtiger_Request $request, $display = true)
    {
        if(!$request->isEmpty('viewname')) {
            ITS4YouCalendar_Module_Model::updateTodayFilterDates(Vtiger_Filter::getInstance($request->get('viewname')));
        }

        parent::preProcess($request, $display);
    }
}