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
 * Activates or deactivates the routed two-factor login flow. This is the
 * explicit on/off switch an administrator uses after the module is installed.
 */
class Settings_TwoFactorAuthentication_Activate_Action extends Settings_Vtiger_IndexAjax_View
{
    public function __construct()
    {
        parent::__construct();
        $this->exposeMethod('activate');
        $this->exposeMethod('deactivate');
    }

    /**
     * @param Vtiger_Request $request
     * @return void
     * @throws Exception
     */
    public function activate(Vtiger_Request $request)
    {
        $response = new Vtiger_Response();
        $ok = TwoFactorAuthentication_Service_Helper::activate();

        if (!$ok) {
            throw new Exception(vtranslate('LBL_ACTIVATE_FAILED', 'Settings:TwoFactorAuthentication'));
        }

        $response->setResult(['success' => true, 'active' => true]);
        $response->emit();
    }

    /**
     * @param Vtiger_Request $request
     * @return void
     * @throws Exception
     */
    public function deactivate(Vtiger_Request $request)
    {
        $response = new Vtiger_Response();

        TwoFactorAuthentication_Service_Helper::deactivate();

        $response->setResult(['success' => true, 'active' => false]);
        $response->emit();
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
}
