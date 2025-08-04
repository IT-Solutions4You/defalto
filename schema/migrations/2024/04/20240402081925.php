<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

require_once 'vtlib/Vtiger/Cron.php';

if (!class_exists('Migration_20240402081925')) {
    class Migration_20240402081925 extends AbstractMigrations
    {
        /**
         * @param string $strFileName
         */
        public function migrate(string $strFileName): void
        {
            $moduleName = 'Webmails';
            $this->db->pquery('DELETE FROM vtiger_profile2tab WHERE tabid IN (SELECT tabid FROM vtiger_tab WHERE name = ?)', [$moduleName]);
            $this->db->pquery('DELETE FROM vtiger_tab WHERE name = ?', [$moduleName]);

            // Regenerate tabdata.php and parent_tabdata.php
            require_once('vtlib/Vtiger/Deprecated.php');
            Vtiger_Deprecated::createModuleMetaFile();
            Vtiger_Deprecated::createModuleGroupMetaFile();
        }
    }
} else {
    $baseFileName = str_replace('.php', '', basename(__FILE__));
    $this->makeAborting($this->wrongClassName . $baseFileName);
}