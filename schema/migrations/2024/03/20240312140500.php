<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

if (!class_exists('Migration_20240312140500')) {
    class Migration_20240312140500 extends AbstractMigrations
    {
        /**
         * @param string $strFileName
         * @throws AppException
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
                    $fieldModel->column = 'smownerid';
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