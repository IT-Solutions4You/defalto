<?php
/**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *********************************************************************************/
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * Modifications and additions by IT-Solutions4You (ITS4YOU) are Copyright (c) IT-Solutions4You s.r.o.
 *
 * These contributions are licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class ProjectTask_SaveTask_Action extends Vtiger_Save_Action
{
    protected function getRecordModelFromRequest($request)
    {
        $module = Vtiger_Module_Model::getInstance('ProjectTask');

        foreach ($module->getFields() as $name => $field) {
            if ($request->has($name) && !$field->isEditable()) {
                throw new Exception(vtranslate('LBL_PERMISSION_DENIED'));
            }
        }

        return parent::getRecordModelFromRequest($request);
    }

    public function process(Vtiger_Request $request)
    {
        $recordModel = $this->saveRecord($request);
        $response = new Vtiger_Response();
        $response->setResult(['record' => $recordModel->getId(), 'module' => $recordModel->getModuleName()]);
        $response->emit();
    }
}
