<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

/**
 * List of the records of all payment modules. A union of the source queries gives the order, the page and the count;
 * the rows of the page are loaded and formatted by the list code of their own module and link to that module.
 */
class Payments_ListView_Model extends Vtiger_ListView_Model
{
    /**
     * Returns the list columns as field models of the payment modules, in the order of the filter.
     *
     * @return array<string, Vtiger_Field_Model>
     */
    public function getListViewHeaders()
    {
        $moduleModel = $this->getModule();
        $headers = [];

        foreach ($this->getQueryGenerator()->getFields() as $fieldName) {
            $field = $moduleModel->getField($fieldName);

            if (!$field || !$field->isViewable() || '6' === (string)$field->get('displaytype')) {
                continue;
            }

            $field->set('listViewRawFieldName', $field->get('column'));
            $headers[$fieldName] = $field;
        }

        return $headers;
    }

    /**
     * Returns the records of one page in list order, each as a record model of its payment module.
     *
     * @param Vtiger_Paging_Model $pagingModel Paging of the list
     * @return array<int, Vtiger_Record_Model>
     */
    public function getListViewEntries($pagingModel)
    {
        $pageLimit = (int)$pagingModel->getPageLimit();
        $rows = $this->getUnionQuery()->getPageRows((int)$pagingModel->getStartIndex(), $pageLimit + 1);
        $sourceRecords = $this->getSourceRecords($rows);
        $records = [];

        foreach ($rows as $row) {
            $record = $sourceRecords[$row['source']][$row['id']] ?? null;

            if ($record) {
                $records[$row['id']] = $record;
            }
        }

        $pagingModel->calculatePageRange($records);

        if (count($rows) > $pageLimit) {
            array_pop($records);
            $pagingModel->set('nextPageExists', true);
        } else {
            $pagingModel->set('nextPageExists', false);
        }

        return $records;
    }

    /**
     * Returns one add button per payment module the user may create records in; it opens that module's form.
     *
     * @return array
     */
    public function getBasicLinks()
    {
        $links = [];

        foreach (Payments_Sources_Helper::getAccessibleSources() as $source) {
            $moduleName = $source['module'];

            if (isset($links[$moduleName]) || !Users_Privileges_Model::isPermitted($moduleName, 'CreateView')) {
                continue;
            }

            $links[$moduleName] = [
                'linktype' => 'LISTVIEWBASIC',
                'linklabel' => 'LBL_ADD_' . strtoupper($source['direction']) . '_PAYMENT',
                'linkurl' => Vtiger_Module_Model::getInstance($moduleName)->getCreateRecordUrl(),
                'linkicon' => '',
                'style_class' => Vtiger_Link_Model::PRIMARY_STYLE_CLASS,
            ];
        }

        return array_values($links);
    }

    /**
     * Returns the mass actions of Payments: delete, when the user may delete in at least one payment module.
     * Payments_MassDelete_Action deletes each payment in its own module; editing happens in the payment modules.
     *
     * @param array $linkParams Link parameters
     * @return array
     */
    public function getListViewMassActions($linkParams)
    {
        $links = ['LISTVIEWMASSACTION' => []];

        foreach (Payments_Sources_Helper::getAccessibleSources() as $source) {
            if (Users_Privileges_Model::isPermitted($source['module'], 'Delete')) {
                $links['LISTVIEWMASSACTION'][] = Vtiger_Link_Model::getInstanceFromValues([
                    'linktype' => 'LISTVIEWMASSACTION',
                    'linklabel' => 'LBL_DELETE',
                    'linkurl' => 'javascript:Vtiger_List_Js.massDeleteRecords("index.php?module=' . $this->getModule()->getName() . '&action=MassDelete");',
                    'linkicon' => '',
                ]);
                break;
            }
        }

        return $links;
    }

    /**
     * Creates the list of a mass action or an export request: its filter, list search, simple search and ordering.
     *
     * @param Vtiger_Request $request Request with viewname, search_params, search_key, search_value, operator,
     *                                orderby and sortorder
     * @return self
     */
    public static function getInstanceForRequest(Vtiger_Request $request): self
    {
        $moduleName = Payments_Sources_Helper::MODULE;
        /** @var self $listViewModel */
        $listViewModel = Vtiger_ListView_Model::getInstance($moduleName, (int)$request->get('viewname'));
        $searchParams = $request->get('search_params');
        $searchParams = is_array($searchParams) ? $searchParams : [];
        $tagParams = $request->get('tag_params');
        $searchParams = array_merge($searchParams, is_array($tagParams) ? $tagParams : []);

        return $listViewModel
            ->set('search_params', Vtiger_Util_Helper::transferListSearchParamsToFilterCondition($searchParams, $listViewModel->getModule()))
            ->set('search_key', (string)$request->get('search_key'))
            ->set('search_value', (string)$request->get('search_value'))
            ->set('operator', (string)$request->get('operator'))
            ->set('orderby', (string)$request->get('orderby'))
            ->set('sortorder', (string)$request->get('sortorder'));
    }

