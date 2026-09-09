<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o.
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class GlobalSearch_Search_Model extends Vtiger_Base_Model
{
    public const MINIMUM_TERM_LENGTH = 2;
    public const PAGE_LIMIT = 10;
    public const MAXIMUM_PAGE_LIMIT = 50;

    public static function getInstance(): self
    {
        return new self();
    }

    /**
     * @return array<string, Vtiger_Module_Model>
     * @throws Exception
     */
    public function getModuleModels(string $moduleName = ''): array
    {
        $fieldConfiguration = GlobalSearch_FieldConfig_Model::getInstance();
        $moduleModels = array_filter(
            GlobalSearch_Configuration_Model::getInstance()->getActiveModuleModels(),
            fn(Vtiger_Module_Model $moduleModel): bool => (bool)$this->getSearchFieldNames(
                $moduleModel,
                $fieldConfiguration
            )
        );

        if ($moduleName === '') {
            return $moduleModels;
        }

        return isset($moduleModels[$moduleName]) ? [$moduleName => $moduleModels[$moduleName]] : [];
    }

    /**
     * @return array<string, array{record_ids:array<int>,has_more:bool}>
     * @throws Exception
     */
    public function search(
        string $searchValue,
        string $moduleName = '',
        int $page = 1,
        int $limit = self::PAGE_LIMIT
    ): array {
        $searchValue = trim(decode_html($searchValue));

        if (mb_strlen($searchValue) < self::MINIMUM_TERM_LENGTH) {
            return [];
        }

        $page = max(1, $page);
        $limit = max(1, min(self::MAXIMUM_PAGE_LIMIT, $limit));
        $offset = ($page - 1) * $limit;
        $results = [];
        $fieldConfiguration = GlobalSearch_FieldConfig_Model::getInstance();
        $currentUser = Users_Record_Model::getCurrentUserModel();
        $db = PearDatabase::getInstance();

        foreach ($this->getModuleModels($moduleName) as $searchModuleName => $moduleModel) {
            $fieldNames = $this->getSearchFieldNames($moduleModel, $fieldConfiguration);

            if (!$fieldNames) {
                continue;
            }

            $queryGenerator = Core_QueryGenerator_Model::getInstance($searchModuleName, $currentUser);
            $queryGenerator->addAnyFieldContainsCondition($fieldNames, $searchValue);
            $queryGenerator->setOrderByColumns([$queryGenerator->getBaseTableIndexColumn() => 'ASC']);
            $queryGenerator->setOrderByClauseRequired(true);
            $queryData = $queryGenerator->getQueryData();
            $queryData['query'] .= sprintf(' LIMIT %d OFFSET %d', $limit + 1, $offset);
            $queryResult = $db->pquery($queryData['query'], $queryData['parameters']);
            $recordIds = [];
            $baseTableIndex = $queryGenerator->getBaseTableIndex();

            while ($row = $db->fetchByAssoc($queryResult)) {
                $recordIds[] = (int)$row[$baseTableIndex];
            }

            $hasMore = count($recordIds) > $limit;
            $recordIds = array_slice($recordIds, 0, $limit);

            if ($recordIds || $moduleName !== '') {
                $results[$searchModuleName] = [
                    'record_ids' => $recordIds,
                    'has_more' => $hasMore,
                ];
            }
        }

        return $results;
    }

    /**
     * @return array<string>
     */
    public function getSearchFieldNames(
        Vtiger_Module_Model $moduleModel,
        GlobalSearch_FieldConfig_Model|null $fieldConfiguration = null
    ): array {
        $fieldConfiguration ??= GlobalSearch_FieldConfig_Model::getInstance();
        $selectedFieldIds = $fieldConfiguration->getSelectedFieldIds((int)$moduleModel->getId());
        $fieldModels = [];

        if ($selectedFieldIds) {
            $fieldModels = $fieldConfiguration->getSelectedFields($moduleModel, $selectedFieldIds);
        } else {
            $defaultFieldMap = array_flip(GlobalSearch_Module_Model::getDefaultSearchFieldNames($moduleModel));

            foreach ($fieldConfiguration->getSupportedFields($moduleModel) as $fieldModel) {
                if (isset($defaultFieldMap[$fieldModel->getName()])) {
                    $fieldModels[] = $fieldModel;
                }
            }
        }

        return array_values(array_unique(array_map(
            static fn(Vtiger_Field_Model $fieldModel): string => $fieldModel->getName(),
            $fieldModels
        )));
    }
}
