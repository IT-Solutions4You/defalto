<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
             $this->db->query('ALTER TABLE `vtiger_modtracker_basic` ADD INDEX(`whodid`, `module`)');

             $this->db->query('ALTER TABLE `vtiger_shorturls` CHANGE `handler_data` `handler_data` TEXT');
        }
    }
} else {
    $baseFileName = str_replace('.php', '', basename(__FILE__));
    $this->makeAborting($this->wrongClassName . $baseFileName);
}