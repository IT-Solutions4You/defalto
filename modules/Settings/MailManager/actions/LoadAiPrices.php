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
 * "Load indicative prices" of the AI usage: the prices of the models in use from the
 * public OpenRouter catalog (USD); the admin's own prices stay unless "overwrite".
 */
class Settings_MailManager_LoadAiPrices_Action extends Settings_MailManager_ProAiAbstract_Action
{
    /**
     * @param Vtiger_Request $request
     *
     * @return void
     */
    public function process(Vtiger_Request $request)
    {
        try {
            $result = MailManager_ProAiPriceCatalog_Model::apply(
                MailManager_ProAiConfig_Model::getPriceModels(),
                '1' === $request->get('overwrite')
            );
            $this->emit(['success' => true] + $result);
        } catch (Exception $e) {
            $this->emit(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
