<?php

/*
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 *
 */
class ITS4YouCalendar_Calendar_Action extends Vtiger_Action_Controller
{
    /**
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->exposeMethod('Info');
        $this->exposeMethod('UIMeta');
        $this->exposeMethod('UpdateDates');
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
     * @param Vtiger_Request $request
     * @return void
     */
    public function UIMeta(Vtiger_Request $request)
    {
        $moduleModel = Vtiger_Module_Model::getInstance($request->get('related_module'));
        $moduleFields = $moduleModel->getFields();
        $fieldsInfo = [];

        foreach ($moduleFields as $fieldName => $fieldModel) {
            $fieldsInfo[$fieldName] = $fieldModel->getFieldInfo();
        }

        $response = new Vtiger_Response();
        $response->setResult([
            'ui_meta' => json_encode($fieldsInfo),
            'message' => 'LBL_SUCCESS',
            'success' => true,
        ]);
        $response->emit();
    }

    /**
     * @param Vtiger_Request $request
     * @return void
     */
    public function Info(Vtiger_Request $request)
    {
        $currentUser = Users_Record_Model::getCurrentUserModel();

        $response = new Vtiger_Response();
        $response->setResult([
            'info' => [
                'call_duration' => $currentUser->get('callduration'),
                'other_duration' => $currentUser->get('othereventduration'),
            ],
            'message' => 'LBL_SUCCESS',
            'success' => true,
        ]);
        $response->emit();
    }

    /**
     * @param Vtiger_Request $request
     * @return void
     */
    public function UpdateDates(Vtiger_Request $request)
    {
        $recordId = (int)$request->get('record');
        $recordModel = Vtiger_Record_Model::getInstanceById($recordId);
        $success = false;
        $message = 'LBL_ERROR_SAVE_DATES';

        if ($recordModel && isPermitted($recordModel->getModuleName(), 'Save', $recordId)) {
            $recordModel->set('mode', 'edit');
            $recordModel->set('is_all_day', 'Yes' === $request->get('is_all_day'));
            $recordModel->set('datetime_start', DateTimeField::convertToUserFormat($request->get('start_date')));
            $recordModel->set('datetime_end', DateTimeField::convertToUserFormat($request->get('end_date')));
            $recordModel->save();

            $success = true;
            $message = 'LBL_SUCCESS_SAVE_DATES';
        }

        $response = new Vtiger_Response();
        $response->setResult([
            'record_info' => $recordModel->getData(),
            'success' => $success,
            'message' => vtranslate($message, $request->getModule()),
        ]);
        $response->emit();
    }
}