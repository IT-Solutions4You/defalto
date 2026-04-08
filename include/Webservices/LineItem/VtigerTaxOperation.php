<?php
/*********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
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

require_once "include/Webservices/VtigerActorOperation.php";
require_once 'include/Webservices/LineItem/VtigerTaxMeta.php';
require_once("include/events/include.inc");
require_once 'modules/com_vtiger_workflow/VTEntityCache.inc';
require_once 'data/CRMEntity.php';
require_once 'include/events/SqlResultIterator.inc';
require_once 'include/Webservices/LineItem/VtigerLineItemMeta.php';
require_once 'include/Webservices/Retrieve.php';
require_once 'include/Webservices/Update.php';
require_once 'include/Webservices/Utils.php';

/**
 * Description of VtigerTaxOperation
 */
class VtigerTaxOperation extends VtigerActorOperation
{
    public function __construct($webserviceObject, $user, $adb, $log)
    {
        parent::__construct($webserviceObject, $user, $adb, $log);
        $this->entityTableName = $this->getActorTables();
        if ($this->entityTableName === null) {
            throw new WebServiceException(WebServiceErrorCode::$UNKOWNENTITY, "Entity is not associated with any tables");
        }
        $this->meta = new VtigerTaxMeta($this->entityTableName, $webserviceObject, $adb, $user);
        $this->moduleFields = null;
    }

    public function create($elementType, $taxElement)
    {
        $element = $this->restrictFields($taxElement);
        $element = $this->sanitizeElementForInsert($element);

        $taxModel = Core_Tax_Model::getInstance();
        $this->applyElementToTaxModel($taxModel, $element);
        $taxModel->save();

        return $this->buildTaxElement($taxModel);
    }

    public function update($element)
    {
        $id = vtws_getIdComponents($element['id'])[1];
        $taxModel = Core_Tax_Model::getInstanceById((int)$id);

        if (!$taxModel) {
            throw new WebServiceException(WebServiceErrorCode::$RECORDNOTFOUND, "Record not found");
        }

        $element = $this->sanitizeElementForInsert($element);
        $this->applyElementToTaxModel($taxModel, $element);
        $taxModel->save();

        return $this->buildTaxElement($taxModel);
    }

    public function delete($id)
    {
        $ids = vtws_getIdComponents($id);
        $elemId = (int)$ids[1];
        $taxModel = Core_Tax_Model::getInstanceById($elemId);

        if (!$taxModel) {
            throw new WebServiceException(WebServiceErrorCode::$RECORDNOTFOUND, "Record not found");
        }

        $taxModel->delete();

        return ["status" => "successful"];
    }

    public function retrieve($id)
    {
        $ids = vtws_getIdComponents($id);
        $elemId = (int)$ids[1];
        $taxModel = Core_Tax_Model::getInstanceById($elemId);

        if (!$taxModel) {
            throw new WebServiceException(WebServiceErrorCode::$RECORDNOTFOUND, "Record not found");
        }

        return $this->buildTaxElement($taxModel);
    }

    /**
     * Function to sanitize element for insert
     *
     * @param <Array> $element
     *
     * @return <Array>
     */
    private function sanitizeElementForInsert($element)
    {
        $element['taxlabel'] = $element['taxlabel'] ?? $element['tax_label'] ?? $element['taxname'] ?? '';
        $element['percentage'] = $element['percentage'] ?? 0;

        $taxName = $element['taxname'] ?? null;

        if (empty($taxName) && !empty($element['id'])) {
            $taxName = $this->formatTaxName(vtws_getIdComponents($element['id'])[1]);
        }

        $taxFormula = '';

        if (!empty($taxName) && !empty($element[$taxName . '_formula'])) {
            $taxFormula = $element[$taxName . '_formula'];
        } elseif (!empty($element['formula'])) {
            $taxFormula = $element['formula'];
        }

        $compoundOn = $this->resolveCompoundOnFromFormula($taxFormula);
        $regions = $this->resolveRegionsFromElement($element);

        $method = $element['method'] ?? 'Simple';

        if ($compoundOn) {
            $method = 'Compound';
        }

        if (($element['method'] ?? '') === 'Deducted' && !$compoundOn && !$regions) {
            $method = 'Deducted';
        }

        $element['method'] = $method;
        $element['compoundon'] = Zend_Json::encode($compoundOn);
        $element['regions'] = Zend_Json::encode($regions);
        $element['deleted'] = (int)($element['deleted'] ?? 0);
        $element['active'] = $element['deleted'] ? 0 : (int)($element['active'] ?? 1);

        return $element;
    }

