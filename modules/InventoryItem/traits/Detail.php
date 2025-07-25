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
    protected array $specialTreatmentFields = ['discount', 'overall_discount',];

    /**
     * Add product block into Detail View
     *
     * @param Vtiger_Request $request
     * @param Vtiger_Viewer  $viewer
     *
     * @return void
     * @throws AppException
     */
    public function adaptDetail(Vtiger_Request $request, Vtiger_Viewer $viewer)
    {
        $recordId = (int)$request->get('record');
        $viewer->assign('ITEM_MODULES', InventoryItem_ItemModules_Model::getItemModules());
        $viewer->assign('EXCLUDED_FIELDS', InventoryItem_Field_Model::excludedFields);
        $viewer->assign('TOTAL_FIELDS', InventoryItem_Field_Model::totalFields);
        $viewer->assign('SPECIAL_TREATMENT_FIELDS', $this->specialTreatmentFields);
        $viewer->assign('EMPTY_ROW', $this->getEmptyRow());
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

        $viewer->assign('INVENTORY_ITEM_COLUMNS', $selectedFields);
        $viewer->assign('FINALS_COLSPAN', $selectedFieldsCount);

        $items = InventoryItem_Utils_Helper::fetchItems($recordId);

        if (!in_array('margin', $selectedFields)) {
            foreach ($items as &$item) {
                $item['margin_amount_display'] = $item['margin_amount_display'] . '&nbsp;<small>(' . $item['margin_display'] . '%)</small>';
            }
        }

        $viewer->assign('INVENTORY_ITEMS', $items);

        $recordModel = Vtiger_Record_Model::getCleanInstance('InventoryItem');
        $recordStructureInstance = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($recordModel, Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_EDIT);
        $recordStructure = [];

        foreach ($recordStructureInstance->getStructure() as $value) {
            foreach ($value as $key2 => $value2) {
                $recordStructure[$key2] = $value2;
            }
        }

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
        $viewer->assign('PRICEBOOKS', $this->fetchPriceBooks($request));
    }

    /**
     * Get (default) empty row as line item
     *
     * @return array
     */
    private function getEmptyRow(): array
    {
        $db = PearDatabase::getInstance();
        $columns = $db->getColumnNames('df_inventoryitem');
        $columns['entityType'] = '';

        return $columns;
    }

    /**
     * Get all PriceBooks.
     * Restricted to Price Books with the same currency as the entity has.
     * If the actual Price Book has different currency, it is added at the end.
     *
     * @param Vtiger_Request $request
     *
     * @return array [id => name]
     */
    private function fetchPriceBooks(Vtiger_Request $request): array
    {
        $records = [];
        $db = PearDatabase::getInstance();
        $module = $request->get('module');
        $user = Users_Record_Model::getCurrentUserModel();

        $recordModel = Vtiger_Record_Model::getInstanceById($request->get('record'), $module);
        $currencyId = $recordModel->get('currency_id');
        $selectedPriceBookId = $recordModel->get('pricebookid');

        $pagingModel = new Vtiger_Paging_Model();
        $pagingModel->set('page', 1);
        $pagingModel->set('limit', 1000);

        $forModule = 'PriceBooks';
        $queryGenerator = new EnhancedQueryGenerator($forModule, $user);
        $queryGenerator->setFields(['id', 'bookname']);

        $moduleSpecificControllerPath = 'modules/' . $forModule . '/controllers/ListViewController.php';

        if (file_exists($moduleSpecificControllerPath)) {
            include_once $moduleSpecificControllerPath;
            $moduleSpecificControllerClassName = $forModule . 'ListViewController';
            $controller = new $moduleSpecificControllerClassName($db, $user, $queryGenerator);
        } else {
            $controller = new ListViewController($db, $user, $queryGenerator);
        }

        $listViewModel = Vtiger_ListView_Model::getCleanInstance($forModule)->set('query_generator', $queryGenerator)->set('listview_controller', $controller);

        if ($currencyId) {
            $queryGenerator = $listViewModel->get('query_generator');
            $queryGenerator->addUserSearchConditions(['search_field' => 'currency_id', 'search_text' => $currencyId, 'operator' => 'e']);
        }

        $records = [];
        $listViewRecords = $listViewModel->getListViewEntries($pagingModel);

        foreach ($listViewRecords as $listViewRecord) {
            $data = $listViewRecord->getData();
            $records[$data['id']] = $data['bookname'];
        }

        if (!isset($records[$selectedPriceBookId])) {
            $records[$selectedPriceBookId] = getEntityName('PriceBooks', $selectedPriceBookId)[$selectedPriceBookId];
        }

        return $records;
    }

    /**
     * Used to inject custom js files into getHeaderScripts method
     *
     * @return Array
     */
    public function adaptHeaderScripts(): array
    {
        return [
            'modules.InventoryItem.resources.InventoryItemDetail',
            'modules.Vtiger.resources.Popup',
            'modules.InventoryItem.resources.ItemsPopup',
        ];
    }
}