    /**
     * Returns the rows a mass action or an export of selected records applies to, in list order: the selected
     * records, or with selected_ids = all every record of the list except the excluded ones.
     *
     * @param Vtiger_Request $request Mass action request
     * @return array<int, array{source: string, id: int}>
     */
    public function getSelectedRows(Vtiger_Request $request): array
    {
        $selectedIds = $request->get('selected_ids');
        $excludedIds = array_map('intval', (array)($request->get('excluded_ids') ?: []));
        $rows = $this->getUnionQuery()->getAllRows();

        if ('all' === $selectedIds) {
            return array_values(array_filter($rows, static fn(array $row): bool => !in_array($row['id'], $excludedIds, true)));
        }

        $selectedIds = array_map('intval', (array)($selectedIds ?: []));

        return array_values(array_filter($rows, static fn(array $row): bool => in_array($row['id'], $selectedIds, true)));
    }

    /**
     * Returns the number of payments matching the filter and the search in all sources.
     *
     * @return int
     */
    public function getListViewCount()
    {
        return $this->getUnionQuery()->getCount();
    }

    /**
     * Creates the union of the source queries for the current filter, search and ordering.
     *
     * @return Payments_UnionQuery_Model
     */
    public function getUnionQuery(): Payments_UnionQuery_Model
    {
        $userSearch = [];

        if ($this->get('search_key')) {
            $userSearch = [
                'search_field' => (string)$this->get('search_key'),
                'search_text' => (string)$this->get('search_value'),
                'operator' => (string)$this->get('operator'),
            ];
        }

        return Payments_UnionQuery_Model::getInstance(
            $this->getQueryGenerator()->getPaymentsViewId(),
            (array)($this->get('search_params') ?: []),
            $userSearch,
            $this->getOrderFieldName(),
            (string)$this->get('sortorder')
        );
    }

    /**
     * Returns the field the list is ordered by: the clicked column, the filter's sorting, or the creation time.
     *
     * @return string
     */
    public function getOrderFieldName(): string
    {
        $orderBy = (string)$this->get('orderby');

        if ($orderBy !== '' && $this->getModule()->getField($orderBy)) {
            return $orderBy;
        }

        $customView = CustomView_Record_Model::getInstanceById($this->getQueryGenerator()->getPaymentsViewId());
        $filterOrderBy = $customView ? $customView->fetchOrderBy() : [];
        $filterField = $this->getModule()->getFieldByColumn($filterOrderBy['orderby'] ?? '');

        if ($filterField) {
            $this->set('sortorder', $filterOrderBy['sortorder'] ?? 'DESC');

            return $filterField->getName();
        }

        $this->set('sortorder', 'DESC');

        return Payments_SourceQuery_Model::ALWAYS_FILLED_FIELD;
    }

    /**
     * Loads the records of a page by the list query of each payment module, keyed by source and record id.
     *
     * @param array<int, array{source: string, id: int}> $rows Rows of the page
     * @return array<string, array<int, Vtiger_Record_Model>>
     */
    protected function getSourceRecords(array $rows): array
    {
        $idsBySource = [];

        foreach ($rows as $row) {
            $idsBySource[$row['source']][] = $row['id'];
        }

        $sources = Payments_Sources_Helper::getAccessibleSources();
        $records = [];

        foreach ($idsBySource as $sourceKey => $recordIds) {
            if (isset($sources[$sourceKey])) {
                $records[$sourceKey] = $this->getSourceModuleRecords($sources[$sourceKey], $recordIds);
            }
        }

        return $records;
    }

    /**
     * Loads records of one source with the list columns, formatted by the module's list controller.
     *
     * @param array      $source    Source configuration
     * @param array<int> $recordIds Record ids
     * @return array<int, Vtiger_Record_Model>
     */
    protected function getSourceModuleRecords(array $source, array $recordIds): array
    {
        $db = PearDatabase::getInstance();
        $user = Users_Record_Model::getCurrentUserModel();
        $moduleName = $source['module'];
        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
        $query = new Core_QueryGenerator_Model($moduleName, $user);
        $listFields = array_values(array_diff($this->getQueryGenerator()->getFields(), $source['missing_fields']));
        $query->setFields(array_unique(array_merge($listFields, ['id'])));
        $query->addRecordIdsCondition($recordIds);
        $result = $db->pquery($query->getQuery(), $query->getQueryParameters());
        $controller = new ListViewController($db, $user, $query);
        $entries = $controller->getListViewRecords(CRMEntity::getInstance($moduleName), $moduleName, $result);
        $records = [];
        $index = 0;

        foreach ($entries as $recordId => $entry) {
            $rawData = $db->query_result_rowdata($result, $index++);

            foreach ($source['missing_fields'] as $fieldName) {
                $entry[$fieldName] = '';
            }

            $entry['id'] = $recordId;
            $records[(int)$recordId] = $moduleModel->getRecordFromArray($entry, $rawData)
                ->set('payments_direction', $source['direction']);
        }

        return $records;
    }
}
