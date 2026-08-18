<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o.
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class GlobalSearch_Module_Model extends Vtiger_Module_Model
{
    /**
     * Returns user-accessible modules which support global search.
     * Individual module models can opt out through isQuickSearchEnabled().
     *
     * @return array<string, Vtiger_Module_Model>
     */
    public static function getSearchModules(): array
    {
        return array_filter(
            Vtiger_Module_Model::getSearchableModules(),
            static fn(Vtiger_Module_Model $moduleModel): bool => (bool)$moduleModel->isQuickSearchEnabled()
        );
    }

    /**
     * Returns the default fields searched before an administrator saves a field configuration.
     * Besides entity name fields, record sequence numbers such as account_no are searchable.
     *
     * @return array<string>
     */
    public static function getDefaultSearchFieldNames(Vtiger_Module_Model $moduleModel): array
    {
        $fieldNames = $moduleModel->getNameFields();

        foreach ($moduleModel->getFields() as $fieldName => $fieldModel) {
            if ($fieldModel instanceof Vtiger_Field_Model && (int)$fieldModel->get('uitype') === 4) {
                $fieldNames[] = $fieldName;
            }
        }

        return array_values(array_unique($fieldNames));
    }

    public function getDatabaseTables(): array
    {
        return [
            'df_global_search_module',
            'df_global_search_field',
        ];
    }

    public function getDefaultUrl(): string
    {
        return 'index.php?module=GlobalSearch&parent=Settings&view=List';
    }

    public function getModuleIcon($height = ''): string
    {
        return sprintf('<i class="fa fa-search" style="font-size: %s"></i>', $height);
    }

    public function getSettingLinks(): array
    {
        return [];
    }
}
