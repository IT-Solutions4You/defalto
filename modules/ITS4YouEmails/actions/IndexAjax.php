<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class ITS4YouEmails_IndexAjax_Action extends Core_Controller_Action
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

    /**
     * @inheritDoc
     */
    public function checkPermission(Vtiger_Request $request): bool
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
        $response->setResult(['success' => true, 'signature' => $signature]);
        $response->emit();
    }
}