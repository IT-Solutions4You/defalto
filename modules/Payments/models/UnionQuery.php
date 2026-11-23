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
 * Joins the source queries of Payments with UNION ALL. The union only decides the order, the page and the count;
 * the rows of a page are then loaded by the list query of their own payment module.
 */
class Payments_UnionQuery_Model extends Vtiger_Base_Model
{
    /** @var array<string, Payments_SourceQuery_Model> */
    protected array $sourceQueries = [];

    /**
     * Creates the union for a Payments filter, the list search and the ordering.
     *
     * @param int    $viewId       Payments filter id
     * @param array  $searchParams List search in filter condition format
     * @param array  $userSearch   Simple search (search_field, search_text, operator) or an empty array
     * @param string $orderField   Field the list is ordered by
     * @param string $sortOrder    ASC or DESC
     * @return self
     */
    public static function getInstance(int $viewId, array $searchParams, array $userSearch, string $orderField, string $sortOrder): self
    {
        $instance = new self();
        $instance->set('order_field', $orderField);
        $instance->set('sort_order', 'ASC' === strtoupper($sortOrder) ? 'ASC' : 'DESC');
        $instance->retrieveSourceQueries($viewId, $searchParams, $userSearch);

        return $instance;
    }

    /**
     * Returns the source queries keyed by source key.
     *
     * @return array<string, Payments_SourceQuery_Model>
     */
    public function getSourceQueries(): array
    {
        return $this->sourceQueries;
    }

    /**
     * Returns the rows of one page in list order: [['source' => source key, 'id' => record id], ...].
     *
     * @param int $startIndex First row, counted from 0
     * @param int $limit      Number of rows
     * @return array<int, array{source: string, id: int}>
     */
    public function getPageRows(int $startIndex, int $limit): array
    {
        $union = $this->getUnionQueryData();

        if (!$union) {
            return [];
        }

        $sortOrder = $this->get('sort_order');
        $query = sprintf(
            'SELECT payments_union.payments_source, payments_union.payments_id FROM (%s) payments_union'
            . ' ORDER BY payments_union.payments_sort %s, payments_union.payments_id %s LIMIT %d, %d',
            $union['query'],
            $sortOrder,
            $sortOrder,
            max(0, $startIndex),
            max(0, $limit)
        );
        $db = PearDatabase::getInstance();
        $result = $db->pquery($query, $union['parameters']);
        $rows = [];

        while ($row = $db->fetchByAssoc($result)) {
            $rows[] = ['source' => (string)$row['payments_source'], 'id' => (int)$row['payments_id']];
        }

        return $rows;
    }

    /**
     * Returns all rows of the union in list order, for exports and mass actions.
     *
     * @return array<int, array{source: string, id: int}>
     */
    public function getAllRows(): array
    {
        $count = $this->getCount();

        return $count ? $this->getPageRows(0, $count) : [];
    }

    /**
     * Returns the number of payments matching the filter and the search in all sources.
     *
     * @return int
     */
    public function getCount(): int
    {
        $union = $this->getUnionQueryData();

        if (!$union) {
            return 0;
        }

        $db = PearDatabase::getInstance();
        $result = $db->pquery('SELECT COUNT(*) AS count FROM (' . $union['query'] . ') payments_union', $union['parameters']);

        return (int)$db->query_result($result, 0, 'count');
    }

    /**
     * Returns the UNION ALL of the source parts with its parameters, or an empty array without sources.
     *
     * @return array{query: string, parameters: array}|array{}
     */
    protected function getUnionQueryData(): array
    {
        $parts = $parameters = [];
        $index = 0;

        foreach ($this->sourceQueries as $sourceKey => $sourceQuery) {
            $part = $sourceQuery->getUnionPartQueryData((string)$this->get('order_field'));
            $alias = 'payments_part' . $index++;
            $parts[] = '(SELECT ? AS payments_source, ' . $alias . '.df_projection_0 AS payments_id, '
                . $alias . '.df_projection_1 AS payments_sort FROM (' . $part['query'] . ') ' . $alias . ')';
            $parameters[] = $sourceKey;
            array_push($parameters, ...$part['parameters']);
        }

        if (!$parts) {
            return [];
        }

        return [
            'query' => implode(' UNION ALL ', $parts),
            'parameters' => $parameters,
        ];
    }

    /**
     * Creates the query of every accessible source with the filter and the search.
     *
     * @param int   $viewId       Payments filter id
     * @param array $searchParams List search in filter condition format
     * @param array $userSearch   Simple search or an empty array
     * @return void
     */
    protected function retrieveSourceQueries(int $viewId, array $searchParams, array $userSearch): void
    {
        $user = Users_Record_Model::getCurrentUserModel();

        foreach (Payments_Sources_Helper::getAccessibleSources() as $sourceKey => $source) {
            $query = Payments_SourceQuery_Model::getSourceInstance($sourceKey, $source, $user);

            if ($viewId) {
                $query->initForCustomViewById($viewId);
            }

            $glue = $query->getWhereFields() && $searchParams ? QueryGenerator::$AND : '';
            $query->parseAdvFilterList($searchParams, $glue);

            if (!empty($userSearch['search_field'])) {
                $query->addUserSearchConditions($userSearch);
            }

            $this->sourceQueries[$sourceKey] = $query;
        }
    }
}
