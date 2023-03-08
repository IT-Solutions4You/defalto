<?php

/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class ITS4YouCalendar_Events_Action extends Vtiger_Action_Controller
{
    /**
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->exposeMethod('Range');
        $this->exposeMethod('EditEventType');
        $this->exposeMethod('DeleteEventType');
        $this->exposeMethod('EventInfo');
    }

    /**
     * @param Vtiger_Request $request
     * @return void
     * @throws Exception
     */
    public function process(Vtiger_Request $request)
    {
        $mode = $request->getMode();

        if (!empty($mode) && $this->isMethodExposed($mode)) {
            $this->invokeExposedMethod($mode, $request);
        }
    }

    /**
     * @throws Exception
     */
    public function EventInfo(Vtiger_Request $request)
    {
        $recordId = (int)$request->get('record_id');
        $eventTypeId = (int)$request->get('event_type_id');

        $eventType = ITS4YouCalendar_Events_Model::getInstance($eventTypeId);

        if (empty($eventTypeId)) {
            $eventType->retrieveCalendarData();
        }

        $eventType->setRecordModel(Vtiger_Record_Model::getInstanceById($recordId));

        $response = new Vtiger_Response();
        $response->setResult([
            'info' => $eventType->getRecord(),
            'success' => true,
        ]);
        $response->emit();
    }

    /**
     * @param Vtiger_Request $request
     * @return void
     */
    public function DeleteEventType(Vtiger_Request $request)
    {
        $eventType = ITS4YouCalendar_Events_Model::getInstance((int)$request->get('record'));
        $eventType->delete();

        $response = new Vtiger_Response();
        $response->setResult([
            'message' => vtranslate('LBL_DELETE_SUCCESS', $request->getModule()),
            'success' => true,
        ]);
        $response->emit();
    }

    /**
     * @param Vtiger_Request $request
     * @return void
     */
    public function EditEventType(Vtiger_Request $request)
    {
        $module = $request->getModule();
        $eventTypeInfo = $request->get('event_type');
        $eventType = ITS4YouCalendar_Events_Model::getInstance((int)$eventTypeInfo['record']);
        $eventType->set('fields', explode(',', trim($eventTypeInfo['fields'], ',')));
        $eventType->set('module', $eventTypeInfo['module']);
        $eventType->set('color', $eventTypeInfo['color']);
        $recordInfo = false;
        $success = true;

        if ($eventType->isEmptyId()) {
            if ($eventType->isDuplicate()) {
                $message = 'LBL_DUPLICATE_EVENT_TYPE';
                $success = false;
            } else {
                $eventType->save();

                $message = 'LBL_CREATED_EVENT_TYPE';
                $recordInfo = [
                    'id' => $eventType->getId(),
                    'name' => $eventType->getName(),
                    'background_color' => $eventType->getBackgroundColor(),
                    'text_color' => $eventType->getTextColor(),
                    'visible' => 1,
                ];
            }
        } else {
            $eventType->save();
            $recordInfo = [
                'id' => $eventType->getId(),
                'name' => $eventType->getName(),
                'background_color' => $eventType->getBackgroundColor(),
                'text_color' => $eventType->getTextColor(),
            ];

            $message = 'LBL_UPDATE_EVENT_TYPE';
        }

        $response = new Vtiger_Response();
        $response->setResult([
            'success' => $success,
            'message' => vtranslate($message, $module),
            'record' => $recordInfo
        ]);
        $response->emit();
    }

    /**
     * @param Vtiger_Request $request
     * @return void
     */
    public function Range(Vtiger_Request $request)
    {
        $response = new Vtiger_Response();
        $response->setResult([
            'events' => ITS4YouCalendar_Events_Model::getEventsFromRequest($request),
            'success' => true,
        ]);
        $response->emit();
    }
}