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

if (!class_exists('Migration_20240821140800')) {
    class Migration_20240821140800 extends AbstractMigrations
    {
        /**
         * @param string $strFileName
         * @throws Exception
         */
        public function migrate(string $strFileName): void
        {
            $companyDetails = new Settings_Vtiger_CompanyDetails_Model();
            $companyDetails->createTables();
        }
    }
} else {
    $baseFileName = str_replace('.php', '', basename(__FILE__));
    $this->makeAborting($this->wrongClassName . $baseFileName);
}