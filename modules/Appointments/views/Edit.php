<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Appointments_Edit_View extends Vtiger_Edit_View
{

    /**
     * @param Vtiger_Request $request
     *
     * @return void
     */
    public function process(Vtiger_Request $request)
    {
        Appointments_Module_Model::retrieveDefaultValuesForEdit($request);

        $viewer = $this->getViewer($request);
        $viewer->assign('RECURRING_INFORMATION', Appointments_Recurrence_Model::getRecurrenceInformation($request));

        parent::process($request);
    }
}