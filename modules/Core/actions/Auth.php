<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Core_Auth_Action extends Core_Controller_Action
{
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
        $authModel->retrieveAuthClientId();
        $token = null;
        $accessToken = null;

        if ($authModel->getClientId() === $request->get('client_id')) {
            $token = $authModel->getToken();
            $accessToken = $authModel->getAccessToken();
        }

        $response = new Vtiger_Response();
        $response->setResult(['success' => !empty($token), 'token' => $token, 'access_token' => $accessToken]);
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
            $authModel = Core_Auth_Model::getInstance($request->get('client_id'), $request->get('client_secret'), $request->get('client_token', ''));
            $authModel->setProviderName($request->get('provider'));

            $url = $authModel->getRedirectUri();
        }

        $response = new Vtiger_Response();
        $response->setResult(['success' => $success, 'url' => $url, 'message' => $message]);
        $response->emit();
    }
}