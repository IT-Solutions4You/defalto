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
 * Export of the Payments list into one file: the rows come from the union of the payment modules in list order,
 * the values from the core export of each payment module (Payments_SourceExport_Model). The columns are the fields
 * of all payment modules; a field a module does not have stays empty in its rows. The direction field tells the
 * module of a row.
 */
class Payments_ExportData_Action extends Vtiger_ExportData_Action
{
    /**
     * Exports all payments of the filter, the current page or the selected payments.
     *
     * @param Vtiger_Request $request Export request (mode, viewname, search, ordering, export_type)
     * @return void
     * @throws Exception
     */
    public function ExportData(Vtiger_Request $request)
    {
        $rows = $this->getExportRows($request);
        $sources = Payments_Sources_Helper::getAccessibleSources();
        $idsBySource = [];

        foreach ($rows as $row) {
            $idsBySource[$row['source']][] = $row['id'];
        }

        $headers = $sourceRows = [];

        foreach ($idsBySource as $sourceKey => $recordIds) {
            if (!isset($sources[$sourceKey])) {
                continue;
            }

            $sourceData = (new Payments_SourceExport_Model())->getSourceData($request, $sources[$sourceKey]['module'], $recordIds);
            $headers += $sourceData['headers'];
            $sourceRows[$sourceKey] = $sourceData['rows'];
        }

        $entries = [];

        foreach ($rows as $row) {
            $values = $sourceRows[$row['source']][$row['id']] ?? null;

            if (null === $values) {
                continue;
            }

            $entry = [];

            foreach (array_keys($headers) as $fieldName) {
                $entry[] = $values[$fieldName] ?? '';
            }

            $entries[] = $entry;
        }

        $this->output($request, array_values($headers), $entries);
    }

    /**
     * Returns the payments to export in list order: all payments of the filter (without the list search),
     * the current page, or the selected payments.
     *
     * @param Vtiger_Request $request Export request
     * @return array<int, array{source: string, id: int}>
     */
    protected function getExportRows(Vtiger_Request $request): array
    {
        $listViewModel = Payments_ListView_Model::getInstanceForRequest($request);

        switch ($request->getMode()) {
            case 'ExportSelectedRecords':
                return $listViewModel->getSelectedRows($request);
            case 'ExportCurrentPage':
                $pageLimit = (int)(new Vtiger_Paging_Model())->getPageLimit();
                $page = max(1, (int)$request->get('page'));

                return $listViewModel->getUnionQuery()->getPageRows(($page - 1) * $pageLimit, $pageLimit);
            default:
                $listViewModel->set('search_params', [])->set('search_key', '')->set('search_value', '')->set('operator', '');

                return $listViewModel->getUnionQuery()->getAllRows();
        }
    }
}
