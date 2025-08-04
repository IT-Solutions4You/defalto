<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Appointments_Calendar_View extends Vtiger_Index_View
{
    /**
     * @param Vtiger_Request $request
     *
     * @return void
     */
    public function Calendar(Vtiger_Request $request)
    {
        if (!$request->isEmpty('initialView')) {
            Appointments_Events_Model::$initialView = $request->get('initialView');
        }

        $module = $request->getModule();
        /** @var Appointments_Module_Model $moduleModel */
        $moduleModel = Vtiger_Module_Model::getInstance($module);
        $typeFieldName = 'calendar_type';
        $typeField = $moduleModel->getField($typeFieldName);

        $viewer = $this->getViewer($request);
        $viewer->assign('CURRENT_USER', Users_Record_Model::getCurrentUserModel());
        $viewer->assign('FIELD_TYPE_VALUES', $typeField->getPicklistValues());
        $viewer->assign('TYPE_COLORS', $typeField->getPicklistColors());
        $viewer->assign('EVENT_TYPES', Appointments_Events_Model::getEventTypes());
        $viewer->assign('HIDE_DAYS', $moduleModel->getHideDays());
        $viewer->assign('IS_EVENT_TYPES_VISIBLE', $moduleModel->isEventTypesVisible());

        $usersGroups = Appointments_UsersGroups_Model::getInstance();

        $viewer->assign('USERS_GROUPS', $usersGroups);
        $viewer->assign('USERS_GROUPS_INFO', $usersGroups->getInfo());
        $viewer->assign('USERS_GROUPS_USER_SELECTED', $usersGroups->getUserSelected());
        $viewer->assign('USERS_GROUPS_USERS_SELECTED', $usersGroups->getUsersSelected());
        $viewer->assign('USERS_GROUPS_GROUP_SELECTED', $usersGroups->getGroupSelected());
        $viewer->assign('USERS_GROUPS_TABS', $usersGroups->getTabs());

        $viewer->view('Calendar.tpl', $module);
    }

    /**
     * @param Vtiger_Request $request
     *
     * @return void
     */
    public function EditEventType(Vtiger_Request $request)
    {
        $module = $request->getModule();
        $recordId = (int)$request->get('record');

        $eventTypeFields = Appointments_Events_Model::getEventFields();
        $eventTypeModules = array_keys($eventTypeFields);
        $eventTypeRecord = Appointments_Events_Model::getInstance($recordId);

        if ($eventTypeRecord->isEmptyId()) {
            $selectedModule = $eventTypeModules[0];
        } else {
            $selectedModule = $eventTypeRecord->getModule();
        }

        $selectedFields = (array)$eventTypeFields[$selectedModule];

        $viewer = $this->getViewer($request);
        $viewer->assign('CURRENT_USER', Users_Record_Model::getCurrentUserModel());
        $viewer->assign('QUALIFIED_MODULE', $request->getModule(false));
        $viewer->assign('MODULE', $request->getModule());
        $viewer->assign('EVENT_TYPE_ALL_FIELDS', $eventTypeFields);
        $viewer->assign('EVENT_TYPE_ALL_MODULES', array_keys($eventTypeFields));
        $viewer->assign('EVENT_TYPE_MODULE', $selectedModule);
        $viewer->assign('EVENT_TYPE_FIELDS', $selectedFields);
        $viewer->assign('EVENT_TYPE_RECORD', $eventTypeRecord);
        $viewer->view('EditEventType.tpl', $module);
    }

    /**
     * @param Vtiger_Request $request
     *
     * @return void
     * @throws Exception
     */
    public function PopoverContainer(Vtiger_Request $request)
    {
        $recordId = (int)$request->get('recordId');
        $recordModel = Vtiger_Record_Model::getInstanceById($recordId);
        $moduleModel = $recordModel->getModule();
        $eventTypeId = (int)$request->get('eventTypeId');
        $eventType = Appointments_Events_Model::getInstance($eventTypeId);

        if (empty($eventTypeId)) {
            $eventType->retrieveCalendarData();
        }

        $eventType->setRecordModel($recordModel);

        $dateFields = $eventType->getFormattedDates();
        $popoverValues = $eventType->getPopoverValues();
        $qualifiedModule = $request->getModule(false);

        $viewer = $this->getViewer($request);
        $viewer->assign('HEADER_VALUES', $popoverValues);
        $viewer->assign('RECORD_ID', $recordId);
        $viewer->assign('RECORD_MODEL', $recordModel);
        $viewer->assign('DATE_FIELDS', $dateFields);
        $viewer->assign('MODULE_MODEL', $moduleModel);
        $viewer->assign('QUALIFIED_MODULE', $qualifiedModule);
        $viewer->assign('EVENT_TYPE', $eventType);
        $viewer->assign('EVENT_TYPE_DETAIL_LINK', $eventType->getDetailLink());
        $viewer->view('PopoverContainer.tpl', $qualifiedModule);
    }

    /**
     * @param Vtiger_Request $request
     *
     * @return void
     */
    public function UsersGroupsModal(Vtiger_Request $request)
    {
        $module = $request->getModule();
        /** @var Appointments_Module_Model $moduleModel */
        $moduleModel = Vtiger_Module_Model::getInstance($module);

        $viewer = $this->getViewer($request);
        $viewer->assign('QUALIFIED_MODULE', $request->getModule(false));
        $viewer->assign('MODULE', $request->getModule());
        $viewer->assign('CURRENT_USER', Users_Record_Model::getCurrentUserModel());

        $usersGroups = Appointments_UsersGroups_Model::getInstance();

        $viewer->assign('USERS_GROUPS_VALUES', $usersGroups->getAll());
        $viewer->assign('USERS_GROUPS_SELECTED', $request->get('selected'));

        $viewer->view('UsersGroupsModal.tpl', $module);
    }

    /**
     * @param Vtiger_Request $request
     *
     * @return array
     */
    public function getHeaderCss(Vtiger_Request $request): array
    {
        $headerCssInstances = parent::getHeaderCss($request);
        $moduleName = $request->getModule();
        $layout = Vtiger_Viewer::getLayoutName();
        $cssFileNames = [
            '~vendor/fullcalendar/fullcalendar/lib/main.min.css',
            '~/libraries/jquery/colorpicker/css/colorpicker.css',
            'modules.Vtiger.resources.Detail',
            "layouts.$layout.modules.$moduleName.resources.Calendar",
        ];

        return array_merge($headerCssInstances, $this->checkAndConvertCssStyles($cssFileNames));
    }

    /**
     * @param Vtiger_Request $request
     *
     * @return array
     */
    public function getHeaderScripts(Vtiger_Request $request)
    {
        $headerScriptInstances = parent::getHeaderScripts($request);
        $moduleName = $request->getModule();
        $jsFileNames = [
            '~vendor/fullcalendar/fullcalendar/lib/main.min.js',
            '~/libraries/jquery/colorpicker/js/colorpicker.js',
            'modules.Vtiger.resources.Detail',
            "modules.$moduleName.resources.Calendar",
        ];

        return array_merge($headerScriptInstances, $this->checkAndConvertJsScripts($jsFileNames));
    }

    /**
     * @param Vtiger_Request $request
     *
     * @return mixed|void
     * @throws Exception
     */
    public function process(Vtiger_Request $request)
    {
        $mode = $request->getMode();

        if (!empty($mode) && $this->isMethodExposed($mode)) {
            return $this->invokeExposedMethod($mode, $request);
        }

        $this->Calendar($request);
    }

    /**
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->exposeMethod('Calendar');
        $this->exposeMethod('EditEventType');
        $this->exposeMethod('PopoverContainer');
        $this->exposeMethod('UsersGroupsModal');
    }
}