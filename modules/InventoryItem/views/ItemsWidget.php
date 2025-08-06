<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
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
        $adjustment = $entityRecordModel->get('adjustment');

        if (empty($adjustment)) {
            $adjustment = 0.0;
        }

        $viewer = $this->getViewer($request);
        $viewer->assign('ITEMS', $items);
        $viewer->assign('MODULE', 'InventoryItem');
        $viewer->assign('FOR_RECORD', $recordId);
        $viewer->assign('FOR_MODULE', $forModule);
        $viewer->assign('ENTITY_MODEL', $entityRecordModel);
        $viewer->assign('PRICE_WITHOUT_VAT_DISPLAY', CurrencyField::convertToUserFormat($entityRecordModel->get('price_after_overall_discount'), $current_user, true));
        $viewer->assign('VAT_DISPLAY', CurrencyField::convertToUserFormat($entityRecordModel->get('tax_amount'), $current_user, true));
        $viewer->assign('PRICE_TOTAL_DISPLAY', CurrencyField::convertToUserFormat($entityRecordModel->get('price_total'), $current_user, true));
        $viewer->assign('ADJUSTMENT', number_format($adjustment, 2));
        $viewer->assign('ADJUSTMENT_DISPLAY', CurrencyField::convertToUserFormat($adjustment, $current_user, true, false, true));
        $viewer->assign('GRAND_TOTAL_DISPLAY', CurrencyField::convertToUserFormat($entityRecordModel->get('grand_total'), $current_user, true));
        $viewer->view('ItemsWidget.tpl', 'InventoryItem');
    }
}