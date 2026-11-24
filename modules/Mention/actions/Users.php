<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Mention_Users_Action extends Core_Controller_Action
{
    public function requiresPermission(Vtiger_Request $request): array
    {
        return [
            ['module_parameter' => 'sourceModule', 'action' => 'DetailView', 'record_parameter' => 'sourceRecord'],
        ];
    }

    public function process(Vtiger_Request $request)
    {
        $module = Vtiger_Module_Model::getInstance('Mention');
        $sourceModule = $request->get('sourceModule');
        $sourceRecord = (int)$request->get('sourceRecord');
        $users = [];

        if (vtlib_isModuleActive('Mention') && $module->isMentionContextPermitted($sourceModule, $sourceRecord)) {
            $users = $module->getMentionUsers($sourceModule, $sourceRecord);
        }

        $response = new Vtiger_Response();
        $response->setResult(['users' => $users]);
        $response->emit();
    }
}
