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

            $parentModule = new Vtiger_Field();
            $parentModule->table = $module->basetable;
            $parentModule->name = 'parentid';
            $parentModule->column = 'parentid';
            $parentModule->label = 'Parent';
            $parentModule->uitype = 10;
            $parentModule->presence = 0;
            $parentModule->sequence = 2;
            $parentModule->columntype = 'INT(11)';
            $parentModule->typeofdata = 'V~O';
            $parentModule->quickcreate = 1;
            $parentModule->masseditable = 0;
            $parentModule->summaryfield = 0;
            $parentModule->save($headerBlock);
            $parentModule->setRelatedModules(['Quotes', 'SalesOrder', 'PurchaseOrder', 'Invoice']);

            $product = new Vtiger_Field();
            $product->table = $module->basetable;
            $product->name = 'productid';
            $product->column = 'productid';
            $product->label = 'Product Name';
            $product->uitype = 10;
            $product->presence = 0;
            $product->sequence = 3;
            $product->columntype = 'INT(11)';
            $product->typeofdata = 'V~O';
            $product->quickcreate = 1;
            $product->masseditable = 0;
            $product->summaryfield = 0;
            $product->save($headerBlock);
            $product->setRelatedModules(['Products', 'Services']);

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

            $unit = new Vtiger_Field();
            $unit->column = 'unit';
            $unit->table = $module->basetable;
            $unit->uitype = 1;
            $unit->name = 'unit';
            $unit->label = 'Unit';
            $unit->presence = 0;
            $unit->maximumlength = 100;
            $unit->sequence = 5;
            $unit->typeofdata = 'V~O';
            $unit->quickcreate = 0;
            $unit->masseditable = 0;
            $unit->summaryfield = 1;
            $unit->columntype = 'VARCHAR(255) DEFAULT NULL';
            $unit->save($headerBlock);

            $quantity = new Vtiger_Field();
            $quantity->table = $module->basetable;
            $quantity->name = 'quantity';
            $quantity->column = 'quantity';
            $quantity->label = 'Quantity';
            $quantity->uitype = 7;
            $quantity->presence = 0;
            $quantity->sequence = 6;
            $quantity->columntype = 'DECIMAL(25,4) DEFAULT NULL';
            $quantity->typeofdata = 'N~O';
            $quantity->quickcreate = 1;
            $quantity->masseditable = 0;
            $quantity->summaryfield = 0;
            $quantity->save($headerBlock);

            $price = new Vtiger_Field();
            $price->table = $module->basetable;
            $price->name = 'price';
            $price->column = 'price';
            $price->label = 'Price';
            $price->uitype = 71;
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
            $subTotal->uitype = 71;
            $subTotal->presence = 0;
            $subTotal->sequence = 8;
            $subTotal->columntype = 'DECIMAL(25,4) DEFAULT NULL';
            $subTotal->typeofdata = 'N~O';
            $subTotal->quickcreate = 1;
            $subTotal->masseditable = 0;
            $subTotal->summaryfield = 0;
            $subTotal->save($headerBlock);

            $discountType = new Vtiger_Field();
            $discountType->table = $module->basetable;
            $discountType->name = 'discount_type';
            $discountType->column = 'discount_type';
            $discountType->label = 'Discount Type';
            $discountType->uitype = 16;
            $discountType->presence = 0;
            $discountType->sequence = 9;
            $discountType->columntype = 'VARCHAR(255) DEFAULT NULL';
            $discountType->typeofdata = 'V~O';
            $discountType->quickcreate = 1;
            $discountType->masseditable = 0;
            $discountType->save($headerBlock);
            $discountType->setPicklistValues(['Percentage', 'Direct', 'Product Unit Price']);

            $discount = new Vtiger_Field();
            $discount->table = $module->basetable;
            $discount->name = 'discount';
            $discount->column = 'discount';
            $discount->label = 'Discount';
            $discount->uitype = 7;
            $discount->presence = 0;
            $discount->sequence = 10;
            $discount->columntype = 'DECIMAL(25,4) DEFAULT NULL';
            $discount->typeofdata = 'N~O';
            $discount->quickcreate = 1;
            $discount->masseditable = 0;
            $discount->summaryfield = 0;
            $discount->save($headerBlock);

            $overallDiscount = new Vtiger_Field();
            $overallDiscount->table = $module->basetable;
            $overallDiscount->name = 'overall_discount';
            $overallDiscount->column = 'overall_discount';
            $overallDiscount->label = 'Overall Discount';
            $overallDiscount->uitype = 9;
            $overallDiscount->presence = 0;
            $overallDiscount->sequence = 11;
            $overallDiscount->columntype = 'DECIMAL(25,4) DEFAULT NULL';
            $overallDiscount->typeofdata = 'N~O';
            $overallDiscount->quickcreate = 1;
            $overallDiscount->masseditable = 0;
            $overallDiscount->summaryfield = 0;
            $overallDiscount->save($headerBlock);

            $discountAmount = new Vtiger_Field();
            $discountAmount->table = $module->basetable;
            $discountAmount->name = 'discount_amount';
            $discountAmount->column = 'discount_amount';
            $discountAmount->label = 'Discount Amount';
            $discountAmount->uitype = 71;
            $discountAmount->presence = 0;
            $discountAmount->sequence = 12;
            $discountAmount->columntype = 'DECIMAL(25,4) DEFAULT NULL';
            $discountAmount->typeofdata = 'N~O';
            $discountAmount->quickcreate = 1;
            $discountAmount->masseditable = 0;
            $discountAmount->summaryfield = 0;
            $discountAmount->save($headerBlock);

            $overallDiscountAmount = new Vtiger_Field();
            $overallDiscountAmount->table = $module->basetable;
            $overallDiscountAmount->name = 'overall_discount_amount';
            $overallDiscountAmount->column = 'overall_discount_amount';
            $overallDiscountAmount->label = 'Overall Discount Amount';
            $overallDiscountAmount->uitype = 71;
            $overallDiscountAmount->presence = 0;
            $overallDiscountAmount->sequence = 13;
            $overallDiscountAmount->columntype = 'DECIMAL(25,4) DEFAULT NULL';
            $overallDiscountAmount->typeofdata = 'N~O';
            $overallDiscountAmount->quickcreate = 1;
            $overallDiscountAmount->masseditable = 0;
            $overallDiscountAmount->summaryfield = 0;
            $overallDiscountAmount->save($headerBlock);

            $priceAfterDiscount = new Vtiger_Field();
            $priceAfterDiscount->table = $module->basetable;
            $priceAfterDiscount->name = 'price_after_discount';
            $priceAfterDiscount->column = 'price_after_discount';
            $priceAfterDiscount->label = 'Price After Discount';
            $priceAfterDiscount->uitype = 71;
            $priceAfterDiscount->presence = 0;
            $priceAfterDiscount->sequence = 14;
            $priceAfterDiscount->columntype = 'DECIMAL(25,4) DEFAULT NULL';
            $priceAfterDiscount->typeofdata = 'N~O';
            $priceAfterDiscount->quickcreate = 1;
            $priceAfterDiscount->masseditable = 0;
            $priceAfterDiscount->summaryfield = 0;
            $priceAfterDiscount->save($headerBlock);

            $priceAfterOverallDiscount = new Vtiger_Field();
            $priceAfterOverallDiscount->table = $module->basetable;
            $priceAfterOverallDiscount->name = 'price_after_overall_discount';
            $priceAfterOverallDiscount->column = 'price_after_overall_discount';
            $priceAfterOverallDiscount->label = 'Price After Overall Discount';
            $priceAfterOverallDiscount->uitype = 71;
            $priceAfterOverallDiscount->presence = 0;
            $priceAfterOverallDiscount->sequence = 15;
            $priceAfterOverallDiscount->columntype = 'DECIMAL(25,4) DEFAULT NULL';
            $priceAfterOverallDiscount->typeofdata = 'N~O';
            $priceAfterOverallDiscount->quickcreate = 1;
            $priceAfterOverallDiscount->masseditable = 0;
            $priceAfterOverallDiscount->summaryfield = 0;
            $priceAfterOverallDiscount->save($headerBlock);

            $tax = new Vtiger_Field();
            $tax->table = $module->basetable;
            $tax->name = 'tax';
            $tax->column = 'tax';
            $tax->label = 'Tax';
            $tax->uitype = 7;
            $tax->presence = 0;
            $tax->sequence = 16;
            $tax->columntype = 'DECIMAL(25,4) DEFAULT NULL';
            $tax->typeofdata = 'N~O';
            $tax->quickcreate = 1;
            $tax->masseditable = 0;
            $tax->summaryfield = 0;
            $tax->save($headerBlock);

            $taxAmount = new Vtiger_Field();
            $taxAmount->table = $module->basetable;
            $taxAmount->name = 'tax_amount';
            $taxAmount->column = 'tax_amount';
            $taxAmount->label = 'Tax Amount';
            $taxAmount->uitype = 71;
            $taxAmount->presence = 0;
            $taxAmount->sequence = 17;
            $taxAmount->columntype = 'DECIMAL(25,4) DEFAULT NULL';
            $taxAmount->typeofdata = 'N~O';
            $taxAmount->quickcreate = 1;
            $taxAmount->masseditable = 0;
            $taxAmount->summaryfield = 0;
            $taxAmount->save($headerBlock);

            $totalPrice = new Vtiger_Field();
            $totalPrice->table = $module->basetable;
            $totalPrice->name = 'price_total';
            $totalPrice->column = 'price_total';
            $totalPrice->label = 'Price Total';
            $totalPrice->uitype = 71;
            $totalPrice->presence = 0;
            $totalPrice->sequence = 18;
            $totalPrice->columntype = 'DECIMAL(25,4) DEFAULT NULL';
            $totalPrice->typeofdata = 'N~O';
            $totalPrice->quickcreate = 1;
            $totalPrice->masseditable = 0;
            $totalPrice->summaryfield = 0;
            $totalPrice->save($headerBlock);

            $purchaseCost = new Vtiger_Field();
            $purchaseCost->table = $module->basetable;
            $purchaseCost->name = 'purchase_cost';
            $purchaseCost->column = 'purchase_cost';
            $purchaseCost->label = 'Purchase Cost';
            $purchaseCost->uitype = 71;
            $purchaseCost->presence = 0;
            $purchaseCost->sequence = 19;
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
            $margin->uitype = 71;
            $margin->presence = 0;
            $margin->sequence = 20;
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
            $sequence->sequence = 21;
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