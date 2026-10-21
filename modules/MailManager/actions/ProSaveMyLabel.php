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
 * My Settings — saves one of the current user's own email labels
 */
class MailManager_ProSaveMyLabel_Action extends MailManager_ProAbstract_Action
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
        // the user's own labels; the admin edits the mailbox owner's
        $userId = MailManager_ProMySettings_Model::getLabelOwnerId((int)$request->get('mailboxid'));

        try {
            $labelId = Settings_MailManager_Label_Model::save([
                'labelid' => (int)$request->get('labelid'),
                // as typed — get() html-encodes "&" and the output escapes once more
                'name' => trim((string)$request->getRaw('name')),
                'color' => $request->get('color'),
                'ai_description' => trim((string)$request->getRaw('ai_description')),
                'suggested_module' => $request->get('suggested_module'),
                'active' => $request->get('active') === '1',
                'workspace' => $request->get('workspace') === '1',
                'auto_action' => $request->get('auto_action') === '1',
            ], $userId);

            $response->setResult([
                'success' => true,
                'label' => Settings_MailManager_Label_Model::describe(
                    Settings_MailManager_Label_Model::getById($labelId)
                ),
            ]);
        } catch (Exception $e) {
            $response->setResult(['success' => false, 'message' => $e->getMessage()]);
        }

        $response->emit();
    }
}
