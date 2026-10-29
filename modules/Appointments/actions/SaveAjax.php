<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Appointments_SaveAjax_Action extends Vtiger_SaveAjax_Action
{
    /**
     * @param Vtiger_Request $request
     *
     * @return Appointments_Record_Model
     */
    public function getRecordModelFromRequest(Vtiger_Request $request): Appointments_Record_Model
    {
        /** @var Appointments_Record_Model $recordModel */
        $recordModel = parent::getRecordModelFromRequest($request);
        Appointments_DateTime_Helper::setAllDayDatesFromRequest($request, $recordModel);

        return $recordModel;
    }
}
