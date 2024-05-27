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

if (!class_exists('Migration_20240411112311')) {
    class Migration_20240411112311 extends AbstractMigrations
    {
        /**
         * @param string $fileName
         */
        public function migrate(string $fileName): void
        {
            $moduleName = 'InventoryItem';

            // create module
            $module = new Vtiger_Module();
            $module->name = $moduleName;
            $module->label = $moduleName;
            $module->customized = 0;
            $module->basetable = 'df_inventoryitem';
            $module->basetableid = 'inventoryitemid';
            $module->save();

            vtws_addDefaultModuleTypeEntity($moduleName);

            $this->db->pquery(
                'CREATE TABLE IF NOT EXISTS `df_inventoryitem` (
                                     `inventoryitemid` INT(11) NULL DEFAULT NULL,
                                     `inventoryitem_no` VARCHAR(100) NULL DEFAULT NULL COLLATE \'utf8_general_ci\',
                                     PRIMARY KEY (`inventoryitemid`) USING BTREE
                                 )
                                 COLLATE=\'utf8_general_ci\'
                                 ENGINE=InnoDB',
                []
            );
            $this->db->pquery(
                'CREATE TABLE IF NOT EXISTS `df_inventoryitemcf` (
                                     `inventoryitemid` INT(11) NOT NULL,
                                     PRIMARY KEY (`inventoryitemid`) USING BTREE
                                 )
                                 COLLATE=\'utf8_general_ci\'
                                 ENGINE=InnoDB
                                 ;',
                []
            );
            $this->db->pquery(
                'CREATE TABLE IF NOT EXISTS `df_inventoryitemtaxrel` (
                                     `inventoryitemid` INT(11) NOT NULL,
                                     `taxid` INT(11) NOT NULL,
                                     `sequence` INT(11) NOT NULL,
                                     `percentage` INT(11) NOT NULL,
                                     `amount` INT(11) NOT NULL,
                                     PRIMARY KEY (`inventoryitemid`) USING BTREE
                                 )
                                 COLLATE=\'utf8_general_ci\'
                                 ENGINE=InnoDB
                                 ;',
                []
            );

            // add primary block
            $headerBlock = new Vtiger_Block();
            $headerBlock->label = 'LBL_INVENTORYITEM_INFORMATION';
            $headerBlock->save($module);

            // fields for header block
            $subject = new Vtiger_Field();
            $subject->column = 'item_text';
            $subject->table = $module->basetable;
            $subject->uitype = 1;
            $subject->name = 'item_text';
            $subject->label = 'Item text';
            $subject->presence = 0;
            $subject->maximumlength = 100;
            $subject->sequence = 1;
            $subject->typeofdata = 'V~O';
            $subject->quickcreate = 0;
            $subject->masseditable = 0;
            $subject->summaryfield = 1;
            $subject->columntype = 'VARCHAR(255)';
            $subject->save($headerBlock);
            $module->setEntityIdentifier($subject);

            $assignedTo = new Vtiger_Field();
            $assignedTo->column = 'smownerid';
            $assignedTo->table = 'vtiger_crmentity';
            $assignedTo->uitype = 53;
            $assignedTo->name = 'assigned_user_id';
            $assignedTo->label = 'Assigned To';
            $assignedTo->presence = 0;
            $assignedTo->sequence = 100;
            $assignedTo->typeofdata = 'V~M';
            $assignedTo->quickcreate = 3;
            $assignedTo->masseditable = 1;
            $assignedTo->save($headerBlock);

            $created = new Vtiger_Field();
            $created->table = 'vtiger_crmentity';
            $created->uitype = 70;
            $created->name = 'createdtime';
            $created->label = 'Created Time';
            $created->presence = 0;
            $created->sequence = 101;
            $created->displaytype = 2;
            $created->save($headerBlock);

            $modified = new Vtiger_Field();
            $modified->table = 'vtiger_crmentity';
            $modified->uitype = 70;
            $modified->name = 'modifiedtime';
            $modified->label = 'Modified Time';
            $modified->presence = 0;
            $modified->sequence = 102;
            $modified->displaytype = 2;
            $modified->save($headerBlock);

            $modifiedBy = new Vtiger_Field();
            $modifiedBy->table = 'vtiger_crmentity';
            $modifiedBy->uitype = 70;
            $modifiedBy->name = 'modifiedby';
            $modifiedBy->label = 'Last Modified By';
            $modifiedBy->presence = 0;
            $modifiedBy->sequence = 103;
            $modifiedBy->displaytype = 2;
            $modifiedBy->save($headerBlock);

            $ciBlock = new Vtiger_Block();
            $ciBlock->label = 'LBL_CUSTOM_INFORMATION';
            $ciBlock->save($module);

            $descriptionBlock = new Vtiger_Block();
            $descriptionBlock->label = 'LBL_DESCRIPTION_INFORMATION';
            $descriptionBlock->save($module);

            $description = new Vtiger_Field();
            $description->table = 'vtiger_crmentity';
            $description->uitype = 19;
            $description->name = 'description';
            $description->label = 'Description';
            $description->presence = 0;
            $description->typeofdata = 'V~O';
            $description->quickcreate = 1;
            $description->masseditable = 1;
            $description->save($descriptionBlock);

            // Create default custom filter (mandatory)
            $filter1 = new Vtiger_Filter();
            $filter1->name = 'All';
            $filter1->isdefault = true;
            $module->addFilter($filter1);
            // Add fields to the filter created
            $filter1->addField($subject, 1)->addField($assignedTo, 2);

            // Set sharing access of this module
            $module->setDefaultSharing();

            // Enable and Disable available tools
            $module->enableTools(['Import', 'Export', 'Merge']);

            // Initialize Webservice support
            $module->initWebservice();

            include_once 'vtlib/Vtiger/Module.php';
            include_once 'modules/ModTracker/ModTracker.php';

            //Enable ModTracker for the module
            ModTracker::enableTrackingForModule(getTabid($moduleName));

            $product = new Vtiger_Field();
            $product->table = $module->basetable;
            $product->name = 'productid';
            $product->column = 'productid';
            $product->label = 'Product Name';
            $product->uitype = 10;
            $product->presence = 0;
            $product->sequence = 2;
            $product->columntype = 'INT(11)';
            $product->typeofdata = 'V~O';
            $product->quickcreate = 1;
            $product->masseditable = 0;
            $product->summaryfield = 0;
            $product->save($headerBlock);
            $product->setRelatedModules(['Products', 'Services']);

            $parentModule = new Vtiger_Field();
            $parentModule->table = $module->basetable;
            $parentModule->name = 'parentid';
            $parentModule->column = 'parentid';
            $parentModule->label = 'Parent';
            $parentModule->uitype = 10;
            $parentModule->presence = 0;
            $parentModule->sequence = 3;
            $parentModule->columntype = 'INT(11)';
            $parentModule->typeofdata = 'V~O';
            $parentModule->quickcreate = 1;
            $parentModule->masseditable = 0;
            $parentModule->summaryfield = 0;
            $parentModule->save($headerBlock);
            $parentModule->setRelatedModules(['Quotes', 'SalesOrder', 'PurchaseOrder', 'Invoice']);

            $parentItem = new Vtiger_Field();
            $parentItem->table = $module->basetable;
            $parentItem->name = 'parentitemid';
            $parentItem->column = 'parentitemid';
            $parentItem->label = 'Parent Inventory Item';
            $parentItem->uitype = 10;
            $parentItem->presence = 0;
            $parentItem->sequence = 4;
            $parentItem->columntype = 'INT(11)';
            $parentItem->typeofdata = 'V~O';
            $parentItem->quickcreate = 1;
            $parentItem->masseditable = 0;
            $parentItem->summaryfield = 0;
            $parentItem->save($headerBlock);
            $parentItem->setRelatedModules(['InventoryItem']);

            $quantity = new Vtiger_Field();
            $quantity->table = $module->basetable;
            $quantity->name = 'quantity';
            $quantity->column = 'quantity';
            $quantity->label = 'Quantity';
            $quantity->uitype = 7;
            $quantity->presence = 0;
            $quantity->sequence = 5;
            $quantity->columntype = 'DECIMAL(25,4) DEFAULT NULL';
            $quantity->typeofdata = 'N~O';
            $quantity->quickcreate = 1;
            $quantity->masseditable = 0;
            $quantity->summaryfield = 0;
            $quantity->save($headerBlock);

            $unit = new Vtiger_Field();
            $unit->column = 'unit';
            $unit->table = $module->basetable;
            $unit->uitype = 1;
            $unit->name = 'unit';
            $unit->label = 'Unit';
            $unit->presence = 0;
            $unit->maximumlength = 100;
            $unit->sequence = 6;
            $unit->typeofdata = 'V~O';
            $unit->quickcreate = 0;
            $unit->masseditable = 0;
            $unit->summaryfield = 1;
            $unit->columntype = 'VARCHAR(255) DEFAULT NULL';
            $unit->save($headerBlock);

            $price = new Vtiger_Field();
            $price->table = $module->basetable;
            $price->name = 'price';
            $price->column = 'price';
            $price->label = 'Price';
            $price->uitype = 7;
            $price->presence = 0;
            $price->sequence = 7;
            $price->columntype = 'DECIMAL(25,4) DEFAULT NULL';
            $price->typeofdata = 'NN~O';
            $price->quickcreate = 1;
            $price->masseditable = 0;
            $price->summaryfield = 0;
            $price->save($headerBlock);

            $subTotal = new Vtiger_Field();
            $subTotal->table = $module->basetable;
            $subTotal->name = 'subtotal';
            $subTotal->column = 'subtotal';
            $subTotal->label = 'Subtotal';
            $subTotal->uitype = 7;
            $subTotal->presence = 0;
            $subTotal->sequence = 8;
            $subTotal->columntype = 'DECIMAL(25,4) DEFAULT NULL';
            $subTotal->typeofdata = 'N~O';
            $subTotal->quickcreate = 1;
            $subTotal->masseditable = 0;
            $subTotal->summaryfield = 0;
            $subTotal->save($headerBlock);

            $discount = new Vtiger_Field();
            $discount->table = $module->basetable;
            $discount->name = 'discount';
            $discount->column = 'discount';
            $discount->label = 'Discount';
            $discount->uitype = 7;
            $discount->presence = 0;
            $discount->sequence = 9;
            $discount->columntype = 'DECIMAL(25,4) DEFAULT NULL';
            $discount->typeofdata = 'N~O';
            $discount->quickcreate = 1;
            $discount->masseditable = 0;
            $discount->summaryfield = 0;
            $discount->save($headerBlock);

            $discountAmount = new Vtiger_Field();
            $discountAmount->table = $module->basetable;
            $discountAmount->name = 'discount_amount';
            $discountAmount->column = 'discount_amount';
            $discountAmount->label = 'Discount Amount';
            $discountAmount->uitype = 7;
            $discountAmount->presence = 0;
            $discountAmount->sequence = 10;
            $discountAmount->columntype = 'DECIMAL(25,4) DEFAULT NULL';
            $discountAmount->typeofdata = 'N~O';
            $discountAmount->quickcreate = 1;
            $discountAmount->masseditable = 0;
            $discountAmount->summaryfield = 0;
            $discountAmount->save($headerBlock);

            $totalAfterDiscount = new Vtiger_Field();
            $totalAfterDiscount->table = $module->basetable;
            $totalAfterDiscount->name = 'total_after_discount';
            $totalAfterDiscount->column = 'total_after_discount';
            $totalAfterDiscount->label = 'Total After Discount';
            $totalAfterDiscount->uitype = 7;
            $totalAfterDiscount->presence = 0;
            $totalAfterDiscount->sequence = 11;
            $totalAfterDiscount->columntype = 'DECIMAL(25,4) DEFAULT NULL';
            $totalAfterDiscount->typeofdata = 'N~O';
            $totalAfterDiscount->quickcreate = 1;
            $totalAfterDiscount->masseditable = 0;
            $totalAfterDiscount->summaryfield = 0;
            $totalAfterDiscount->save($headerBlock);

            $overallDiscount = new Vtiger_Field();
            $overallDiscount->table = $module->basetable;
            $overallDiscount->name = 'overall_discount';
            $overallDiscount->column = 'overall_discount';
            $overallDiscount->label = 'Overall Discount';
            $overallDiscount->uitype = 7;
            $overallDiscount->presence = 0;
            $overallDiscount->sequence = 12;
            $overallDiscount->columntype = 'DECIMAL(25,4) DEFAULT NULL';
            $overallDiscount->typeofdata = 'N~O';
            $overallDiscount->quickcreate = 1;
            $overallDiscount->masseditable = 0;
            $overallDiscount->summaryfield = 0;
            $overallDiscount->save($headerBlock);

            $overallDiscountAmount = new Vtiger_Field();
            $overallDiscountAmount->table = $module->basetable;
            $overallDiscountAmount->name = 'overall_discount_amount';
            $overallDiscountAmount->column = 'overall_discount_amount';
            $overallDiscountAmount->label = 'Overall Discount Amount';
            $overallDiscountAmount->uitype = 7;
            $overallDiscountAmount->presence = 0;
            $overallDiscountAmount->sequence = 13;
            $overallDiscountAmount->columntype = 'DECIMAL(25,4) DEFAULT NULL';
            $overallDiscountAmount->typeofdata = 'N~O';
            $overallDiscountAmount->quickcreate = 1;
            $overallDiscountAmount->masseditable = 0;
            $overallDiscountAmount->summaryfield = 0;
            $overallDiscountAmount->save($headerBlock);

            $totalAfterOverallDiscount = new Vtiger_Field();
            $totalAfterOverallDiscount->table = $module->basetable;
            $totalAfterOverallDiscount->name = 'total_after_overall_discount';
            $totalAfterOverallDiscount->column = 'total_after_overall_discount';
            $totalAfterOverallDiscount->label = 'Total After Overall Discount';
            $totalAfterOverallDiscount->uitype = 7;
            $totalAfterOverallDiscount->presence = 0;
            $totalAfterOverallDiscount->sequence = 14;
            $totalAfterOverallDiscount->columntype = 'DECIMAL(25,4) DEFAULT NULL';
            $totalAfterOverallDiscount->typeofdata = 'N~O';
            $totalAfterOverallDiscount->quickcreate = 1;
            $totalAfterOverallDiscount->masseditable = 0;
            $totalAfterOverallDiscount->summaryfield = 0;
            $totalAfterOverallDiscount->save($headerBlock);

            $taxAmount = new Vtiger_Field();
            $taxAmount->table = $module->basetable;
            $taxAmount->name = 'tax_amount';
            $taxAmount->column = 'tax_amount';
            $taxAmount->label = 'Tax Amount';
            $taxAmount->uitype = 7;
            $taxAmount->presence = 0;
            $taxAmount->sequence = 15;
            $taxAmount->columntype = 'DECIMAL(25,4) DEFAULT NULL';
            $taxAmount->typeofdata = 'N~O';
            $taxAmount->quickcreate = 1;
            $taxAmount->masseditable = 0;
            $taxAmount->summaryfield = 0;
            $taxAmount->save($headerBlock);

            $total = new Vtiger_Field();
            $total->table = $module->basetable;
            $total->name = 'total';
            $total->column = 'total';
            $total->label = 'Total';
            $total->uitype = 7;
            $total->presence = 0;
            $total->sequence = 16;
            $total->columntype = 'DECIMAL(25,4) DEFAULT NULL';
            $total->typeofdata = 'N~O';
            $total->quickcreate = 1;
            $total->masseditable = 0;
            $total->summaryfield = 0;
            $total->save($headerBlock);

            $purchaseCost = new Vtiger_Field();
            $purchaseCost->table = $module->basetable;
            $purchaseCost->name = 'purchase_cost';
            $purchaseCost->column = 'purchase_cost';
            $purchaseCost->label = 'Purchase Cost';
            $purchaseCost->uitype = 7;
            $purchaseCost->presence = 0;
            $purchaseCost->sequence = 17;
            $purchaseCost->columntype = 'DECIMAL(25,4) DEFAULT NULL';
            $purchaseCost->typeofdata = 'N~O';
            $purchaseCost->quickcreate = 1;
            $purchaseCost->masseditable = 0;
            $purchaseCost->summaryfield = 0;
            $purchaseCost->save($headerBlock);

            $margin = new Vtiger_Field();
            $margin->table = $module->basetable;
            $margin->name = 'margin';
            $margin->column = 'margin';
            $margin->label = 'Margin';
            $margin->uitype = 7;
            $margin->displaytype = 2;
            $margin->presence = 0;
            $margin->sequence = 18;
            $margin->columntype = 'DECIMAL(25,4) DEFAULT NULL';
            $margin->typeofdata = 'N~O';
            $margin->quickcreate = 1;
            $margin->masseditable = 0;
            $margin->summaryfield = 0;
            $margin->save($headerBlock);

            $sequence = new Vtiger_Field();
            $sequence->table = $module->basetable;
            $sequence->name = 'sequence';
            $sequence->column = 'sequence';
            $sequence->label = 'Sequence';
            $sequence->uitype = 7;
            $sequence->presence = 0;
            $sequence->sequence = 19;
            $sequence->columntype = 'INT(11) DEFAULT 1';
            $sequence->typeofdata = 'I~O';
            $sequence->quickcreate = 1;
            $sequence->masseditable = 0;
            $sequence->summaryfield = 0;
            $sequence->save($headerBlock);
        }
    }
} else {
    $baseFileName = str_replace('.php', '', basename(__FILE__));
    $this->makeAborting($this->wrongClassName . $baseFileName);
}