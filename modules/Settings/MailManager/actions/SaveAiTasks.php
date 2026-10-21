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
 * Saves which agent does each AI task, and the triage cascade (I13)
 */
class Settings_MailManager_SaveAiTasks_Action extends Settings_MailManager_ProAiAbstract_Action
{
    /**
     * @param Vtiger_Request $request
     *
     * @return void
     */
    public function process(Vtiger_Request $request)
    {
        MailManager_ProAiAgent_Model::saveTaskSettings(
            (array)json_decode((string)$request->getRaw('tasks'), true),
            '1' === $request->get('cascade'),
            (int)$request->get('fallback')
        );
        $this->emit(['success' => true]);
    }
}