    /**
     * @param Core_Tax_Model $taxModel
     * @param array $element
     * @return void
     */
    private function applyElementToTaxModel(Core_Tax_Model $taxModel, array $element): void
    {
        $taxModel->setLabel($element['taxlabel']);
        $taxModel->set('percentage', (float)$element['percentage']);
        $taxModel->set('method', $element['method']);
        $taxModel->set('compound_on', $element['compoundon']);
        $taxModel->set('regions', $element['regions']);
        $taxModel->set('deleted', (int)$element['deleted']);
        $taxModel->set('active', (int)$element['active']);
    }

    /**
     * @param Core_Tax_Model $taxModel
     * @return array
     */
    private function buildTaxElement(Core_Tax_Model $taxModel): array
    {
        $taxId = (int)$taxModel->getId();
        $taxName = $this->formatTaxName($taxId);
        $element = [
            'id' => vtws_getId($this->meta->getEntityId(), $taxId),
            'taxid' => $taxId,
            'taxname' => $taxName,
            'taxlabel' => $taxModel->getLabel(),
            'percentage' => $taxModel->getPercentage(),
            'method' => $taxModel->getTaxMethod(),
            'deleted' => $taxModel->isDeleted() ? 1 : 0,
            'active' => $taxModel->isActive() ? 1 : 0,
        ];

        $regions = Zend_Json::decode(html_entity_decode((string)$taxModel->get('regions')));

        if ($regions) {
            $regionMap = $this->getRegionNameMap();

            foreach ($regions as $regionInfo) {
                if (!isset($regionInfo['region_id'])) {
                    continue;
                }

                $regionId = (int)$regionInfo['region_id'];

                if (isset($regionMap[$regionId])) {
                    $element[$regionMap[$regionId]] = $regionInfo['value'];
                }
            }
        }

        $compoundOn = Zend_Json::decode(html_entity_decode((string)$taxModel->get('compound_on')));

        if ($compoundOn) {
            $compoundInfo = [];

            foreach ($compoundOn as $taxId) {
                $compoundInfo[] = $this->formatTaxName((int)$taxId);
            }

            $element[$taxName . '_formula'] = implode('+', $compoundInfo);
        }

        return $element;
    }

    /**
     * @param string $taxFormula
     * @return array
     * @throws Exception
     */
    private function resolveCompoundOnFromFormula(string $taxFormula): array
    {
        if (empty($taxFormula)) {
            return [];
        }

        $tokens = array_filter(array_map('trim', explode('+', $taxFormula)));

        if (empty($tokens)) {
            return [];
        }

        $taxes = Core_Tax_Model::getAllTaxes();
        $tokenMap = [];

        foreach ($taxes as $tax) {
            if (!$tax->isActive()) {
                continue;
            }

            $taxId = (int)$tax->getId();
            $tokenMap[$this->formatTaxName($taxId)] = $tax;
            $tokenMap[$tax->getLabel()] = $tax;
        }

        $compoundOn = [];

        foreach ($tokens as $token) {
            if (!isset($tokenMap[$token])) {
                continue;
            }

            $tax = $tokenMap[$token];

            if ($tax->getTaxMethod() === 'Simple') {
                $compoundOn[] = (int)$tax->getId();
            }
        }

        return $compoundOn;
    }

    /**
     * @param array $element
     * @return array
     */
    private function resolveRegionsFromElement(array $element): array
    {
        $regions = [];
        $regionMap = $this->getRegionNameMap();

        foreach ($regionMap as $regionId => $regionKey) {
            if (array_key_exists($regionKey, $element)) {
                $regions[] = [
                    'region_id' => $regionId,
                    'value' => $element[$regionKey],
                ];
            }
        }

        return $regions;
    }

    /**
     * @return array
     * @throws Exception
     */
    private function getRegionNameMap(): array
    {
        $regions = Core_TaxRegion_Model::getAllRegions();
        $map = [];

        foreach ($regions as $region) {
            $name = strtolower(str_replace(' ', '_', $region->getName()));
            $map[$region->getId()] = $name;
        }

        return $map;
    }

    /**
     * @param int $taxId
     * @return string
     */
    private function formatTaxName(int $taxId): string
    {
        return 'tax' . $taxId;
    }
}