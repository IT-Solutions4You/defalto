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
 * Switches the browser notifications of new mail on or off — the current
 * user's own setting (My settings, Notifications)
 */
class MailManager_ProSaveMyNotifications_Action extends MailManager_ProAbstract_Action
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
     *
     * @return void
     */
    public function process(Vtiger_Request $request)
    {
        MailManager_ProNotifier_Model::setEnabled(
            (int)Users_Record_Model::getCurrentUserModel()->getId(),
            (bool)$request->get('enabled')
        );

        $response = new Vtiger_Response();
        $response->setResult(['success' => true]);
        $response->emit();
    }
}
