<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

if (!class_exists('Migration_20230605094602')) {
    class Migration_20230605094602 extends AbstractMigrations
    {
        /**
         * @param string $strFileName
         */
        public function migrate(string $strFileName): void
        {
            $sql = 'SELECT DISTINCT vtiger_field.tabid, vtiger_field.block, vtiger_field.sequence, vtiger_entityname.tablename, vtiger_entityname.modulename 
                    FROM vtiger_field 
                        INNER JOIN vtiger_entityname ON vtiger_entityname.tabid = vtiger_field.tabid
                    WHERE uitype = 71';
            $tabIdResult = $this->db->query($sql);

            while ($tabIdRow = $this->db->fetchByAssoc($tabIdResult)) {
                $module = Vtiger_Module::getInstance($tabIdRow['modulename']);

                if ($module) {
                    $field = Vtiger_Field::getInstance('currency_id', $module);

                    if (!$field) {
                        $block = Vtiger_Block::getInstance($tabIdRow['block'], $module);

                        if ($block) {
                            $currencyId = new Vtiger_Field();
                            $currencyId->table = $tabIdRow['tablename'];
                            $currencyId->name = 'currency_id';
                            $currencyId->column = 'currency_id';
                            $currencyId->label = 'Currency';
                            $currencyId->uitype = 117;
                            $currencyId->presence = 0;
                            $currencyId->sequence = (int)$tabIdRow['sequence'] + 1;
                            $currencyId->columntype = 'INT(19)';
                            $currencyId->typeofdata = 'I~O';
                            $currencyId->quickcreate = 3;
                            $currencyId->masseditable = 1;
                            $currencyId->summaryfield = 0;
                            $currencyId->displaytype = 1;
                            $currencyId->save($block);
                        }
                    }
                }

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