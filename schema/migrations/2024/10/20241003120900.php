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

if (!class_exists('Migration_20241003120900')) {
    class Migration_20241003120900 extends AbstractMigrations
    {
        /**
         * @param string $strFileName
         * @throws Exception
         */
        public function migrate(string $strFileName): void
        {
            $system = new Settings_Vtiger_Systems_Model();
            $system->createTables();

            $files = Core_Files_Model::getInstance('Vtiger');
            $files->deleteModuleFile('blockuitypes/Base.php');
            $files->deleteModuleFile('blockuitypes/Factory.php');
            $files->deleteModuleFile('blockuitypes/Interface.php');

            $files->deleteLayoutFile('blockuitypes/Base.tpl');
            $files->deleteLayoutFile('blockuitypes/BaseEdit.tpl');
        }
    }
} else {
    $baseFileName = str_replace('.php', '', basename(__FILE__));
    $this->makeAborting($this->wrongClassName . $baseFileName);
}