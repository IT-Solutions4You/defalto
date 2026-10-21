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
 * Saves the list tab the current user's inbox opens with (My settings, Mail list)
 */
class MailManager_ProSaveMyListTab_Action extends MailManager_ProAbstract_Action
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
        MailManager_ProThreadList_Model::saveDefaultListTab(
            (int)Users_Record_Model::getCurrentUserModel()->getId(),
            (string)$request->get('tab')
        );

        $response = new Vtiger_Response();
        $response->setResult(['success' => true]);
        $response->emit();
    }
}
