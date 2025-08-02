<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

if (!class_exists('Migration_20231115150055')) {
    class Migration_20231115150055 extends AbstractMigrations
    {
        /**
         * @param string $strFileName
         */
        public function migrate(string $strFileName): void
        {
            $this->db->query('ALTER TABLE `vtiger_modtracker_basic` ADD INDEX(`whodid`)');
            $this->db->query('ALTER TABLE `vtiger_modtracker_basic` ADD INDEX(`module`)');
            $this->db->query('ALTER TABLE `vtiger_modtracker_basic` ADD INDEX(`status`)');
            $this->db->query('ALTER TABLE `vtiger_modtracker_basic` ADD INDEX(`whodid`, `module`)');
            $this->db->query('ALTER TABLE `vtiger_modtracker_basic` ADD INDEX(`crmid`, `module`)');
            $this->db->query('ALTER TABLE `vtiger_modtracker_basic` ADD INDEX(`crmid`, `status`)');

            $this->db->query('ALTER TABLE `vtiger_shorturls` CHANGE `handler_data` `handler_data` TEXT');
        }
    }
} else {
    $baseFileName = str_replace('.php', '', basename(__FILE__));
    $this->makeAborting($this->wrongClassName . $baseFileName);
}