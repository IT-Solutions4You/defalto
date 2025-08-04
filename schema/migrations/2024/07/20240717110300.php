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

if (!class_exists('Migration_20240717110300')) {
    class Migration_20240717110300 extends AbstractMigrations
    {
        /**
         * @param string $strFileName
         * @throws Exception
         */
        public function migrate(string $strFileName): void
        {
            $tax = Core_Tax_Model::getInstance();
            $tax->createTables();

            $region = Core_TaxRegion_Model::getInstance();
            $region->createTables();

            $taxRecord = Core_TaxRecord_Model::getInstance();
            $taxRecord->createTables();

            $region->createLinks();
            $tax->createLinks();
            $tax->migrateData();
        }
    }
} else {
    $baseFileName = str_replace('.php', '', basename(__FILE__));
    $this->makeAborting($this->wrongClassName . $baseFileName);
}