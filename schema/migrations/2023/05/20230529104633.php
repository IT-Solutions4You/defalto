<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

if (!class_exists('Migration_20230529104633')) {
    class Migration_20230529104633 extends AbstractMigrations
    {
        /**
         * @param string $strFileName
         */
        public function migrate(string $strFileName): void
        {
            $sql = 'CREATE TABLE IF NOT EXISTS `its4you_cvorderby` (
                      `id` int(11) PRIMARY KEY NOT NULL AUTO_INCREMENT,
                      `cvid` int(11) NOT NULL,
                      `orderby` varchar(255) NOT NULL,
                      `sortorder` varchar(4) NOT NULL
                    )';
            $this->db->query($sql);
            $foreignKeySql = 'ALTER TABLE `its4you_cvorderby` ADD CONSTRAINT `CUSOM_VIEW_ID` FOREIGN KEY IF NOT EXISTS (`cvid`) REFERENCES `vtiger_customview`(`cvid`) ON DELETE CASCADE ON UPDATE CASCADE';
            $this->db->query($foreignKeySql);
        }
    }
} else {
    $baseFileName = str_replace('.php', '', basename(__FILE__));
    $this->makeAborting($this->wrongClassName . $baseFileName);
}