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
 * Saves the admin's prices per 1M tokens per model — the cost column of the usage (I13)
 */
class Settings_MailManager_SaveAiPrices_Action extends Settings_MailManager_ProAiAbstract_Action
{
    /**
     * @param Vtiger_Request $request
     *
     * @return void
     */
    public function process(Vtiger_Request $request)
    {
        MailManager_ProAiConfig_Model::saveEditedPrices((array)json_decode((string)$request->getRaw('prices'), true));
        $this->emit(['success' => true]);
    }
}
