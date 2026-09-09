<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Core_SummaryWidgetList_Model extends Vtiger_Base_Model
{
    public const RELATION_REFERENCE = 'reference';
    public const RELATION_RELATED_LIST = 'related_list';

    protected Vtiger_Record_Model $sourceRecord;
    protected Vtiger_ListView_Model $listView;
    protected Core_QueryGenerator_Model $queryGenerator;
    protected int $filterId = 0;
    protected string $relationType = '';
    protected string $referenceField = '';
    protected int $relationId = 0;
    protected bool $relationConditionAdded = false;

    public static function getInstance(
        Vtiger_Record_Model $sourceRecord,
        string $targetModule,
        int $filterId,
        array $displayFields,
        string $relationType,
        string $referenceField = '',
        int $relationId = 0
    ): self {
        if (!in_array($relationType, [
            self::RELATION_REFERENCE,
            self::RELATION_RELATED_LIST,
        ], true)) {
            throw new Exception(vtranslate('LBL_NOT_ACCESSIBLE'));
        }

        if (!Users_Privileges_Model::isPermitted($targetModule, 'DetailView')) {
            throw new Exception(vtranslate('LBL_NOT_ACCESSIBLE'));
        }

        $targetModuleModel = Vtiger_Module_Model::getInstance($targetModule);

        if (!$targetModuleModel || !$targetModuleModel->isActive() || !$targetModuleModel->isEntityModule()) {
            throw new Exception(vtranslate('LBL_NOT_ACCESSIBLE'));
        }

        $displayFields = array_values(array_unique(array_filter(array_map('strval', $displayFields))));

        if (!$displayFields || count($displayFields) > 3) {
            throw new Exception(vtranslate('LBL_NOT_ACCESSIBLE'));
        }

        foreach ($displayFields as $fieldName) {
            $field = $targetModuleModel->getField($fieldName);

            if (!$field || !$field->isActiveField() || !$field->isViewable() || (int)$field->getDisplayType() === 6) {
                throw new Exception(vtranslate('LBL_NOT_ACCESSIBLE'));
            }
        }

        $customView = CustomView_Record_Model::getInstanceById($filterId);

        if (!$customView || $customView->getModule()->getName() !== $targetModule) {
            throw new Exception(vtranslate('LBL_NOT_ACCESSIBLE'));
        }

        $permission = (new CustomView($targetModule))->isPermittedCustomView(
            $filterId,
            'List',
            $targetModule
        );

        if ($permission !== 'yes') {
            throw new Exception(vtranslate('LBL_NOT_ACCESSIBLE'));
        }

        $instance = new self();
        $instance->sourceRecord = $sourceRecord;
        $instance->filterId = $filterId;
        $instance->relationType = $relationType;
        $instance->referenceField = $referenceField;
        $instance->relationId = $relationId;
        $currentUser = Users_Record_Model::getCurrentUserModel();
        $queryGenerator = new Core_QueryGenerator_Model($targetModule, $currentUser);
        $queryGenerator->initForCustomViewById($filterId);
        $queryGenerator->setFields(array_values(array_unique(array_merge(
            $displayFields,
            ['id', 'starred']
        ))));
        $controller = new ListViewController(PearDatabase::getInstance(), $currentUser, $queryGenerator);
        $instance->queryGenerator = $queryGenerator;
        $instance->listView = Vtiger_ListView_Model::getCleanInstance($targetModule)
            ->set('query_generator', $queryGenerator)
            ->set('listview_controller', $controller);

        if ($relationType === self::RELATION_REFERENCE) {
            $referenceFieldModel = $targetModuleModel->getField($referenceField);

            if (!$referenceFieldModel
                || !$referenceFieldModel->isActiveField()
                || $referenceFieldModel->getFieldDataType() !== Vtiger_Field_Model::REFERENCE_TYPE
                || !in_array($sourceRecord->getModuleName(), $referenceFieldModel->getReferenceList(false, false), true)) {
                throw new Exception(vtranslate('LBL_NOT_ACCESSIBLE'));
            }
        } elseif (!$relationId) {
            throw new Exception(vtranslate('LBL_NOT_ACCESSIBLE'));
        }

        return $instance;
    }

    public function getHeaders(): array
    {
        return $this->listView->getListViewHeaders();
    }

    public function getEntries(Vtiger_Paging_Model $pagingModel): array
    {
        if (!$this->relationConditionAdded) {
            if ($this->relationType === self::RELATION_REFERENCE) {
                $this->addReferenceCondition();
            } else {
                $this->addRelatedListCondition();
            }

            $this->relationConditionAdded = true;
        }

        $this->listView->retrieveOrderBy($this->filterId);
        $this->queryGenerator->setOrderByClauseRequired(true);
        $queryData = $this->queryGenerator->getQueryData();

        return $this->fetchEntries(
            $queryData['query'],
            $queryData['parameters'],
            $pagingModel
        );
    }

    protected function addReferenceCondition(): void
    {
        $field = $this->listView->getModule()->getField($this->referenceField);

        if (!$field
            || $field->getFieldDataType() !== Vtiger_Field_Model::REFERENCE_TYPE) {
            throw new Exception(vtranslate('LBL_NOT_ACCESSIBLE'));
        }

        $this->queryGenerator->addFieldColumnCondition(
            $this->referenceField,
            '=',
            (int)$this->sourceRecord->getId()
        );
    }

    protected function addRelatedListCondition(): void
    {
        $relation = Vtiger_Relation_Model::getInstanceFromId($this->relationId);
        $targetModule = $this->listView->getModule();

        if (!$relation
            || (int)$relation->get('presence') === 1
            || $relation->getParentModuleName() !== $this->sourceRecord->getModuleName()
            || $relation->getRelationModuleName() !== $targetModule->getName()) {
            throw new Exception(vtranslate('LBL_NOT_ACCESSIBLE'));
        }

        $focus = $this->listView->getFocus();
        $tableIndex = (string)($focus->table_index ?? $targetModule->basetableid ?? '');
        $this->queryGenerator->addRecordIdsCondition(
            $this->fetchRelatedRecordIds($relation, $tableIndex)
        );
    }

    protected function fetchRelatedRecordIds(Vtiger_Relation_Model $relation, string $tableIndex): array
    {
        $db = PearDatabase::getInstance();
        $result = $db->pquery($relation->getQuery($this->sourceRecord));
        $recordIds = [];

        while ($row = $db->fetchByAssoc($result)) {
            $recordId = (int)($row[$tableIndex] ?? $row['crmid'] ?? 0);

            if ($recordId > 0) {
                $recordIds[$recordId] = $recordId;
            }
        }

        return array_values($recordIds);
    }

    protected function fetchEntries(
        string $query,
        array $parameters,
        Vtiger_Paging_Model $pagingModel
    ): array
    {
        $db = PearDatabase::getInstance();
        $module = $this->listView->getModule();
        $moduleName = $module->getName();
        $pageLimit = $pagingModel->getPageLimit();
        $query .= ' LIMIT ' . $pagingModel->getStartIndex() . ', ' . ($pageLimit + 1);
        $result = $db->pquery($query, $parameters);
        $records = $this->listView->get('listview_controller')->getListViewRecords(
            $this->listView->getFocus(),
            $moduleName,
            $result
        );

        $pagingModel->calculatePageRange($records);

        if ($db->num_rows($result) > $pageLimit) {
            array_pop($records);
            $pagingModel->set('nextPageExists', true);
        } else {
            $pagingModel->set('nextPageExists', false);
        }

        $recordModels = [];
        $index = 0;

        foreach ($records as $recordId => $record) {
            $rawData = $db->query_result_rowdata($result, $index++);
            $record['id'] = $recordId;
            $recordModels[$recordId] = $module->getRecordFromArray($record, $rawData);
        }

        return $recordModels;
    }
}
