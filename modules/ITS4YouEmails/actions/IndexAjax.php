<?php
/* * *******************************************************************************
 * The content of this file is subject to the EMAIL Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */

class ITS4YouEmails_IndexAjax_Action extends Vtiger_Action_Controller
{
    public function __construct()
    {
        $methods = [
            'getUserSignature'
        ];

        foreach ($methods as $method) {
            $this->exposeMethod($method);
        }
    }

    public function checkPermission(Vtiger_Request $request)
    {
        return true;
    }

    public function process(Vtiger_Request $request)
    {
        $mode = $request->get('mode');

        if (!empty($mode)) {
            $this->invokeExposedMethod($mode, $request);
        }
    }

    public function getUserSignature(Vtiger_Request $request)
    {
        $currentUserModel = Users_Record_Model::getCurrentUserModel();
        $signature = decode_html($currentUserModel->get('signature'));

        $response = new Vtiger_Response();
        $response->setResult(array('success' => true, 'signature' => $signature));
        $response->emit();
    }
}