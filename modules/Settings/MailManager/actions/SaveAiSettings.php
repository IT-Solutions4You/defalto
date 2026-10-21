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
 * Saves the AI Assistant settings of Mail Manager Pro. The API key is stored encrypted
 * and only when the administrator typed a new one.
 */
class Settings_MailManager_SaveAiSettings_Action extends Settings_Vtiger_Index_Action
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
     * Requires a valid CSRF token and a POST request.
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
     * Stores the submitted AI settings.
     *
     * @param Vtiger_Request $request
     *
     * @return void
     */
    public function process(Vtiger_Request $request)
    {
        $response = new Vtiger_Response();
        $values = [];

        foreach (MailManager_ProAiSettings_Model::getSettingsFields() as $field) {
            if ($request->has($field)) {
                $values[$field] = $request->get($field);
            }
        }

        // the provider and the model belong to the agents now (I13); an old form still sends them
        $provider = MailManager_ProAiSettings_Model::getSettingsProvider($values);

        if ('' === $provider) {
            $response->setResult([
                'success' => false,
                'message' => vtranslate('LBL_UNKNOWN_AI_PROVIDER', $request->getModule(false)),
            ]);
            $response->emit();

            return;
        }

        MailManager_ProAiSettings_Model::saveSettings(
            $provider,
            $values,
            (string)$request->getRaw('ai_priority_rules'),
            (string)$request->getRaw('api_key')
        );
        $response->setResult(['success' => true]);
        $response->emit();
    }
}
