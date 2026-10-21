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
 * "Simulate an outage" of an AI provider (I17): for a while every call of it fails on
 * purpose, so the switch to the backup, the bar and the e-mails can be tried; or ends it.
 */
class Settings_MailManager_SimulateAiOutage_Action extends Settings_MailManager_ProAiAbstract_Action
{
    /**
     * @param Vtiger_Request $request
     *
     * @return void
     */
    public function process(Vtiger_Request $request)
    {
        $provider = (string)$request->get('provider');

        $on = '1' === $request->get('on');

        // without a backup a simulation would only switch the AI off
        if (!in_array($provider, MailManager_ProAiConfig_Model::PROVIDERS, true)
            || ($on && !MailManager_ProAiFailover_Model::canSimulate($provider))) {
            $this->emit(['success' => false]);

            return;
        }

        MailManager_ProAiFailover_Model::simulate($provider, $on);
        $this->emit(['success' => true]);
    }
}
