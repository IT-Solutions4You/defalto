<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o.
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class ProjectMilestone_SaveDate_Action extends Vtiger_Save_Action
{
    protected function getRecordModelFromRequest(Vtiger_Request $request)
    {
        $module = Vtiger_Module_Model::getInstance('ProjectMilestone');

        foreach ($module->getFields() as $name => $field) {
            if ($request->has($name) && !$field->isEditable()) {
                throw new Exception(vtranslate('LBL_PERMISSION_DENIED'));
            }
        }

        return parent::getRecordModelFromRequest($request);
    }

    public function process(Vtiger_Request $request)
    {
        $record = $this->saveRecord($request);
        $response = new Vtiger_Response();
        $response->setResult(['record' => $record->getId(), 'module' => $record->getModuleName()]);
        $response->emit();
    }
}
