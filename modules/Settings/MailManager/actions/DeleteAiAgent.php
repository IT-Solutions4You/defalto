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
 * Deletes an AI agent (I13); its tasks move to another agent, the last one stays
 */
class Settings_MailManager_DeleteAiAgent_Action extends Settings_MailManager_ProAiAbstract_Action
{
    /**
     * @param Vtiger_Request $request
     *
     * @return void
     */
    public function process(Vtiger_Request $request)
    {
        $deleted = MailManager_ProAiAgent_Model::deleteAgent((int)$request->get('agentid'));
        $this->emit($deleted
            ? ['success' => true]
            : ['success' => false, 'message' => vtranslate('LBL_AGENT_LAST', $request->getModule(false))]);
    }
}
