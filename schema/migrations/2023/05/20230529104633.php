<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
            $foreignKeySql = 'ALTER TABLE `its4you_cvorderby` ADD CONSTRAINT `CUSOM_VIEW_ID` FOREIGN KEY (`cvid`) REFERENCES `vtiger_customview`(`cvid`) ON DELETE CASCADE ON UPDATE CASCADE';
            $this->db->query($foreignKeySql);
        }
    }
} else {
    $baseFileName = str_replace('.php', '', basename(__FILE__));
    $this->makeAborting($this->wrongClassName . $baseFileName);
}