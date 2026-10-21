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
 * Saves the backup AI provider (I17): a provider with a key other than none needs the
 * admin's confirmation that data also goes to it during an outage; the notice address.
 */
class Settings_MailManager_SaveAiFailover_Action extends Settings_MailManager_ProAiAbstract_Action
{
    /**
     * @param Vtiger_Request $request
     *
     * @return void
     */
    public function process(Vtiger_Request $request)
    {
        $error = MailManager_ProAiFailover_Model::saveSettings(
            (string)$request->get('provider'),
            '1' === $request->get('confirmed'),
            (string)$request->get('email')
        );
        $this->emit('' === $error ? ['success' => true] : ['success' => false, 'message' => $error]);
    }
}
