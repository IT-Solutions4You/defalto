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

if (!class_exists('Migration_20250917141800')) {
    class Migration_20250917141800 extends AbstractMigrations
    {
        /**
         * @param string $fileName
         * @throws Exception
         */
        public function migrate(string $fileName): void
        {
            $tax = Core_Tax_Model::getInstance();
            $tax->clearLinks();
            $tax->createLinks();

            $region = Core_TaxRegion_Model::getInstance();
            $region->createLinks();

            $menu = new Settings_Vtiger_Menu_Model();
            $menu->createLinks();

            $menuItem = new Settings_Vtiger_MenuItem_Model();
            $menuItem->createLinks();
        }
    }
} else {
    $baseFileName = str_replace('.php', '', basename(__FILE__));
    $this->makeAborting($this->wrongClassName . $baseFileName);
}