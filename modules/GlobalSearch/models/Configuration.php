<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o.
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class GlobalSearch_Configuration_Model extends Vtiger_Base_Model
{
    public static function getInstance(): self
    {
        return new self();
    }

    /**
     * @return array<array{
     *     module:Vtiger_Module_Model,
     *     fields:array<Vtiger_Field_Model>,
     *     selected_field_ids:array<int>,
     *     is_active:bool
     * }>
     * @throws Exception
     */
    public function getSettingsRows(): array
    {
        $moduleConfiguration = GlobalSearch_ModuleConfig_Model::getInstance()->getConfiguration();
        $fieldConfiguration = GlobalSearch_FieldConfig_Model::getInstance();
        $moduleModels = GlobalSearch_Module_Model::getSearchModules();
        $rows = [];
        ksort($moduleModels);

        foreach ($moduleModels as $moduleModel) {
            $supportedFields = $fieldConfiguration->getSupportedFields($moduleModel);

            if (!$supportedFields) {
                continue;
            }

            $tabId = (int)$moduleModel->getId();
            $selectedFieldIds = $fieldConfiguration->getSelectedFieldIds($tabId)
                ?: $this->getDefaultFieldIds($moduleModel, $supportedFields);
            $rows[] = [
                'module' => $moduleModel,
                'fields' => $supportedFields,
                'selected_field_ids' => $selectedFieldIds,
                'is_active' => !isset($moduleConfiguration[$tabId])
                    || !empty($moduleConfiguration[$tabId]['is_active']),
            ];
        }

        return $rows;
    }

    /**
     * @return array<string, Vtiger_Module_Model>
     * @throws Exception
     */
    public function getActiveModuleModels(): array
    {
        $searchableModules = GlobalSearch_Module_Model::getSearchModules();
        $configuration = GlobalSearch_ModuleConfig_Model::getInstance()->getConfiguration();

        if (!$configuration) {
            return $searchableModules;
        }

        uasort($configuration, static fn(array $left, array $right): int => $left['sequence'] <=> $right['sequence']);
        $activeModules = [];

        foreach ($configuration as $tabId => $moduleConfiguration) {
            $moduleName = getTabModuleName($tabId);

            if ($moduleName) {
                $moduleModel = $searchableModules[$moduleName] ?? null;
                unset($searchableModules[$moduleName]);
            } else {
                $moduleModel = null;
            }

            if (!$moduleConfiguration['is_active']) {
                continue;
            }

            if ($moduleModel) {
                $activeModules[$moduleName] = $moduleModel;
            }
        }

        return array_merge($activeModules, $searchableModules);
    }

    /**
     * @param array<array{tab_id:mixed,is_active:mixed,field_ids:mixed}> $configuration
     * @throws Exception
     */
    public function saveConfiguration(array $configuration): void
    {
        $validatedConfiguration = $this->validateConfiguration($configuration);
        $moduleConfiguration = GlobalSearch_ModuleConfig_Model::getInstance();
        $fieldConfiguration = GlobalSearch_FieldConfig_Model::getInstance();

        foreach ($validatedConfiguration as $row) {
            $moduleConfiguration->saveModuleConfig($row['tab_id'], $row['is_active'], $row['sequence']);
            $fieldConfiguration->saveModuleFields($row['tab_id'], $row['field_ids']);
        }
    }

    /**
     * @param array<array{tab_id:mixed,is_active:mixed,field_ids:mixed}> $configuration
     * @return array<array{tab_id:int,is_active:bool,sequence:int,field_ids:array<int>}>
     * @throws Exception
     */
    protected function validateConfiguration(array $configuration): array
    {
        $searchableById = [];

        foreach (GlobalSearch_Module_Model::getSearchModules() as $moduleModel) {
            $searchableById[(int)$moduleModel->getId()] = $moduleModel;
        }

        $fieldConfiguration = GlobalSearch_FieldConfig_Model::getInstance();
        $validatedConfiguration = [];

        foreach ($configuration as $sequence => $row) {
            $tabId = (int)($row['tab_id'] ?? 0);
            $moduleModel = $searchableById[$tabId] ?? null;

            if (!$moduleModel) {
                throw new InvalidArgumentException(vtranslate('LBL_GLOBAL_SEARCH_INVALID_MODULE', 'Settings:GlobalSearch'));
            }

            $isActive = (int)($row['is_active'] ?? 0) === 1;
            $fieldIds = is_array($row['field_ids'] ?? null)
                ? $fieldConfiguration->getValidFieldIds($moduleModel, $row['field_ids'])
                : [];

            if ($isActive && !$fieldIds) {
                throw new InvalidArgumentException(vtranslate('LBL_GLOBAL_SEARCH_FIELDS_REQUIRED', 'Settings:GlobalSearch'));
            }

            $validatedConfiguration[] = [
                'tab_id' => $tabId,
                'is_active' => $isActive,
                'sequence' => $sequence + 1,
                'field_ids' => $fieldIds,
            ];
        }

        return $validatedConfiguration;
    }

    /**
     * @param array<Vtiger_Field_Model> $supportedFields
     * @return array<int>
     */
    protected function getDefaultFieldIds(Vtiger_Module_Model $moduleModel, array $supportedFields): array
    {
        $defaultFieldMap = array_flip(GlobalSearch_Module_Model::getDefaultSearchFieldNames($moduleModel));
        $fieldIds = [];

        foreach ($supportedFields as $fieldModel) {
            if (isset($defaultFieldMap[$fieldModel->getName()])) {
                $fieldIds[] = (int)$fieldModel->getId();
            }
        }

        return $fieldIds;
    }
}
