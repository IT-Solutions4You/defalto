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

if (!class_exists('Migration_20250912120600')) {
    class Migration_20250912120600 extends AbstractMigrations
    {
        /**
         * @param string $fileName
         */
        public function migrate(string $fileName): void
        {
            $tax = Core_Tax_Model::getInstance();
            $region = Core_TaxRegion_Model::getInstance();

            $tax->clearLinks();
            $tax->createLinks();
            $region->createLinks();
        }
    }
} else {
    $baseFileName = str_replace('.php', '', basename(__FILE__));
    $this->makeAborting($this->wrongClassName . $baseFileName);
}