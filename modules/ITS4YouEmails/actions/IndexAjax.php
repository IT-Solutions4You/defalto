<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
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