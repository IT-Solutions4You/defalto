<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

if (!class_exists('Migration_20240312140500')) {
    class Migration_20240312140500 extends AbstractMigrations
    {
        /**
         * @param string $strFileName
         * @throws Exception
         */
        public function migrate(string $strFileName): void
        {
            $moduleNames = ['Faq', 'PriceBooks'];

            foreach($moduleNames as $moduleName) {
                $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
                $fieldModel = Vtiger_Field_Model::getInstance('assigned_user_id', $moduleModel);
                $blockModels = array_values($moduleModel->getBlocks());

                if(!$fieldModel && !empty($blockModels[0])) {
                    /** @var Vtiger_Block_Model $blockModel */
                    $blockModel = $blockModels[0];
                    $fieldModel = new Vtiger_Field();
                    $fieldModel->table = 'vtiger_crmentity';
                    $fieldModel->name = 'assigned_user_id';
                    $fieldModel->label= 'Assigned To';
                    $fieldModel->uitype= 53;
                    $fieldModel->column = 'assigned_user_id';
                    $fieldModel->columntype = 'VARCHAR(255)';
                    $fieldModel->typeofdata = 'V~M';

                    $blockModel->addField($fieldModel);
                }
            }

            $columnTable = 'vtiger_crmentity';
            $columnName = 'isshared';

            if(!columnExists($columnName, $columnTable)) {
                $query = sprintf('ALTER TABLE %s ADD %s INT(1) NULL', $columnTable, $columnName);

                PearDatabase::getInstance()->query($query);
            }
        }
    }
} else {
    $baseFileName = str_replace('.php', '', basename(__FILE__));
    $this->makeAborting($this->wrongClassName . $baseFileName);
}