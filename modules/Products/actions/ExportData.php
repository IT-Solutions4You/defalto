<?php
/**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *********************************************************************************/
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * Modifications and additions by IT-Solutions4You (ITS4YOU) are Copyright (c) IT-Solutions4You s.r.o.
 *
 * These contributions are licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Products_ExportData_Action extends Vtiger_ExportData_Action
{
    var $allTaxes = [];
    var $allRegions = [];
    var $taxHeaders = [];

    public function getAllTaxes()
    {
        if (!$this->allTaxes) {
            $this->allTaxes = Inventory_TaxRecord_Model::getProductTaxes();
        }

        return $this->allTaxes;
    }

    public function getAllRegions()
    {
        if (!$this->allRegions) {
            $this->allRegions = Inventory_TaxRegion_Model::getAllTaxRegions();
        }

        return $this->allRegions;
    }

    public function getHeaders()
    {
        if (!$this->headers) {
            $translatedHeaders = parent::getHeaders();
            $taxModels = $this->getAllTaxes();
            foreach ($taxModels as $taxId => $taxModel) {
                $taxName = $taxModel->getName();
                $decodedTaxName = decode_html($taxName);
                $translatedHeaders[] = $decodedTaxName;
                $this->taxHeaders[] = $decodedTaxName;

                $regions = $taxModel->getRegionTaxes();
                foreach ($regions as $regionsTaxInfo) {
                    $allRegions = $this->getAllRegions();
                    foreach (array_fill_keys($regionsTaxInfo['list'], $regionsTaxInfo['value']) as $regionId => $taxPercentage) {
                        if ($allRegions[$regionId]) {
                            $taxRegionName = $taxName . '-' . $allRegions[$regionId]->getName();
                            $taxRegionName = decode_html($taxRegionName);
                            $translatedHeaders[] = $taxRegionName;
                            $this->taxHeaders[] = $taxRegionName;
                        }
                    }
                }
            }
            $this->headers = $translatedHeaders;
        }

        return $this->headers;
    }

    public function sanitizeValues($arr)
    {
        $recordId = $arr['crmid'];
        $arr = parent::sanitizeValues($arr);

        $headers = $this->getHeaders();
        $taxModels = $this->getAllTaxes();
        $taxValues = [];

        foreach ($taxModels as $taxId => $taxModel) {
            $taxName = $taxModel->getName();
            $taxPercentageInfo = getProductTaxPercentage($taxModel->get('taxname'), $recordId);
            $taxValues[$taxName] = $taxPercentageInfo['percentage'];

            if ($taxPercentageInfo['regions']) {
                foreach ($taxPercentageInfo['regions'] as $regionsTaxInfo) {
                    $allRegions = $this->getAllRegions();
                    foreach (array_fill_keys($regionsTaxInfo['list'], $regionsTaxInfo['value']) as $regionId => $taxPercentage) {
                        if ($allRegions[$regionId]) {
                            $regionTaxName = $taxName . '-' . $allRegions[$regionId]->getName();
                            $taxValues[$regionTaxName] = $taxPercentage;
                        }
                    }
                }
            }
        }

        foreach ($this->taxHeaders as $fieldName) {
            $arr[$fieldName] = $taxValues[$fieldName];
        }

        return $arr;
    }
}