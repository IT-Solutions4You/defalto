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
 * Returns the warnings shown in the delete confirmation of one record or a list selection. Other modules add them
 * through a modifier registered for the deleted module (modifiable DeleteWarningsAction, method modifyGetWarnings()).
 * The action only reads; the delete itself runs through DeleteAjax or MassDelete.
 */
class Core_DeleteWarnings_Action extends Vtiger_Mass_Action
{
    /**
     * @inheritDoc
     */
    public function requiresPermission(Vtiger_Request $request): array
    {
        $permissions = parent::requiresPermission($request);
        $permissions[] = ['module_parameter' => 'module', 'action' => 'Delete'];

        return $permissions;
    }

    /**
     * Returns the warnings for the records of the request (selected_ids, or the list selection with selected_ids=all).
     *
     * @param Vtiger_Request $request
     * @return void
     * @throws Exception
     */
    public function process(Vtiger_Request $request)
    {
        $recordIds = array_map('intval', (array)$this->getRecordsListFromRequest($request));
        $warnings = [];

        Core_Modifiers_Model::modifyVariableForClass(get_class($this), 'getWarnings', $request->getModule(), $warnings, $recordIds, $request);

        $response = new Vtiger_Response();
        $response->setResult(['warnings' => array_values(array_unique(array_filter($warnings)))]);
        $response->emit();
    }
}
