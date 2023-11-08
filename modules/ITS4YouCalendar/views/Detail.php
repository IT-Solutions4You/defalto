<?php

/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class ITS4YouCalendar_Detail_View extends Vtiger_Detail_View
{

    /** Calendar Widget: Copy to vtiger Detail view */
    public function getEvents(Vtiger_Request $request)
    {
        $activitiesModuleName = 'ITS4YouCalendar';
        $activitiesModule = Vtiger_Module_Model::getInstance($activitiesModuleName);
        $parentField = $activitiesModule->getField('parent_id');
        $parentModules = array_merge($parentField->getReferenceList(), ['Accounts', 'Contacts']);
        $currentUserPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();

        if ($currentUserPrivilegesModel->hasModulePermission($activitiesModule->getId()) && in_array($request->getModule(), $parentModules)) {
            $moduleName = $request->getModule();
            $recordId = $request->get('record');

            $pageNumber = $request->get('page');
            if (empty ($pageNumber)) {
                $pageNumber = 1;
            }
            $pagingModel = new Vtiger_Paging_Model();
            $pagingModel->set('page', $pageNumber);
            $pagingModel->set('limit', 10);

            if (!$this->record) {
                $this->record = Vtiger_DetailView_Model::getInstance($moduleName, $recordId);
            }
            $recordModel = $this->record->getRecord();
            $moduleModel = $recordModel->getModule();

            $relatedActivities = $moduleModel->getCalendarEvents('', $pagingModel, 'all', $recordId);

            $viewer = $this->getViewer($request);
            $viewer->assign('RECORD', $recordModel);
            $viewer->assign('MODULE_NAME', $moduleName);
            $viewer->assign('PAGING_MODEL', $pagingModel);
            $viewer->assign('PAGE_NUMBER', $pageNumber);
            $viewer->assign('ACTIVITIES', $relatedActivities);
            $viewer->assign('ACTIVITIES_MODULE_NAME', $activitiesModuleName);

            return $viewer->view('RelatedEvents.tpl', $moduleName, true);
        }

        return '';
    }

    /**
     * @param Vtiger_Request $request
     * @return void
     */
    public function process(Vtiger_Request $request)
    {
        $viewer = $this->getViewer($request);
        $viewer->assign('RECURRING_INFORMATION', ITS4YouCalendar_Recurrence_Model::getRecurrenceInformation($request));

        parent::process($request);
    }
}