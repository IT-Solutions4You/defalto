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

class PriceBooks_SaveAjax_Action extends Vtiger_SaveAjax_Action
{
    public function saveRecord($request)
    {
        $recordModel = $this->getRecordModelFromRequest($request);
        vglobal('VTIGER_TIMESTAMP_NO_CHANGE_MODE', $request->get('_timeStampNoChangeMode', false));
        $recordModel->save();
        vglobal('VTIGER_TIMESTAMP_NO_CHANGE_MODE', false);
        if ($request->get('relationOperation')) {
            $parentModuleName = $request->get('sourceModule');
            $parentModuleModel = Vtiger_Module_Model::getInstance($parentModuleName);
            $parentRecordId = $request->get('sourceRecord');
            $relatedModule = $recordModel->getModule();
            $relatedRecordId = $recordModel->getId();

            $relationModel = Vtiger_Relation_Model::getInstance($parentModuleModel, $relatedModule);
            $relationModel->addRelation($parentRecordId, $relatedRecordId);

            //To store the relationship between Products/Services and PriceBooks
            if ($parentRecordId && ($parentModuleName === 'Products' || $parentModuleName === 'Services')) {
                $parentRecordModel = Vtiger_Record_Model::getInstanceById($parentRecordId, $parentModuleName);
                $sellingPricesList = $parentModuleModel->getPricesForProducts($recordModel->get('currency_id'), [$parentRecordId]);
                $recordModel->updateListPrice($parentRecordId, $sellingPricesList[$parentRecordId]);
            }
        }

        return $recordModel;
    }
}