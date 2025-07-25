<?php
/**
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (c) vtiger.
 * Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
 * All Rights Reserved.
 */

class InventoryItem_ItemsWidget_View extends Vtiger_Index_View
{
    public function process(Vtiger_Request $request)
    {
        global $current_user;
        $recordId = (int)$request->get('for_record');
        $forModule = $request->get('for_module');
        $entityRecordModel = Vtiger_Record_Model::getInstanceById($recordId, $forModule);
        $items = InventoryItem_Utils_Helper::fetchItems($recordId);

        $viewer = $this->getViewer($request);
        $viewer->assign('ITEMS', $items);
        $viewer->assign('MODULE', 'InventoryItem');
        $viewer->assign('FOR_RECORD', $recordId);
        $viewer->assign('FOR_MODULE', $forModule);
        $viewer->assign('ENTITY_MODEL', $entityRecordModel);
        $viewer->assign('PRICE_WITHOUT_VAT_DISPLAY', CurrencyField::convertToUserFormat($entityRecordModel->get('price_after_overall_discount'), $current_user, true));
        $viewer->assign('VAT_DISPLAY', CurrencyField::convertToUserFormat($entityRecordModel->get('tax_amount'), $current_user, true));
        $viewer->assign('PRICE_TOTAL_DISPLAY', CurrencyField::convertToUserFormat($entityRecordModel->get('price_total'), $current_user, true));
        $viewer->assign('ADJUSTMENT', number_format($entityRecordModel->get('adjustment'), 2));
        $viewer->assign('ADJUSTMENT_DISPLAY', CurrencyField::convertToUserFormat($entityRecordModel->get('adjustment'), $current_user, true, false, true));
        $viewer->assign('GRAND_TOTAL_DISPLAY', CurrencyField::convertToUserFormat($entityRecordModel->get('grand_total'), $current_user, true));
        $viewer->view('ItemsWidget.tpl', 'InventoryItem');
    }
}