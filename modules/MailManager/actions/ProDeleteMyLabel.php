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
 * My Settings — deletes one of the current user's own email labels
 */
class MailManager_ProDeleteMyLabel_Action extends MailManager_ProAbstract_Action
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
        // the user's own labels; the admin deletes the mailbox owner's
        $userId = MailManager_ProMySettings_Model::getLabelOwnerId((int)$request->get('mailboxid'));

        $deleted = Settings_MailManager_Label_Model::delete((int)$request->get('labelid'), $userId);
        $response->setResult($deleted
            ? ['success' => true]
            : ['success' => false, 'message' => vtranslate('LBL_LABEL_NOT_FOUND', 'Settings:MailManager')]);
        $response->emit();
    }
}
