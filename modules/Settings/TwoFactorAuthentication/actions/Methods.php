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
 * Saves which second-factor methods are available to users.
 */
class Settings_TwoFactorAuthentication_Methods_Action extends Settings_Vtiger_IndexAjax_View
{
    public function __construct()
    {
        parent::__construct();
        $this->exposeMethod('save');
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
            echo $this->invokeExposedMethod($mode, $request);
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
    public function save(Vtiger_Request $request): void
    {
        $response = new Vtiger_Response();
        $email = (int)$request->get('allow_email') === 1;
        $totp = (int)$request->get('allow_totp') === 1;

        TwoFactorAuthentication_Service_Helper::setAllowedMethods($email, $totp);

        $allowed = TwoFactorAuthentication_Service_Helper::getAllowedMethods();
        $response->setResult([
            'success' => true,
            'allow_email' => in_array(TwoFactorAuthentication_Service_Helper::METHOD_EMAIL, $allowed, true),
            'allow_totp' => in_array(TwoFactorAuthentication_Service_Helper::METHOD_TOTP, $allowed, true),
        ]);
        $response->emit();
    }
}
