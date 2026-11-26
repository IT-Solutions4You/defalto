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
        $recordModel = Vtiger_Record_Model::getInstanceById($request->get('record'), $request->get('module'));
        $currencyId = (int)$recordModel->get('currency_id');
        $selectedPriceBookId = (int)$recordModel->get('pricebookid');

        $records = self::queryPriceBooks($currencyId);

        if ($selectedPriceBookId && !isset($records[$selectedPriceBookId])) {
            $records[$selectedPriceBookId] = getEntityName('PriceBooks', $selectedPriceBookId)[$selectedPriceBookId];
        }

        return $records;
    }

    /**
     * All the Price Books the user can see, with the currency of each one. The edit view filters them by the currency of the document
     * in the browser, because the currency can be changed there.
     *
     * @param int $selectedPriceBookId price book of the document, added when the user cannot see it in the list
     *
     * @return array [id => ['name' => string, 'currency_id' => int]]
     * @throws Exception
     */
    public static function fetchPriceBooksWithCurrency(int $selectedPriceBookId = 0): array
    {
        $names = self::queryPriceBooks(0);

        if ($selectedPriceBookId && !isset($names[$selectedPriceBookId])) {
            $names[$selectedPriceBookId] = getEntityName('PriceBooks', $selectedPriceBookId)[$selectedPriceBookId];
        }

        $currencies = [];

        if ($names) {
            $db = PearDatabase::getInstance();
            $result = $db->pquery(sprintf('SELECT pricebookid, currency_id FROM vtiger_pricebook WHERE pricebookid IN (%s)', generateQuestionMarks(array_keys($names))), array_keys($names));

            while ($row = $db->fetchByAssoc($result)) {
                $currencies[(int)$row['pricebookid']] = (int)$row['currency_id'];
            }
        }

        $records = [];

        foreach ($names as $id => $name) {
            $records[$id] = ['name' => $name, 'currency_id' => $currencies[$id] ?? 0];
        }

        return $records;
    }

    /**
     * @param int $currencyId only the price books of this currency, 0 for all of them
     *
     * @return array [id => name], by the permissions of the user
     */
    protected static function queryPriceBooks(int $currencyId): array
    {
        $db = PearDatabase::getInstance();
        $user = Users_Record_Model::getCurrentUserModel();

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
            $listViewModel->get('query_generator')->addUserSearchConditions(['search_field' => 'currency_id', 'search_text' => $currencyId, 'operator' => 'e']);
        }

        $records = [];

        foreach ($listViewModel->getListViewEntries($pagingModel) as $listViewRecord) {
            $data = $listViewRecord->getData();
            $records[$data['id']] = $data['bookname'];
        }

        return $records;
    }
}
