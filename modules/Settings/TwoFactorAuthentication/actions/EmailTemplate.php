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
 * Saves / resets the customizable email templates (subject + HTML body).
 */
class Settings_TwoFactorAuthentication_EmailTemplate_Action extends Settings_Vtiger_Index_Action
{
    public function __construct()
    {
        parent::__construct();
        $this->exposeMethod('save');
        $this->exposeMethod('reset');
    }

    /**
     * @param Vtiger_Request $request
     * @return string
     * @throws Exception
     */
    private function getKey(Vtiger_Request $request): string
    {
        $key = (string)$request->get('key');

        if (!in_array($key, [TwoFactorAuthentication_Service_Helper::TPL_LOGIN, TwoFactorAuthentication_Service_Helper::TPL_BACKUP], true)) {
            throw new Exception('Invalid template key');
        }

        return $key;
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
    public function reset(Vtiger_Request $request): void
    {
        $response = new Vtiger_Response();
        $key = $this->getKey($request);

        TwoFactorAuthentication_Service_Helper::resetEmailTemplate($key);
        $template = TwoFactorAuthentication_Service_Helper::getEmailTemplate($key);

        $response->setResult(['success' => true, 'key' => $key, 'subject' => $template['subject'], 'body' => $template['body']]);
        $response->emit();
    }

    /**
     * @param Vtiger_Request $request
     * @return void
     * @throws Exception
     */
    public function save(Vtiger_Request $request): void
    {
        $response = new Vtiger_Response();
        $key = $this->getKey($request);

        // getRaw keeps the HTML body intact (the request purifier would strip it).
        TwoFactorAuthentication_Service_Helper::saveEmailTemplate($key, trim((string)$request->get('subject')), (string)$request->getRaw('body'));

        $response->setResult(['success' => true, 'key' => $key]);
        $response->emit();
    }
}
