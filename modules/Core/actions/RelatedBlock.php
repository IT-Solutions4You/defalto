<?php
/*
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Core_RelatedBlock_Action extends Vtiger_Action_Controller
{
    /**
     * @throws AppException
     */
    public function process(Vtiger_Request $request): void
    {
        $moduleName = $request->getModule();
        $recordId = $request->getRecord();

        if($recordId) {
            $instance = Core_RelatedBlock_Model::getInstanceById($recordId, $moduleName);
        } else {
            $instance = Core_RelatedBlock_Model::getInstance($moduleName);
        }

        $instance->retrieveFromRequest($request);
        $instance->save();

        header('location:' . $instance->getEditViewUrl());
    }
}