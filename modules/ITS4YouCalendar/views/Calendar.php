<?php

/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class ITS4YouCalendar_Calendar_View extends Vtiger_Index_View
{
    /**
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->exposeMethod('Calendar');
        $this->exposeMethod('EditEventType');
        $this->exposeMethod('PopoverContainer');
    }

    /**
     * @param Vtiger_Request $request
     * @return void
     */
    public function EditEventType(Vtiger_Request $request)
    {
        $module = $request->getModule();
        $recordId = (int)$request->get('record');

        $eventTypeFields = ITS4YouCalendar_Events_Model::getEventFields();
        $eventTypeModules = array_keys($eventTypeFields);
        $eventTypeRecord = ITS4YouCalendar_Events_Model::getInstance($recordId);

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
     * @param Vtiger_Request $request
     * @return void
     */
    public function Calendar(Vtiger_Request $request)
    {
        $module = $request->getModule();
        /** @var ITS4YouCalendar_Module_Model $moduleModel */
        $moduleModel = Vtiger_Module_Model::getInstance($module);
        $typeFieldName = 'calendar_type';
        $typeField = $moduleModel->getField($typeFieldName);

        $viewer = $this->getViewer($request);
        $viewer->assign('CURRENT_USER', Users_Record_Model::getCurrentUserModel());
        $viewer->assign('FIELD_TYPE_VALUES', $typeField->getPicklistValues());
        $viewer->assign('TYPE_COLORS', $typeField->getPicklistColors());
        $viewer->assign('EVENT_TYPES', ITS4YouCalendar_Events_Model::getEventTypes());
        $viewer->assign('USERS_GROUPS_VALUES', $moduleModel->getUsersAndGroups());

        $viewer->view('Calendar.tpl', $module);
    }

    /**
     * @param Vtiger_Request $request
     * @return array
     */
    public function getHeaderScripts(Vtiger_Request $request)
    {
        $headerScriptInstances = parent::getHeaderScripts($request);
        $moduleName = $request->getModule();
        $jsFileNames = array(
            '~vendor/fullcalendar/fullcalendar/lib/main.min.js',
            '~/libraries/jquery/colorpicker/js/colorpicker.js',
            'modules.Vtiger.resources.Detail',
            "modules.$moduleName.resources.Calendar",
        );

        return array_merge($headerScriptInstances, $this->checkAndConvertJsScripts($jsFileNames));
    }


    /**
     * @param Vtiger_Request $request
     * @return array
     */
    public function getHeaderCss(Vtiger_Request $request)
    {
        $headerCssInstances = parent::getHeaderCss($request);
        $moduleName = $request->getModule();
        $layout = Vtiger_Viewer::getLayoutName();
        $cssFileNames = array(
            '~vendor/fullcalendar/fullcalendar/lib/main.min.css',
            '~/libraries/jquery/colorpicker/css/colorpicker.css',
            'modules.Vtiger.resources.Detail',
            "layouts.$layout.modules.$moduleName.resources.Calendar",
        );

        return array_merge($headerCssInstances, $this->checkAndConvertCssStyles($cssFileNames));
    }

    /**
     * @param Vtiger_Request $request
     * @return void
     * @throws Exception
     */
    public function PopoverContainer(Vtiger_Request $request)
    {
        $recordId = (int)$request->get('recordId');
        $recordModel = Vtiger_Record_Model::getInstanceById($recordId);
        $moduleModel = $recordModel->getModule();
        $eventTypeId = (int)$request->get('eventTypeId');
        $eventType = ITS4YouCalendar_Events_Model::getInstance($eventTypeId);

        if(empty($eventTypeId)) {
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
}