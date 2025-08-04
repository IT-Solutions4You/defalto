<?php
/*************************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * Modifications and additions by IT-Solutions4You (ITS4YOU) are Copyright (c) IT-Solutions4You s.r.o.
 *
 * These contributions are licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Settings_Vtiger_TaxRecord_Model extends Vtiger_Base_Model
{
    const PRODUCT_AND_SERVICE_TAX = 0;
    const SHIPPING_AND_HANDLING_TAX = 1;

    public function __construct($values = [])
    {
        parent::__construct($values);
        $this->unMarkDeleted();
    }

    private $type;

    public function getId()
    {
        return $this->get('taxid');
    }

    public function getName()
    {
        return $this->get('taxlabel');
    }

    public function getTax()
    {
        return $this->get('percentage');
    }

    public function isDeleted()
    {
        return $this->get('deleted') == 0 ? false : true;
    }

    public function markDeleted()
    {
        return $this->set('deleted', '1');
    }

    public function unMarkDeleted()
    {
        return $this->set('deleted', '0');
    }

    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    public function getType()
    {
        return $this->type;
    }

    public function isProductTax()
    {
        return ($this->getType() == self::PRODUCT_AND_SERVICE_TAX) ? true : false;
    }

    public function isShippingTax()
    {
        return ($this->getType() == self::SHIPPING_AND_HANDLING_TAX) ? true : false;
    }

    public function getCreateTaxUrl()
    {
        return '?module=Vtiger&parent=Settings&view=TaxAjax';
    }

    public function getEditTaxUrl()
    {
        return '?module=Vtiger&parent=Settings&view=TaxAjax&type=' . $this->getType() . '&taxid=' . $this->getId();
    }

    private function getTableNameFromType()
    {
        $tablename = 'vtiger_inventorytaxinfo';

        if ($this->isShippingTax()) {
            $tablename = 'vtiger_shippingtaxinfo';
        }

        return $tablename;
    }

    public function save()
    {
        $db = PearDatabase::getInstance();

        $tablename = $this->getTableNameFromType();

        $taxId = $this->getId();

        if (!empty($taxId)) {
            $deleted = 0;
            if ($this->isDeleted()) {
                $deleted = 1;
            }
            $query = 'UPDATE ' . $tablename . ' SET taxlabel=?,percentage=?,deleted=? WHERE taxid=?';
            $params = [$this->getName(), $this->get('percentage'), $deleted, $taxId];
            $db->pquery($query, $params);
        } else {
            $taxId = $this->addTax();
        }

        return $taxId;
    }

    /**    Function used to add the tax type which will do database alterations
     *
     * @param string $taxlabel - tax label name to be added
     * @param string $taxvalue - tax value to be added
     * @param string $sh       - sh or empty , if sh passed then the tax will be added in shipping and handling related table
     *
     * @return void
     */
    public function addTax()
    {
        $adb = PearDatabase::getInstance();

        $tableName = $this->getTableNameFromType();
        $taxid = $adb->getUniqueID($tableName);
        $taxLabel = $this->getName();
        $percentage = $this->get('percentage');

        //if the tax is not available then add this tax.
        //Add this tax as a column in related table	
        if ($this->isShippingTax()) {
            $taxname = "shtax" . $taxid;
            $query = "ALTER TABLE vtiger_inventoryshippingrel ADD COLUMN $taxname decimal(7,3) DEFAULT NULL";
        } else {
            $taxname = "tax" . $taxid;
            $query = "ALTER TABLE vtiger_inventoryproductrel ADD COLUMN $taxname decimal(7,3) DEFAULT NULL";
        }
        $res = $adb->pquery($query, []);

        vimport('~~/include/utils/utils.php');

        //if the tax is added as a column then we should add this tax in the list of taxes
        if ($res) {
            $query = 'INSERT INTO ' . $tableName . ' values(?,?,?,?,?)';
            $params = [$taxid, $taxname, $taxLabel, $percentage, 0];
            $adb->pquery($query, $params);

            return $taxid;
        }
        throw new Error('Error occurred while adding tax');
    }

    public static function getProductTaxes()
    {
        vimport('~~/include/utils/InventoryUtils.php');
        $taxes = getAllTaxes();
        $recordList = [];
        foreach ($taxes as $taxInfo) {
            $taxRecord = new self();
            $taxRecord->setData($taxInfo)->setType(self::PRODUCT_AND_SERVICE_TAX);
            $recordList[] = $taxRecord;
        }

        return $recordList;
    }

    public static function getShippingTaxes()
    {
        vimport('~~/include/utils/InventoryUtils.php');
        $taxes = getAllTaxes('all', 'sh');
        $recordList = [];
        foreach ($taxes as $taxInfo) {
            $taxRecord = new self();
            $taxRecord->setData($taxInfo)->setType(self::SHIPPING_AND_HANDLING_TAX);
            $recordList[] = $taxRecord;
        }

        return $recordList;
    }

    public static function getInstanceById($id, $type = self::PRODUCT_AND_SERVICE_TAX)
    {
        $db = PearDatabase::getInstance();
        $tablename = 'vtiger_inventorytaxinfo';

        if ($type == self::SHIPPING_AND_HANDLING_TAX) {
            $tablename = 'vtiger_shippingtaxinfo';
        }

        $query = 'SELECT * FROM ' . $tablename . ' WHERE taxid=?';
        $result = $db->pquery($query, [$id]);
        $taxRecordModel = new self();
        if ($db->num_rows($result) > 0) {
            $row = $db->query_result_rowdata($result, 0);
            $taxRecordModel->setData($row)->setType($type);
        }

        return $taxRecordModel;
    }

    public static function checkDuplicate($label, $excludedIds = [], $type = self::PRODUCT_AND_SERVICE_TAX)
    {
        $db = PearDatabase::getInstance();

        if (!is_array($excludedIds)) {
            if (!empty($excludedIds)) {
                $excludedIds = [$excludedIds];
            } else {
                $excludedIds = [];
            }
        }
        $tablename = 'vtiger_inventorytaxinfo';

        if ($type == self::SHIPPING_AND_HANDLING_TAX) {
            $tablename = 'vtiger_shippingtaxinfo';
        }

        $query = 'SELECT 1 FROM ' . $tablename . ' WHERE taxlabel = ?';
        $params = [$label];

        if (!empty($excludedIds)) {
            $query .= " AND taxid NOT IN (" . generateQuestionMarks($excludedIds) . ")";
            $params = array_merge($params, $excludedIds);
        }
        $result = $db->pquery($query, $params);

        return ($db->num_rows($result) > 0) ? true : false;
    }
}