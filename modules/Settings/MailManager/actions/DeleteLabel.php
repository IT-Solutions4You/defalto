<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Settings_MailManager_DeleteLabel_Action extends Settings_MailManager_ProAbstract_Action
{
    /**
     * @param Vtiger_Request $request
     * @return void
     */
    public function process(Vtiger_Request $request)
    {
        $response = new Vtiger_Response();

        $labelId = (int)$request->get('labelid');
        // the admin manages the global labels only — the users' own stay theirs
        if (!$labelId || !Settings_MailManager_Label_Model::getOwned($labelId, null)) {
            $response->setResult([
                'success' => false,
                'message' => vtranslate('LBL_LABEL_NOT_FOUND', 'Settings:MailManager'),
            ]);
            $response->emit();

            return;
        }

        try {
            Settings_MailManager_Label_Model::delete($labelId);
            $response->setResult(['success' => true]);
        } catch (Exception $e) {
            $response->setResult(['success' => false, 'message' => $e->getMessage()]);
        }

        $response->emit();
    }
}
