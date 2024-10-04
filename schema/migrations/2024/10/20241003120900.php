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
        }
    }
} else {
    $baseFileName = str_replace('.php', '', basename(__FILE__));
    $this->makeAborting($this->wrongClassName . $baseFileName);
}