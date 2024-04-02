<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once 'vtlib/Vtiger/Cron.php';

if (!class_exists('Migration_20240402114808')) {
    class Migration_20240402114808 extends AbstractMigrations
    {
        /**
         * @param string $strFileName
         */
        public function migrate(string $strFileName): void
        {
            $moduleName = 'ExtensionStore';

            $tabId = getTabid($moduleName);
            Vtiger_Link::deleteLink($tabId, 'HEADERSCRIPT', 'ExtensionStoreCommonHeaderScript');

            $this->db->pquery('DELETE FROM vtiger_profile2tab WHERE tabid IN (SELECT tabid FROM vtiger_tab WHERE name = ?)', [$moduleName]);
            $this->db->pquery('DELETE FROM vtiger_tab WHERE name = ?', [$moduleName]);
            $this->db->pquery('DELETE FROM vtiger_settings_field WHERE name=?', array('LBL_EXTENSION_STORE'));
            $this->db->pquery('DROP TABLE IF EXISTS vtiger_extnstore_users');

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