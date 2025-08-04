<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

if (!class_exists('Migration_20230605094602')) {
    class Migration_20230605094602 extends AbstractMigrations
    {
        /**
         * @param string $strFileName
         */
        public function migrate(string $strFileName): void
        {
            $sql = 'SELECT DISTINCT vtiger_field.tabid, vtiger_entityname.tablename 
                    FROM vtiger_field 
                        INNER JOIN vtiger_entityname ON vtiger_entityname.tabid = vtiger_field.tabid
                    WHERE uitype = 71';
            $tabIdResult = $this->db->query($sql);

            while ($tabIdRow = $this->db->fetchByAssoc($tabIdResult)) {
                $tableName = $tabIdRow['tablename'];
                Vtiger_Utils::AddColumn($tableName, 'currency_id', 'INT(19)');
                Vtiger_Utils::AddColumn($tableName, 'conversion_rate', 'DECIMAL(10,3)');
                $this->db->query('UPDATE ' . $tableName . ' SET currency_id = 1, conversion_rate = 1 WHERE currency_id IS NULL');
            }
        }
    }
} else {
    $baseFileName = str_replace('.php', '', basename(__FILE__));
    $this->makeAborting($this->wrongClassName . $baseFileName);
}