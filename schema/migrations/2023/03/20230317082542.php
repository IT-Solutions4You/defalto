<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

if (!class_exists('Migration_20230317082542')) {
    class Migration_20230317082542 extends AbstractMigrations
    {
        /**
         * @param string $strFileName
         */
        public function migrate(string $strFileName): void
        {
            $module = Vtiger_Module::getInstance('Accounts');

            if ($module) {
                $block = Vtiger_Block::getAllForModule($module)[0];

                if ($block) {
                    $regNo = Vtiger_Field::getInstance('reg_no', $module);

                    if (!$regNo) {
                        $regNo = new Vtiger_Field();
                        $regNo->table = $module->basetable;
                        $regNo->name = 'reg_no';
                        $regNo->column = 'reg_no';
                        $regNo->label = 'Company Reg. No.';
                        $regNo->uitype = 1;
                        $regNo->presence = 0;
                        $regNo->columntype = 'VARCHAR(100)';
                        $regNo->typeofdata = 'V~O';
                        $regNo->quickcreate = 0;
                        $regNo->masseditable = 0;
                        $regNo->summaryfield = 0;
                        $regNo->displaytype = 1;
                        $regNo->save($block);
                    }

                    $vatId = Vtiger_Field::getInstance('vat_id', $module);

                    if (!$vatId) {
                        $vatId = new Vtiger_Field();
                        $vatId->table = $module->basetable;
                        $vatId->name = 'vat_id';
                        $vatId->column = 'vat_id';
                        $vatId->label = 'VAT ID';
                        $vatId->uitype = 1;
                        $vatId->presence = 0;
                        $vatId->columntype = 'VARCHAR(100)';
                        $vatId->typeofdata = 'V~O';
                        $vatId->quickcreate = 0;
                        $vatId->masseditable = 0;
                        $vatId->summaryfield = 0;
                        $vatId->displaytype = 1;
                        $vatId->save($block);
                    }
                }
            }
        }
    }
} else {
    $baseFileName = str_replace('.php', '', basename(__FILE__));
    $this->makeAborting($this->wrongClassName . $baseFileName);
}