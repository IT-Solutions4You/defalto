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
 * Moves the agents to a provider with the recommended model of their type (I13)
 */
class Settings_MailManager_ApplyAiRecommended_Action extends Settings_MailManager_ProAiAbstract_Action
{
    /**
     * @param Vtiger_Request $request
     *
     * @return void
     */
    public function process(Vtiger_Request $request)
    {
        $provider = (string)$request->get('provider');

        if (!in_array($provider, MailManager_ProAiConfig_Model::PROVIDERS, true) || !MailManager_ProAiConfig_Model::hasApiKey($provider)) {
            $this->emit(['success' => false, 'message' => vtranslate('LBL_MISSING_API_KEY', $request->getModule(false))]);

            return;
        }

        try {
            $this->emit(['success' => true, 'changed' => MailManager_ProAiAgent_Model::applyRecommended($provider)]);
        } catch (Exception $e) {
            $this->emit(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
