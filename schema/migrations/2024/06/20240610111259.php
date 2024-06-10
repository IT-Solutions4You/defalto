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

if (!class_exists('Migration_20240610111259')) {
    class Migration_20240610111259 extends AbstractMigrations
    {
        /**
         * @param string $strFileName
         */
        public function migrate(string $strFileName): void
        {
            $this->db->pquery('ALTER TABLE vtiger_blocks ADD blockuitype INT(11) DEFAULT 1');

            $sql = 'CREATE TABLE IF NOT EXISTS `vtiger_blockuitype` (
                `blockuitype` int(11) NOT NULL,
                `name` varchar(255) NOT NULL,
                PRIMARY KEY (`blockuitype`)
            )';
            $this->db->pquery($sql);
        }
    }
} else {
    $baseFileName = str_replace('.php', '', basename(__FILE__));
    $this->makeAborting($this->wrongClassName . $baseFileName);
}