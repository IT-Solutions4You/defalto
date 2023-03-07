<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

if (!class_exists('Migration_20230306215154')) {
    class Migration_20230306215154 extends AbstractMigrations
    {
        /**
         * @param string $strFileName
         */
        public function migrate(string $strFileName): void
        {
            $sql = 'SELECT vtiger_field.fieldname, vtiger_tab.name 
                FROM vtiger_field 
                    INNER JOIN vtiger_tab ON vtiger_tab.tabid = vtiger_field.tabid
                WHERE fieldname LIKE ?';
            $res = $this->db->pquery($sql, ['%fax%']);

            while ($row = $this->db->fetchByAssoc($res)) {
                $moduleInstance = Vtiger_Module::getInstance($row['name']);

                if (!$moduleInstance) {
                    continue;
                }

                $field = Vtiger_Field::getInstance($row['fieldname'], $moduleInstance);

                if (!$field) {
                    continue;
                }

                $field->delete();
            }
        }
    }
} else {
    $baseFileName = str_replace('.php', '', basename(__FILE__));
    $this->makeAborting($this->wrongClassName . $baseFileName);
}