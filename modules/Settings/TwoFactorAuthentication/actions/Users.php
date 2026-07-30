<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

/**
 * Per-user administration from the "Users" tab: turn 2FA on/off for a user,
 * change their method, and reset a user's second factor (lost device).
 */
class Settings_TwoFactorAuthentication_Users_Action extends Settings_Vtiger_IndexAjax_View
{
    public function __construct()
    {
        parent::__construct();
        $this->exposeMethod('setMethod');
        $this->exposeMethod('setExempt');
        $this->exposeMethod('reset');
    }

    /**
     * @param Vtiger_Request $request
     * @return int
     * @throws Exception
     */
    private function getUserId(Vtiger_Request $request): int
    {
        $userId = (int)$request->get('userid');

        if ($userId <= 0) {
            throw new Exception(vtranslate('LBL_USER_NOT_FOUND', 'Settings:TwoFactorAuthentication'));
        }

        return $userId;
    }

    /**
     * @param Vtiger_Request $request
     * @return void
     * @throws Exception
     */
    public function process(Vtiger_Request $request)
    {
        $mode = $request->getMode();

        if (!empty($mode)) {
            $this->invokeExposedMethod($mode, $request);
        }
    }

    /**
     * @param Vtiger_Request $request
     * @return bool
     * @throws Exception
     */
    public function validateRequest(Vtiger_Request $request): bool
    {
        return $request->validateWriteAccess();
    }

    /**
     * @param Vtiger_Request $request
     * @return void
     * @throws Exception
     */
    public function reset(Vtiger_Request $request): void
    {
        $response = new Vtiger_Response();
        $userId = $this->getUserId($request);

        TwoFactorAuthentication_Service_Helper::resetUser($userId);

        $response->setResult(['success' => true, 'userid' => $userId]);
        $response->emit();
    }

    /**
     * @param Vtiger_Request $request
     * @return void
     * @throws Exception
     */
    public function setExempt(Vtiger_Request $request): void
    {
        $response = new Vtiger_Response();
        $userId = $this->getUserId($request);
        $exempt = (int)$request->get('exempt') === 1;

        TwoFactorAuthentication_Service_Helper::setExempt($userId, $exempt);

        $response->setResult(['success' => true, 'userid' => $userId, 'exempt' => $exempt]);
        $response->emit();
    }

    /**
     * @param Vtiger_Request $request
     * @return void
     * @throws Exception
     */
    public function setMethod(Vtiger_Request $request): void
    {
        $response = new Vtiger_Response();
        $userId = $this->getUserId($request);
        $method = (string)$request->get('method');

        if ($method !== '' && !TwoFactorAuthentication_Service_Helper::isMethodAllowed($method)) {
            throw new Exception(vtranslate('LBL_METHOD_NOT_ALLOWED', 'Settings:TwoFactorAuthentication'));
        }

        TwoFactorAuthentication_Service_Helper::setUserMethod($userId, $method);

        $response->setResult(['success' => true, 'userid' => $userId, 'method' => $method]);
        $response->emit();
    }
}
