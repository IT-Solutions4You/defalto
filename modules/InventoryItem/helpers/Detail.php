<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class InventoryItem_Detail_Helper
{
    /**
     * Get (default) empty row as line item
     *
     * @return array
     */
    public static function getEmptyRow(): array
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
    public static function fetchPriceBooks(Vtiger_Request $request): array
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

        if ($selectedPriceBookId && !isset($records[$selectedPriceBookId])) {
            $records[$selectedPriceBookId] = getEntityName('PriceBooks', $selectedPriceBookId)[$selectedPriceBookId];
        }

        return $records;
    }
}