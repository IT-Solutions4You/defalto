<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Settings_MailManager_SaveLabel_Action extends Settings_MailManager_ProAbstract_Action
{
    /**
     * @param Vtiger_Request $request
     * @return void
     */
    public function process(Vtiger_Request $request)
    {
        $response = new Vtiger_Response();

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
            ]);

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
