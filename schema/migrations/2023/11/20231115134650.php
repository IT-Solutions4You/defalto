<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

if (!class_exists('Migration_20231115134650')) {
    class Migration_20231115134650 extends AbstractMigrations
    {
        /**
         * @param string $strFileName
         */
        public function migrate(string $strFileName): void
        {
             $this->db->query('ALTER TABLE `vtiger_service` CHANGE `servicename` `servicename` VARCHAR(255)');
             $this->db->query('ALTER TABLE `vtiger_products` CHANGE `productname` `productname` VARCHAR(255)');
        }
    }
} else {
    $baseFileName = str_replace('.php', '', basename(__FILE__));
    $this->makeAborting($this->wrongClassName . $baseFileName);
}