<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class ITS4YouCalendar_Edit_View extends Vtiger_Edit_View {

    /**
     * @param Vtiger_Request $request
     * @return void
     */
    public function process(Vtiger_Request $request)
    {
        $currentUser = Users_Record_Model::getCurrentUserModel();
        $request->set('calendar_status', $currentUser->get('defaulteventstatus'));

        $viewer = $this->getViewer($request);
        $viewer->assign('RECURRING_INFORMATION', ITS4YouCalendar_Recurrence_Model::getRecurrenceInformation($request));
        $viewer->assign('TOMORROWDATE', DateTimeField::convertToUserFormat(date('Y-m-d', strtotime('+1 day'))));

        parent::process($request);
    }
}