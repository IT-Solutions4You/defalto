<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

trait InventoryItem_Detail_Trait
{
    public function adaptDetail(Vtiger_Request $request, Vtiger_Viewer $viewer)
    {
        $productModuleModel = Vtiger_Module_Model::getInstance('Products');
        $viewer->assign('PRODUCT_ACTIVE', $productModuleModel->isActive());

        $serviceModuleModel = Vtiger_Module_Model::getInstance('Services');
        $viewer->assign('SERVICE_ACTIVE', $serviceModuleModel->isActive());


        /*show($request->getAll());
        $entityModuleModel = Vtiger_Module_Model::getInstance($request->get('module'));
        $entityRecordModel = Vtiger_Record_Model::getInstanceById($request->get('record'), $entityModuleModel);
        $inventoryItemModuleModel = Vtiger_Module_Model::getInstance('InventoryItem');
        $relationModel = Vtiger_Relation_Model::getInstance($entityModuleModel, $inventoryItemModuleModel);
        $query = $relationModel->getQuery($entityRecordModel);
        show($query);*/
    }
}

