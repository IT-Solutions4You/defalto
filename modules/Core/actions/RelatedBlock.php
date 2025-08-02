<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Core_RelatedBlock_Action extends Vtiger_Action_Controller
{
    /**
     * @throws Exception
     */
    public function process(Vtiger_Request $request): void
    {
        $moduleName = $request->getModule();
        $recordId = $request->getRecord();

        if ($recordId) {
            $instance = Core_RelatedBlock_Model::getInstanceById($recordId, $moduleName);
        } else {
            $instance = Core_RelatedBlock_Model::getInstance($moduleName);
        }

        $instance->retrieveFromRequest($request);
        $instance->save();

        header('location:' . $instance->getEditViewUrl());
    }
}