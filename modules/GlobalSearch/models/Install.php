<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o.
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class GlobalSearch_Install_Model extends Core_Install_Model
{
    public array $registerSettingsLinks = [
        [
            'LBL_GLOBAL_SEARCH_SETTINGS',
            'index.php?module=GlobalSearch&parent=Settings&view=List',
            'LBL_CONFIGURATION',
            'LBL_GLOBAL_SEARCH_SETTINGS_DESCRIPTION',
        ],
    ];

    public function addCustomLinks(): void
    {
        $this->updateSettingsLinks();
    }

    public function deleteCustomLinks(): void
    {
        $this->updateSettingsLinks(false);
    }

    public function getBlocks(): array
    {
        return [];
    }

    public function getTables(): array
    {
        return [
            'df_global_search_field',
            'df_global_search_module',
        ];
    }

    /**
     * @throws Exception
     */
    public function installTables(): void
    {
        $this->getTable('df_global_search_module', null)
            ->createTable('tab_id', 'int(19) NOT NULL')
            ->createColumn('is_active', 'int(1) NOT NULL DEFAULT 0')
            ->createColumn('sequence', 'int(19) NOT NULL DEFAULT 0')
            ->createKey('PRIMARY KEY IF NOT EXISTS (`tab_id`)')
            ->createKey('CONSTRAINT `fk_df_global_search_module_tab` FOREIGN KEY IF NOT EXISTS (`tab_id`) REFERENCES `vtiger_tab` (`tabid`) ON DELETE CASCADE');

        $this->getTable('df_global_search_field', 'global_search_field_id')
            ->createTable()
            ->createColumn('tab_id', 'int(19) NOT NULL')
            ->createColumn('field_id', 'int(19) NOT NULL')
            ->createColumn('sequence', 'int(19) NOT NULL DEFAULT 0')
            ->createKey('UNIQUE KEY IF NOT EXISTS `idx_df_global_search_field` (`tab_id`,`field_id`)')
            ->createKey('CONSTRAINT `fk_df_global_search_field_module` FOREIGN KEY IF NOT EXISTS (`tab_id`) REFERENCES `df_global_search_module` (`tab_id`) ON DELETE CASCADE')
            ->createKey('CONSTRAINT `fk_df_global_search_field_field` FOREIGN KEY IF NOT EXISTS (`field_id`) REFERENCES `vtiger_field` (`fieldid`) ON DELETE CASCADE');
    }
}
