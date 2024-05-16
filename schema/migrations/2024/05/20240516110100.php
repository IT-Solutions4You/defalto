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

if (!class_exists('Migration_20240516110100')) {
    class Migration_20240516110100 extends AbstractMigrations
    {
        /**
         * @param string $strFileName
         */
        public function migrate(string $strFileName): void
        {
            if (!columnExists('conversion_rate', 'vtiger_pricebook')) {
                $this->db->pquery('ALTER TABLE vtiger_pricebook ADD COLUMN conversion_rate DECIMAL(10,3) NOT NULL DEFAULT 1.000');
            }
        }
    }
} else {
    $baseFileName = str_replace('.php', '', basename(__FILE__));
    $this->makeAborting($this->wrongClassName . $baseFileName);
}