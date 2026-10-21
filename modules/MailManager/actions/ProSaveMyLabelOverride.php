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
 * A user's own setting of a GLOBAL label for the personal mailboxes — what
 * the label suggests, whether it acts by itself and what the AI sorts under
 * it; op=reset returns to the common setting. The label itself (name, color)
 * stays the admin's
 */
class MailManager_ProSaveMyLabelOverride_Action extends MailManager_ProAbstract_Action
{
    /**
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
     * @param Vtiger_Request $request
     * @return void
     */
    public function process(Vtiger_Request $request)
    {
        $response = new Vtiger_Response();
        // the user's own setting; the admin edits the mailbox owner's
        $userId = MailManager_ProMySettings_Model::getLabelOwnerId((int)$request->get('mailboxid'));
        $labelId = (int)$request->get('labelid');

        try {
            if ($request->get('op') === 'reset') {
                Settings_MailManager_Label_Model::deleteOverride($labelId, $userId);
            } else {
                Settings_MailManager_Label_Model::saveOverride($labelId, $userId, [
                    // as typed — get() html-encodes "&" and the output escapes once more
                    'ai_description' => trim((string)$request->getRaw('ai_description')),
                    'suggested_module' => $request->get('suggested_module'),
                    'auto_action' => $request->get('auto_action') === '1',
                    'ai_enabled' => $request->get('ai_enabled') === '1',
                ]);
            }
            $response->setResult(['success' => true]);
        } catch (Exception $e) {
            $response->setResult(['success' => false, 'message' => $e->getMessage()]);
        }

        $response->emit();
    }
}
