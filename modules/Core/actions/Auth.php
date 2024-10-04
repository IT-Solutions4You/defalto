<?php

/*
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Core_Auth_Action extends Vtiger_Action_Controller
{
    public function postProcess(Vtiger_Request $request)
    {
    }

    public function preProcess(Vtiger_Request $request, $display = true)
    {
    }

    public function process(Vtiger_Request $request)
    {
        $this->exposeMethod('url');
        $this->exposeMethod('token');

        $mode = $request->getMode();

        if (!empty($mode)) {
            $this->invokeExposedMethod($mode, $request);
        }
    }

    public function token(Vtiger_Request $request)
    {
        $authModel = Core_Auth_Model::getInstance();
        $token = null;

        if ($authModel->getClientId() === $request->get('client_id')) {
            $token = $authModel->getToken();
        }

        $response = new Vtiger_Response();
        $response->setResult(['success' => !empty($token), 'token' => $token]);
        $response->emit();
    }

    public function url(Vtiger_Request $request)
    {
        $message = '';
        $url = '';
        $success = true;

        if ($request->isEmpty('provider')) {
            $request->set('provider', (new ITS4YouEmails_Mailer_Model())->getProviderByServer($request->get('server')));
        }

        if ($request->isEmpty('provider') || $request->isEmpty('client_id') || $request->isEmpty('client_secret')) {
            $message = vtranslate('LBL_MISSING_PARAMS', $request->getModule());
            $success = false;
        }

        if ($success) {
            $authModel = Core_Auth_Model::getInstance();
            $authModel->setToken('');
            $authModel->setProviderName($request->get('provider'));
            $authModel->setClientId($request->get('client_id'));
            $authModel->setClientSecret($request->get('client_secret'));
            $url = $authModel->getRedirectUri();
        }

        $response = new Vtiger_Response();
        $response->setResult(['success' => $success, 'url' => $url, 'message' => $message]);
        $response->emit();
    }
}