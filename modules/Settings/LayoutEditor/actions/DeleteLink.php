<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o.
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Settings_LayoutEditor_DeleteLink_Action extends Settings_Vtiger_Index_Action
{
    public function validateRequest(Vtiger_Request $request): bool
    {
        return $request->validateWriteAccess();
    }

    public function process(Vtiger_Request $request)
    {
        $link = (new Settings_LayoutEditor_LinkSettings_Model())->deleteModuleLink(
            (string)$request->get('sourceModule'),
            (string)$request->get('linkId')
        );
        $response = new Vtiger_Response();
        $response->setResult(['message' => vtranslate('LBL_LINK_DELETED', 'Core'), 'link' => $link]);
        $response->emit();
    }
}
