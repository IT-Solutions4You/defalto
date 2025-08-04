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

class PriceBooks_RelationAjax_Action extends Vtiger_RelationAjax_Action
{
    function process(Vtiger_Request $request)
    {
        $mode = $request->get('mode');
        if (!empty($mode) && method_exists($this, "$mode")) {
            $this->$mode($request);

            return;
        }
    }

    public function requiresPermission(Vtiger_Request $request)
    {
        $permissions = parent::requiresPermission($request);
        $mode = $request->getMode();
        if (!empty($mode)) {
            switch ($mode) {
                case 'addListPrice':
                    $permissions[] = ['module_parameter' => 'module', 'action' => 'DetailView', 'record_parameter' => 'src_record'];
                    $permissions[] = ['module_parameter' => 'related_module', 'action' => 'DetailView'];
                    break;
                default:
                    break;
            }
        }

        return $permissions;
    }

    /**
     * Function adds PriceBooks-Products Relation
     *
     * @param type $request
     */
    public function addListPrice($request)
    {
        $sourceModule = $request->getModule();
        $sourceRecordId = $request->get('src_record');
        $relatedModule = $request->get('related_module');
        $relInfos = $request->get('relinfo');

        $sourceModuleModel = Vtiger_Module_Model::getInstance($sourceModule);
        $relatedModuleModel = Vtiger_Module_Model::getInstance($relatedModule);
        $relationModel = Vtiger_Relation_Model::getInstance($sourceModuleModel, $relatedModuleModel);

        foreach ($relInfos as $relInfo) {
            $price = Vtiger_Currency_UIType::convertToDBFormat($relInfo['price'], null, true);
            $relationModel->addListPrice($sourceRecordId, $relInfo['id'], $price);
        }
    }

    /*
     * Function to add relation for specified source record id and related record id list
     * @param <array> $request
     */
    function addRelation($request)
    {
        $sourceModule = $request->getModule();
        $sourceRecordId = $request->get('src_record');

        $relatedModule = $request->get('related_module');
        $relatedRecordIdList = $request->get('related_record_list');

        $sourceModuleModel = Vtiger_Module_Model::getInstance($sourceModule);
        $relatedModuleModel = Vtiger_Module_Model::getInstance($relatedModule);
        $relationModel = Vtiger_Relation_Model::getInstance($sourceModuleModel, $relatedModuleModel);

        foreach ($relatedRecordIdList as $relatedRecordId) {
            $relationModel->addRelation($sourceRecordId, $relatedRecordId);
        }
    }

    /**
     * Function to delete the relation for specified source record id and related record id list
     *
     * @param <array> $request
     */
    function deleteRelation($request)
    {
        $sourceModule = $request->getModule();
        $sourceRecordId = $request->get('src_record');

        $relatedModule = $request->get('related_module');
        $relatedRecordIdList = $request->get('related_record_list');

        $sourceModuleModel = Vtiger_Module_Model::getInstance($sourceModule);
        $relatedModuleModel = Vtiger_Module_Model::getInstance($relatedModule);
        $relationModel = PriceBooks_Relation_Model::getInstance($sourceModuleModel, $relatedModuleModel);

        foreach ($relatedRecordIdList as $relatedRecordId) {
            $relationModel->deleteRelation($sourceRecordId, $relatedRecordId);
        }
    }
}