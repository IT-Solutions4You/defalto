<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

if (!class_exists('Migration_20230322110623')) {
    class Migration_20230322110623 extends AbstractMigrations
    {
        /**
         * @param string $strFileName
         */
        public function migrate(string $strFileName): void
        {
            $this->db->query('DROP TABLE IF EXISTS vtiger_def_org_field');
        }
    }
} else {
    $baseFileName = str_replace('.php', '', basename(__FILE__));
    $this->makeAborting($this->wrongClassName . $baseFileName);
}