<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class InventoryItem_DetailView_Modifier implements Core_Modifier_Interface
{
    protected array $specialTreatmentFields = ['discount', 'overall_discount',];

    /**
     * @inheritDoc
     */
    public function modifyProcess(Vtiger_Viewer $viewer, Vtiger_Request $request): void
    {
    }

    /**
     * Allows display of InventoryItem Block in other modules
     *
     * @param Vtiger_Viewer  $viewer
     * @param Vtiger_Request $request
     *
     * @return void
     * @throws Exception
     */
    public function modifyShowModuleDetailView(Vtiger_Viewer $viewer, Vtiger_Request $request)
    {
        $recordId = (int)$request->get('record');
        $viewer->assign('ITEM_MODULES', InventoryItem_ItemModules_Model::getItemModules());
        $viewer->assign('EXCLUDED_FIELDS', InventoryItem_Field_Model::excludedFields);
        $viewer->assign('TOTAL_FIELDS', InventoryItem_Field_Model::totalFields);
        $viewer->assign('SPECIAL_TREATMENT_FIELDS', $this->specialTreatmentFields);
        $viewer->assign('EMPTY_ROW', InventoryItem_Detail_Helper::getEmptyRow());
        $viewer->assign('CURRENT_USER_MODEL', Users_Record_Model::getCurrentUserModel());
        $selectedFields = InventoryItem_Module_Model::getSelectedFields(gettabid($request->getModule()));
        $selectedFieldsCount = count($selectedFields);

        foreach ($selectedFields as $value) {
            if (in_array($value, InventoryItem_Field_Model::preventDisplay)) {
                $selectedFieldsCount--;
            }
        }

        if (in_array('description', $selectedFields)) {
            $viewer->assign('DESCRIPTION_ALLOWED', true);
            $key = array_search('description', $selectedFields);

            if ($key !== false) {
                unset($selectedFields[$key]);
            }
        }

        $items = InventoryItem_Utils_Helper::fetchItems($recordId);

        if (!in_array('margin', $selectedFields)) {
            foreach ($items as &$item) {
                $item['margin_amount_display'] = $item['margin_amount_display'] . '&nbsp;<small>(' . $item['margin_display'] . '%)</small>';
            }
        }

        $viewer->assign('INVENTORY_ITEMS', $items);
        $viewer->assign('INVENTORY_ITEMS_COUNT', count($items));

        $recordModel = Vtiger_Record_Model::getCleanInstance('InventoryItem');
        $recordStructureInstance = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($recordModel, Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_EDIT);
        $recordStructure = [];

        foreach ($recordStructureInstance->getStructure() as $value) {
            foreach ($value as $key2 => $value2) {
                $recordStructure[$key2] = $value2;
            }
        }

        foreach ($selectedFields as $fieldName) {
            if (!isset($recordStructure[$fieldName])) {
                unset($selectedFields[$fieldName]);
                $selectedFieldsCount--;
            }
        }

        $viewer->assign('INVENTORY_ITEM_COLUMNS', $selectedFields);
        $viewer->assign('FINALS_COLSPAN', $selectedFieldsCount);
        $viewer->assign('INVENTORY_ITEM_RECORD_STRUCTURE', $recordStructure);

        $currentUser = Users_Record_Model::getCurrentUserModel();
        $entityRecordModel = Vtiger_Record_Model::getInstanceById($recordId);
        $overallDiscount = $entityRecordModel->get('overall_discount');
        $overallDiscountAmount = $entityRecordModel->get('overall_discount_amount');
        $adjustment = $entityRecordModel->get('adjustment');

        if (!$overallDiscount) {
            $overallDiscount = 0;
        }

        if (!$overallDiscountAmount) {
            $overallDiscountAmount = 0;
        }

        if (!$adjustment) {
            $adjustment = 0;
        }

        $viewer->assign('RECORD', $entityRecordModel);
        $viewer->assign('SUBTOTAL_DISPLAY', CurrencyField::convertToUserFormat($entityRecordModel->get('price_after_discount'), $currentUser, true));
        $viewer->assign('OVERALL_DISCOUNT', number_format($overallDiscount, 2));
        $viewer->assign('OVERALL_DISCOUNT_AMOUNT', number_format($overallDiscountAmount, 2));
        $viewer->assign('OVERALL_DISCOUNT_AMOUNT_DISPLAY', CurrencyField::convertToUserFormat($overallDiscountAmount, $currentUser, true, false, true));
        $viewer->assign('PRICE_WITHOUT_VAT_DISPLAY', CurrencyField::convertToUserFormat($entityRecordModel->get('price_after_overall_discount'), $currentUser, true));
        $viewer->assign('VAT_DISPLAY', CurrencyField::convertToUserFormat($entityRecordModel->get('tax_amount'), $currentUser, true));
        $viewer->assign('PRICE_TOTAL_DISPLAY', CurrencyField::convertToUserFormat($entityRecordModel->get('price_total'), $currentUser, true));
        $viewer->assign('ADJUSTMENT', number_format($adjustment, 2));
        $viewer->assign('ADJUSTMENT_DISPLAY', CurrencyField::convertToUserFormat($adjustment, $currentUser, true, false, true));
        $viewer->assign('GRAND_TOTAL_DISPLAY', CurrencyField::convertToUserFormat($entityRecordModel->get('grand_total'), $currentUser, true));
        $viewer->assign('PRICEBOOKS', InventoryItem_Detail_Helper::fetchPriceBooks($request));
    }

    /**
     * Modifies an array of .js files that should be loaded so that the InventoryItem block could provide its functionality
     *
     * @param array          $jsFileNames
     * @param Vtiger_Request $request
     *
     * @return void
     */
    public function modifyGetHeaderScripts(array &$jsFileNames, Vtiger_Request $request): void
    {
        $myJsFileNames = [
            'modules.Vtiger.resources.Detail',
            'modules.InventoryItem.resources.InventoryItemDetail',
            'modules.Vtiger.resources.Popup',
            'modules.InventoryItem.resources.ItemsPopup',
        ];
        $jsFileNames = array_merge($jsFileNames, $myJsFileNames);
    }
}