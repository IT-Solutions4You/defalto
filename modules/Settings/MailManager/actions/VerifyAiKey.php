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
 * Tests the AI API key with the provider and returns the available models.
 * The verified model list is cached per provider (it fills the model list after a reload).
 */
class Settings_MailManager_VerifyAiKey_Action extends Settings_Vtiger_Index_Action
{
    /**
     * Requires an administrator and the Pro edition.
     *
     * @param Vtiger_Request $request
     *
     * @return bool
     * @throws Exception
     */
    public function checkPermission(Vtiger_Request $request): bool
    {
        parent::checkPermission($request);

        if (!MailManager_Module_Model::isProEnabled()) {
            throw new Exception(vtranslate('LBL_PRO_MEMBERSHIP_REQUIRED', 'MailManager'));
        }

        return true;
    }

    /**
     * Requires a valid CSRF token and a POST request (the key travels in the body).
     *
     * @param Vtiger_Request $request
     *
     * @return bool
     * @throws Exception
     */
    public function validateRequest(Vtiger_Request $request): bool
    {
        return $request->validateWriteAccess();
    }

    /**
     * Verifies the typed (or stored) key of a provider, stores a typed key that works
     * (encrypted, per provider), caches the model list, runs a tiny test completion and
     * gives agents of that provider without a model the recommended one of their type.
     *
     * @param Vtiger_Request $request
     *
     * @return void
     */
    public function process(Vtiger_Request $request)
    {
        $response = new Vtiger_Response();
        $qualifiedModuleName = $request->getModule(false);
        $provider = (string)$request->get('provider');

        if (!in_array($provider, MailManager_ProAiConfig_Model::PROVIDERS, true)) {
            $response->setResult(['success' => false, 'message' => vtranslate('LBL_UNKNOWN_AI_PROVIDER', $qualifiedModuleName)]);
            $response->emit();

            return;
        }

        $response->setResult(MailManager_ProAiSettings_Model::verifyApiKey($provider, (string)$request->getRaw('api_key')));
        $response->emit();
    }
}
