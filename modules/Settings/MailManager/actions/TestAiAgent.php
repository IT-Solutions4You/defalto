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
 * Tests an AI agent with a sample e-mail (I13): its answer, the time and the tokens
 */
class Settings_MailManager_TestAiAgent_Action extends Settings_MailManager_ProAiAbstract_Action
{
    /**
     * @param Vtiger_Request $request
     *
     * @return void
     */
    public function process(Vtiger_Request $request)
    {
        $agent = MailManager_ProAiAgent_Model::getById((int)$request->get('agentid'));

        if (!$agent) {
            $this->emit(['success' => false, 'message' => vtranslate('LBL_AGENT_INVALID', $request->getModule(false))]);

            return;
        }

        session_write_close();

        // the test checks this agent, never the backup — a simulated outage says so
        $this->emit(MailManager_ProAiAgent_Model::testAgent($agent));
    }
}
