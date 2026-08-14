<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Settings_Vtiger_SummaryWidgets_Model extends Vtiger_Base_Model
{
    protected const DISABLED_LINK_URL = 'block://SummaryWidgets';

    protected PearDatabase $db;
    protected Vtiger_Module_Model $sourceModule;
    protected ?array $registeredDefinitions = null;

    public static function getInstance(string $sourceModuleName): self
    {
        $sourceModule = Vtiger_Module_Model::getInstance($sourceModuleName);

        if (!$sourceModule
            || !$sourceModule->isActive()
            || !$sourceModule->isEntityModule()
            || !$sourceModule->isSummaryViewSupported()) {
            throw new Exception(vtranslate('LBL_PERMISSION_DENIED'));
        }

        $instance = new self();
        $instance->db = PearDatabase::getInstance();
        $instance->sourceModule = $sourceModule;

        return $instance;
    }

    public static function getSupportedModules(): array
    {
        $modules = [];

        foreach (Vtiger_Module_Model::getEntityModules() as $module) {
            if ($module->isActive() && $module->isSummaryViewSupported()) {
                $modules[$module->getName()] = $module;
            }
        }

        uasort($modules, static function (Vtiger_Module_Model $first, Vtiger_Module_Model $second): int {
            return strnatcasecmp($first->getLabel(), $second->getLabel());
        });

        return $modules;
    }

    public static function getSupportedTargetModules(): array
    {
        $modules = [];

        foreach (Vtiger_Module_Model::getEntityModules() as $module) {
            if ($module->isActive() && $module->isPermitted('DetailView')) {
                $modules[$module->getName()] = $module;
            }
        }

        uasort($modules, static function (Vtiger_Module_Model $first, Vtiger_Module_Model $second): int {
            return strnatcasecmp(
                vtranslate($first->getName(), $first->getName()),
                vtranslate($second->getName(), $second->getName())
            );
        });

        return $modules;
    }

    public static function getListWidgetEditorOptions(string $sourceModule, string $targetModule): array
    {
        $sourceModuleModel = Vtiger_Module_Model::getInstance($sourceModule);
        $targetModules = self::getSupportedTargetModules();

        if (!$sourceModuleModel || !isset($targetModules[$targetModule])) {
            throw new Exception(vtranslate('LBL_NOT_ACCESSIBLE'));
        }

        $targetModuleModel = $targetModules[$targetModule];
        $fields = [];
        $fieldGroups = [];
        $referenceFields = [];
        $referenceFieldGroups = [];

        foreach ($targetModuleModel->getBlocks() as $blockLabel => $block) {
            $translatedBlockLabel = vtranslate($blockLabel, $targetModule);

            foreach ($block->getFields() as $field) {
                if (!$field->isActiveField() || !$field->isViewable() || (int)$field->getDisplayType() === 6) {
                    continue;
                }

                $fieldName = $field->getName();
                $fieldLabel = vtranslate($field->get('label'), $targetModule);
                $fields[$fieldName] = $fieldLabel;
                $fieldGroups[$translatedBlockLabel][$fieldName] = $fieldLabel;

                if ($field->getFieldDataType() === Vtiger_Field_Model::REFERENCE_TYPE
                    && in_array($sourceModule, $field->getReferenceList(false, false), true)) {
                    $referenceFields[$fieldName] = $fieldLabel;
                    $referenceFieldGroups[$translatedBlockLabel][$fieldName] = $fieldLabel;
                }
            }
        }

        $filters = [];
        $seenFilters = [];

        foreach (CustomView_Record_Model::getAllByGroup($targetModule) as $group => $customViews) {
            foreach ($customViews as $customView) {
                if (isset($seenFilters[$customView->getId()])) {
                    continue;
                }

                $seenFilters[$customView->getId()] = true;
                $filters[$group][$customView->getId()] = $customView->getDisplayName();
            }
        }

        $relations = [];

        foreach (Vtiger_Relation_Model::getAllRelations($sourceModuleModel) as $relation) {
            if ($relation->getRelationModuleName() === $targetModule) {
                $relations[$relation->getId()] = vtranslate($relation->get('label'), $sourceModule);
            }
        }

        natcasesort($fields);
        natcasesort($referenceFields);

        foreach ($fieldGroups as &$blockFields) {
            natcasesort($blockFields);
        }
        unset($blockFields);

        foreach ($referenceFieldGroups as &$blockReferenceFields) {
            natcasesort($blockReferenceFields);
        }
        unset($blockReferenceFields);

        natcasesort($relations);

        return [
            'fields' => $fields,
            'fieldGroups' => $fieldGroups,
            'filters' => $filters,
            'referenceFields' => $referenceFields,
            'referenceFieldGroups' => $referenceFieldGroups,
            'relations' => $relations,
        ];
    }

    public function getConfiguration(): array
    {
        $definitions = $this->getDefinitions();
        $result = $this->db->pquery(
            'SELECT * FROM vtiger_links WHERE tabid=? AND linktype=? ORDER BY linkid',
            [$this->sourceModule->getId(), 'DETAILVIEWWIDGET']
        );
        $activeByLabel = [];

        while ($row = $this->db->fetchByAssoc($result)) {
            if (str_starts_with((string)$row['linkurl'], 'block://')) {
                continue;
            }

            $label = $row['linklabel'];

            if (!isset($activeByLabel[$label])) {
                $widget = Vtiger_Link_Model::getInstanceFromValues($row);
                $widget->set('editable', $this->isEditableListWidget(
                    (string)$row['linklabel'],
                    (string)$row['linkurl']
                ));
                $activeByLabel[$label] = $widget;
            }
        }

        $activeWidgets = array_values($activeByLabel);
        usort($activeWidgets, static function (Vtiger_Link_Model $first, Vtiger_Link_Model $second): int {
            $sequenceComparison = (int)$first->get('sequence') <=> (int)$second->get('sequence');

            return $sequenceComparison ?: (int)$first->getId() <=> (int)$second->getId();
        });

        $configuration = [
            'left' => [],
            'right' => [],
            'available' => [],
        ];

        foreach ($activeWidgets as $widget) {
            $column = (int)$widget->get('sequence') % 2 === 0 ? 'left' : 'right';
            $configuration[$column][] = $widget;
        }

        foreach ($definitions as $label => $definition) {
            if (!isset($activeByLabel[$label])) {
                $configuration['available'][$label] = $definition;
            }
        }

        return $configuration;
    }

    public function addWidget(string $label): Vtiger_Link_Model
    {
        $definitions = $this->getDefinitions();

        if (!isset($definitions[$label])) {
            throw new Exception(vtranslate('LBL_NOT_ACCESSIBLE'));
        }

        [$moduleName, $definitionLabel, $url, $icon, $sequence, $handlerInfo, $legacyLabels] = array_pad($definitions[$label], 7, null);
        $labels = array_values(array_unique(array_merge([$definitionLabel], (array)$legacyLabels)));
        $result = $this->db->pquery(
            'SELECT linkid, linklabel, linkurl FROM vtiger_links WHERE tabid=? AND linktype=? AND linklabel IN ('
            . generateQuestionMarks($labels) . ') ORDER BY linkid',
            [$this->sourceModule->getId(), 'DETAILVIEWWIDGET', $labels]
        );
        $existingLinks = [];

        while ($row = $this->db->fetchByAssoc($result)) {
            $existingLinks[] = $row;
        }

        usort($existingLinks, static function (array $first, array $second) use ($definitionLabel): int {
            $firstIsActive = !str_starts_with((string)$first['linkurl'], 'block://');
            $secondIsActive = !str_starts_with((string)$second['linkurl'], 'block://');

            if ($firstIsActive !== $secondIsActive) {
                return $firstIsActive ? -1 : 1;
            }

            $firstIsCurrent = $first['linklabel'] === $definitionLabel;
            $secondIsCurrent = $second['linklabel'] === $definitionLabel;

            if ($firstIsCurrent !== $secondIsCurrent) {
                return $firstIsCurrent ? -1 : 1;
            }

            return (int)$first['linkid'] <=> (int)$second['linkid'];
        });

        $linkId = (int)($existingLinks[0]['linkid'] ?? 0);

        if ($linkId) {
            Vtiger_Link::updateLink($this->sourceModule->getId(), $linkId, [
                'linklabel' => $definitionLabel,
                'linkurl' => $url,
                'linkicon' => $icon,
                'handler_path' => $handlerInfo['path'] ?? null,
                'handler_class' => $handlerInfo['class'] ?? null,
                'handler' => $handlerInfo['method'] ?? null,
            ]);
        } else {
            if ($sequence === null) {
                $sequence = $this->getNextWidgetSequence();
            }

            Vtiger_Link::addLink(
                $this->sourceModule->getId(),
                'DETAILVIEWWIDGET',
                $definitionLabel,
                $url,
                $icon,
                (int)$sequence,
                $handlerInfo
            );
        }

        $this->clearCache();

        return $this->getStoredLink($definitionLabel, $url);
    }

    public function createListWidget(Vtiger_Request $request): Vtiger_Link_Model
    {
        [$label, $url] = $this->getListWidgetValues($request);

        Vtiger_Link::addLink(
            $this->sourceModule->getId(),
            'DETAILVIEWWIDGET',
            $label,
            $url,
            'fa-solid fa-table-list',
            $this->getNextWidgetSequence()
        );

        $this->clearCache();

        return $this->getStoredLink($label, $url);
    }

    public function updateListWidget(int $linkId, Vtiger_Request $request): Vtiger_Link_Model
    {
        $this->getListWidgetConfiguration($linkId);
        [$label, $url] = $this->getListWidgetValues($request, $linkId);

        Vtiger_Link::updateLink($this->sourceModule->getId(), $linkId, [
            'linklabel' => $label,
            'linkurl' => $url,
            'linkicon' => 'fa-solid fa-table-list',
        ]);
        $this->clearCache();

        return $this->getStoredLink($label, $url);
    }

    public function getListWidgetConfiguration(int $linkId): array
    {
        $result = $this->db->pquery(
            'SELECT linkid, linklabel, linkurl FROM vtiger_links WHERE tabid=? AND linktype=? AND linkid=?',
            [$this->sourceModule->getId(), 'DETAILVIEWWIDGET', $linkId]
        );
        $row = $this->db->fetchByAssoc($result);

        if (!$row || !$this->isEditableListWidget((string)$row['linklabel'], (string)$row['linkurl'])) {
            throw new Exception(vtranslate('LBL_NOT_ACCESSIBLE'));
        }

        $params = $this->getUrlParameters((string)$row['linkurl']);

        return [
            'linkId' => (int)$row['linkid'],
            'title' => (string)$row['linklabel'],
            'targetModule' => (string)$params['relatedModule'],
            'filterId' => (int)$params['filterId'],
            'fields' => array_values(array_filter(array_map('trim', explode(',', (string)$params['fields'])))),
            'relationType' => (string)$params['relationType'],
            'referenceField' => (string)($params['referenceField'] ?? ''),
            'relationId' => (int)($params['relationId'] ?? 0),
        ];
    }

    protected function getListWidgetValues(Vtiger_Request $request, int $excludedLinkId = 0): array
    {
        $sourceModule = $this->sourceModule->getName();
        $targetModule = trim((string)$request->get('targetModule'));
        $label = trim((string)$request->get('widgetTitle'));
        $filterId = (int)$request->get('filterId');
        $relationType = trim((string)$request->get('relationType'));
        $referenceField = trim((string)$request->get('referenceField'));
        $relationId = (int)$request->get('relationId');
        $fields = array_values(array_unique(array_filter(array_map('strval', (array)$request->get('fields')))));
        $options = self::getListWidgetEditorOptions($sourceModule, $targetModule);

        if ($label === ''
            || mb_strlen($label) > 50
            || !preg_match('/^[\p{L}\p{N}\s._-]+$/u', $label)
            || count($fields) < 1
            || count($fields) > 3) {
            throw new Exception(vtranslate('LBL_INVALID_SUMMARY_LIST_WIDGET', 'Settings:Vtiger'));
        }

        foreach ($fields as $fieldName) {
            if (!isset($options['fields'][$fieldName])) {
                throw new Exception(vtranslate('LBL_NOT_ACCESSIBLE'));
            }
        }

        $filterIds = [];
        foreach ($options['filters'] as $filters) {
            $filterIds += array_fill_keys(array_keys($filters), true);
        }

        if (!isset($filterIds[$filterId])) {
            throw new Exception(vtranslate('LBL_NOT_ACCESSIBLE'));
        }

        $urlParams = [
            'relatedModule' => $targetModule,
            'filterId' => $filterId,
            'fields' => implode(',', $fields),
            'relationType' => $relationType,
        ];

        if ($relationType === Core_SummaryWidgetList_Model::RELATION_REFERENCE) {
            if (!isset($options['referenceFields'][$referenceField])) {
                throw new Exception(vtranslate('LBL_NOT_ACCESSIBLE'));
            }

            $urlParams['referenceField'] = $referenceField;
        } elseif ($relationType === Core_SummaryWidgetList_Model::RELATION_RELATED_LIST) {
            if (!isset($options['relations'][$relationId])) {
                throw new Exception(vtranslate('LBL_NOT_ACCESSIBLE'));
            }

            $urlParams['relationId'] = $relationId;
        } else {
            throw new Exception(vtranslate('LBL_NOT_ACCESSIBLE'));
        }

        if ($this->widgetLabelExists($label, $excludedLinkId)) {
            throw new Exception(vtranslate('LBL_SUMMARY_WIDGET_NAME_EXISTS', 'Settings:Vtiger'));
        }

        $url = 'module=$MODULE$&view=Widget&record=$RECORD$&mode=showList&'
            . http_build_query($urlParams, '', '&', PHP_QUERY_RFC3986);

        if (strlen($url) > 255) {
            throw new Exception(vtranslate('LBL_SUMMARY_WIDGET_URL_TOO_LONG', 'Settings:Vtiger'));
        }

        return [$label, $url];
    }

    public function removeWidget(int $linkId): bool
    {
        $result = $this->db->pquery(
            'SELECT linklabel, linkurl FROM vtiger_links WHERE tabid=? AND linktype=? AND linkid=?',
            [$this->sourceModule->getId(), 'DETAILVIEWWIDGET', $linkId]
        );
        $row = $this->db->fetchByAssoc($result);

        if (!$row) {
            throw new Exception(vtranslate('LBL_NOT_ACCESSIBLE'));
        }

        $isRegisteredWidget = $this->hasRegisteredDefinition((string)$row['linklabel']);

        if ($isRegisteredWidget) {
            Vtiger_Link::updateLink($this->sourceModule->getId(), $linkId, [
                'linkurl' => self::DISABLED_LINK_URL,
            ]);
        } else {
            Vtiger_Link::deleteLink(
                $this->sourceModule->getId(),
                'DETAILVIEWWIDGET',
                (string)$row['linklabel'],
                (string)$row['linkurl']
            );
        }

        $this->clearCache();

        return $isRegisteredWidget;
    }

    public function updateOrder(array $leftLinkIds, array $rightLinkIds): void
    {
        $leftLinkIds = array_values(array_map('intval', $leftLinkIds));
        $rightLinkIds = array_values(array_map('intval', $rightLinkIds));
        $linkIds = array_merge($leftLinkIds, $rightLinkIds);

        if (count($linkIds) !== count(array_unique($linkIds))) {
            throw new Exception(vtranslate('LBL_NOT_ACCESSIBLE'));
        }

        $result = $this->db->pquery(
            'SELECT linkid, linklabel, linkurl FROM vtiger_links WHERE tabid=? AND linktype=? ORDER BY linkid',
            [$this->sourceModule->getId(), 'DETAILVIEWWIDGET']
        );
        $storedByLabel = [];

        while ($row = $this->db->fetchByAssoc($result)) {
            if (!str_starts_with((string)$row['linkurl'], 'block://')
                && !isset($storedByLabel[$row['linklabel']])) {
                $storedByLabel[$row['linklabel']] = (int)$row['linkid'];
            }
        }

        $storedLinkIds = array_values($storedByLabel);
        sort($storedLinkIds);
        $validatedLinkIds = $linkIds;
        sort($validatedLinkIds);

        if ($storedLinkIds !== $validatedLinkIds) {
            throw new Exception(vtranslate('LBL_NOT_ACCESSIBLE'));
        }

        $sequences = [];

        foreach ($leftLinkIds as $index => $linkId) {
            $sequences[$linkId] = $index * 2;
        }

        foreach ($rightLinkIds as $index => $linkId) {
            $sequences[$linkId] = $index * 2 + 1;
        }

        if ($sequences) {
            $caseSql = '';
            $params = [];

            foreach ($sequences as $linkId => $sequence) {
                $caseSql .= ' WHEN ? THEN ?';
                $params[] = $linkId;
                $params[] = $sequence;
            }

            $params[] = $this->sourceModule->getId();
            $params[] = 'DETAILVIEWWIDGET';
            $params[] = array_keys($sequences);
            $this->db->pquery(
                'UPDATE vtiger_links SET sequence=CASE linkid' . $caseSql . ' ELSE sequence END'
                . ' WHERE tabid=? AND linktype=? AND linkid IN ('
                . generateQuestionMarks(array_keys($sequences)) . ')',
                $params
            );
        }

        $this->clearCache();
    }

    protected function clearCache(): void
    {
        Vtiger_Cache::delete('links-' . $this->sourceModule->getId(), ['DETAILVIEWWIDGET']);
    }

    protected function getDefinitions(): array
    {
        $definitions = $this->getRegisteredDefinitions();
        $result = $this->db->pquery(
            'SELECT * FROM vtiger_links WHERE tabid=? AND linktype=? ORDER BY linkid',
            [$this->sourceModule->getId(), 'DETAILVIEWWIDGET']
        );

        while ($row = $this->db->fetchByAssoc($result)) {
            if (!$this->isListWidgetUrl((string)$row['linkurl']) || isset($definitions[$row['linklabel']])) {
                continue;
            }

            $handlerInfo = null;

            if ($row['handler_path'] || $row['handler_class'] || $row['handler']) {
                $handlerInfo = [
                    'path' => $row['handler_path'],
                    'class' => $row['handler_class'],
                    'method' => $row['handler'],
                ];
            }

            $definitions[$row['linklabel']] = [
                $this->sourceModule->getName(),
                $row['linklabel'],
                $row['linkurl'],
                $row['linkicon'],
                (int)$row['sequence'],
                $handlerInfo,
                [],
            ];
        }

        return $definitions;
    }

    protected function isListWidgetUrl(string $url): bool
    {
        $params = $this->getUrlParameters($url);

        return ($params['view'] ?? '') === 'Widget'
            && ($params['mode'] ?? '') === 'showList'
            && !empty($params['relatedModule'])
            && !empty($params['filterId'])
            && !empty($params['fields'])
            && !empty($params['relationType']);
    }

    protected function isEditableListWidget(string $label, string $url): bool
    {
        return $this->isListWidgetUrl($url) && !$this->hasRegisteredDefinition($label);
    }

    protected function getUrlParameters(string $url): array
    {
        $query = parse_url(html_entity_decode($url), PHP_URL_QUERY);

        if ($query === null) {
            $query = ltrim(html_entity_decode($url), '?');
        }

        parse_str($query, $params);

        return $params;
    }

    protected function widgetLabelExists(string $label, int $excludedLinkId = 0): bool
    {
        foreach ($this->getRegisteredDefinitions() as $definition) {
            [, $definitionLabel, , , , , $legacyLabels] = array_pad($definition, 7, null);

            foreach (array_merge([(string)$definitionLabel], (array)$legacyLabels) as $registeredLabel) {
                if (strcasecmp($registeredLabel, $label) === 0) {
                    return true;
                }
            }
        }

        $result = $this->db->pquery(
            'SELECT linkid, linklabel FROM vtiger_links'
            . ' WHERE tabid=? AND linktype=? AND linkurl NOT LIKE ? ORDER BY linkid',
            [$this->sourceModule->getId(), 'DETAILVIEWWIDGET', 'block://%']
        );

        while ($row = $this->db->fetchByAssoc($result)) {
            if ((int)$row['linkid'] !== $excludedLinkId && strcasecmp((string)$row['linklabel'], $label) === 0) {
                return true;
            }
        }

        return false;
    }

    protected function hasRegisteredDefinition(string $storedLabel): bool
    {
        foreach ($this->getRegisteredDefinitions() as $definition) {
            [, $label, , , , , $legacyLabels] = array_pad($definition, 7, null);

            if (in_array($storedLabel, array_merge([(string)$label], (array)$legacyLabels), true)) {
                return true;
            }
        }

        return false;
    }

    protected function getRegisteredDefinitions(): array
    {
        if ($this->registeredDefinitions === null) {
            $this->registeredDefinitions = Core_Install_Model::getSummaryWidgetDefinitions(
                $this->sourceModule->getName()
            );
        }

        return $this->registeredDefinitions;
    }

    protected function getNextWidgetSequence(): int
    {
        $result = $this->db->pquery(
            'SELECT MAX(sequence) AS max_sequence FROM vtiger_links'
            . ' WHERE tabid=? AND linktype=? AND linkurl NOT LIKE ?',
            [$this->sourceModule->getId(), 'DETAILVIEWWIDGET', 'block://%']
        );

        return (int)$this->db->query_result($result, 0, 'max_sequence') + 1;
    }

    protected function getStoredLink(string $label, string $url): Vtiger_Link_Model
    {
        $result = $this->db->pquery(
            'SELECT * FROM vtiger_links WHERE tabid=? AND linktype=? AND linklabel=? AND linkurl=? ORDER BY linkid DESC',
            [$this->sourceModule->getId(), 'DETAILVIEWWIDGET', $label, $url]
        );
        $row = $this->db->fetchByAssoc($result);

        if (!$row) {
            throw new Exception(vtranslate('LBL_NOT_ACCESSIBLE'));
        }

        $widget = Vtiger_Link_Model::getInstanceFromValues($row);
        $widget->set('editable', $this->isEditableListWidget(
            (string)$row['linklabel'],
            (string)$row['linkurl']
        ));

        return $widget;
    }
}
