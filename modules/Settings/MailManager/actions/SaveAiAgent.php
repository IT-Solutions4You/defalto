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
 * Creates or updates an AI agent (I13)
 */
class Settings_MailManager_SaveAiAgent_Action extends Settings_MailManager_ProAiAbstract_Action
{
    /**
     * @param Vtiger_Request $request
     *
     * @return void
     */
    public function process(Vtiger_Request $request)
    {
        try {
            $agentId = MailManager_ProAiAgent_Model::saveAgent([
                'agentid' => (int)$request->get('agentid'),
                'name' => (string)$request->getRaw('name'),
                'tier' => (string)$request->get('tier'),
                'active' => '1' === $request->get('active'),
                'provider' => (string)$request->get('provider'),
                'model' => (string)$request->get('model'),
                'effort' => (string)$request->get('effort'),
                'instructions' => (string)$request->getRaw('instructions'),
                'max_input_tokens' => (int)$request->get('max_input_tokens'),
                'backup_provider' => (string)$request->get('backup_provider'),
                'backup_model' => (string)$request->get('backup_model'),
            ]);
            $this->emit(['success' => true, 'agentid' => $agentId]);
        } catch (Exception $e) {
            $this->emit(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
