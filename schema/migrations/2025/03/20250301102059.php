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

if (!class_exists('Migration_20250301102059')) {
    class Migration_20250301102059 extends AbstractMigrations
    {
        /**
         * @param string $strFileName
         *
         * @throws AppException
         * @throws Exception
         */
        public function migrate(string $strFileName): void
        {
            error_reporting(63);
            ini_set('display_errors', 1);
            $inventoryModules = ['Quotes', 'PurchaseOrder', 'SalesOrder', 'Invoice'];
            $deactivateFields = ['hdnS_H_Percent', 'hdnS_H_Amount', 'hdnDiscountPercent',];
            $changeFields = [
                'pre_tax_total'   => ['price_after_overall_discount', 'Price After Overall Discount', 'Pre Tax Total'],
                'hdnDiscountAmount' => ['discount_amount', 'Discount Amount', 'Discount Amount'],
                'hdnGrandTotal'           => ['price_total', 'Total', 'Total'],
                'txtAdjustment'      => ['adjustment', 'Adjustment', 'Adjustment'],
                'hdnSubTotal'        => ['subtotal', 'Sub Total', 'Sub Total'],
            ];
            $createFields = [
                'price_after_discount'    => 'Price After Discount',
                'overall_discount_amount' => 'Overall Discount Amount',
                'tax_amount'              => 'Tax Amount',
                'margin'                  => 'Margin',
            ];

            foreach ($inventoryModules as $inventoryModuleName) {
                $inventoryModuleEntity = CRMEntity::getInstance($inventoryModuleName);
                $inventoryModule = Vtiger_Module::getInstance($inventoryModuleName);
                $inventoryModuleId = $inventoryModule->getId();
                $inventoryModuleModel = Vtiger_Module_Model::getInstance($inventoryModuleName);
                $block = Vtiger_Block::getInstance('LBL_ITEM_DETAILS', $inventoryModule);
                $updateFieldSql = 'UPDATE vtiger_field SET fieldname = ?, fieldlabel = ?, block = ?, displaytype = 1, presence = 0 WHERE tabid = ? AND fieldname = ?';
                $updateCvColumnlistSql = 'UPDATE vtiger_cvcolumnlist SET columnname = REPLACE(columnname, ?, ?) WHERE cvid IN (SELECT cvid FROM vtiger_customview WHERE entitytype = ?)';
                $updateCvAdvFilterSql = 'UPDATE vtiger_cvadvfilter SET columnname = REPLACE(columnname, ?, ?) WHERE cvid IN (SELECT cvid FROM vtiger_customview WHERE entitytype = ?)';
                $updateWorkflowsSql = 'UPDATE com_vtiger_workflows SET `test` = REPLACE(`test`, ?, ?) WHERE module_name = ?';

                foreach ($deactivateFields as $fieldName) {
                    $field = Vtiger_Field_Model::getInstance($fieldName, $inventoryModuleModel);

                    if (!$field) {
                        continue;
                    }

                    $field->set('presence', 1);
                    $field->save();
                }

                foreach ($changeFields as $oldFieldName => $changeFieldData) {
                    $controlField = Vtiger_Field::getInstance($oldFieldName, $inventoryModule);

                    if (!$controlField) {
                        continue;
                    }

                    $newFieldName = $changeFieldData[0];
                    $newFieldLabel = $changeFieldData[1];
                    $oldLabel = $changeFieldData[2];
                    $crazyOldLabel = $inventoryModuleName . '_' . str_replace(' ', '_', $oldLabel);
                    $crazyNewLabel = $inventoryModuleName . '_' . str_replace(' ', '_', $newFieldLabel);

                    $this->db->pquery($updateFieldSql, [$newFieldName, $newFieldLabel, $block->id, $inventoryModuleId, $oldFieldName]);
                    $this->db->pquery($updateCvColumnlistSql, [$oldFieldName . ':' . $crazyOldLabel, $newFieldName . ':' . $crazyNewLabel, $inventoryModuleName]);
                    $this->db->pquery($updateCvAdvFilterSql, [$oldFieldName . ':' . $crazyOldLabel, $newFieldName . ':' . $crazyNewLabel, $inventoryModuleName]);
                    $this->db->pquery($updateWorkflowsSql, ['"' . $oldFieldName . '"', '"' . $newFieldName . '"', $inventoryModuleName]);
                }

                foreach ($createFields as $fieldName => $fieldLabel) {
                    $controlField = Vtiger_Field::getInstance($fieldName, $inventoryModule);

                    if ($controlField) {
                        continue;
                    }

                    $field = new Vtiger_Field();
                    $field->name = $fieldName;
                    $field->label = $fieldLabel;
                    $field->table = $inventoryModuleEntity->table_name;
                    $field->column = $fieldName;
                    $field->columntype = 'DECIMAL(25,4) DEFAULT NULL';
                    $field->uitype = 71;
                    $field->typeofdata = 'N~O';
                    $field->displaytype = 1;
                    $field->quickcreate = 0;
                    $field->summaryfield = 0;
                    $field->presence = 0;
                    $field->masseditable = 0;
                    $field->save($block);
                }
            }
        }
    }
} else {
    $baseFileName = str_replace('.php', '', basename(__FILE__));
    $this->makeAborting($this->wrongClassName . $baseFileName);
}