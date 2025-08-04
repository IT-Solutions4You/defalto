<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Appointments_QuickCreateAjax_View extends Vtiger_QuickCreateAjax_View
{
    /**
     * @param Vtiger_Request $request
     *
     * @return void
     */
    public function process(Vtiger_Request $request)
    {
        Appointments_Module_Model::retrieveDefaultValuesForEdit($request);

        parent::process($request);
    }
}