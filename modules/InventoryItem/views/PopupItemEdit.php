<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class InventoryItem_PopupItemEdit_View extends Vtiger_Footer_View
{
    protected array $hardCodedFields = [
        'productid',
        'description',
        'quantity',
        'unit',
        'price',
        'subtotal',
        'discount',
        'discount_amount',
        'price_after_discount',
        'overall_discount',
        'overall_discount_amount',
        'price_after_overall_discount',
        'discounts_amount',
        'tax',
        'tax_amount',
        'price_total',
        'purchase_cost',
        'margin',
        'margin_amount',
        'sequence',
    ];

    /**
     * @inheritDoc
     */
    public function requiresPermission(Vtiger_Request $request)
    {
        $permissions = parent::requiresPermission($request);

        $permissions[] = ['module_parameter' => 'module', 'action' => 'EditView'];

        return $permissions;
    }

    /**
     * @inheritDoc
     */
    public function process(Vtiger_Request $request)
    {
        $viewer = $this->getViewer($request);
        $moduleName = $request->getModule();
        $recordId = $request->get('record');

        if ($recordId) {
            $recordModel = Vtiger_Record_Model::getInstanceById($recordId);
            $productId = $recordModel->get('productid');

            if ($productId) {
                $itemType = getSalesEntityType($productId);
            } else {
                $itemType = '';
            }
        } else {
            $recordModel = Vtiger_Record_Model::getCleanInstance('InventoryItem');
            $itemType = $request->get('item_type');
        }

        $itemData = $this->getItemData($recordId);

        if ($request->get('duplicate') == true) {
            $recordModel->set('sequence', '');
            $recordModel->set('id', '');
            $recordId = '';
            $itemData['sequence'] = '';
            $itemData['inventoryitemid'] = '';
        }

        $selectedFields = InventoryItem_Module_Model::getSelectedFields(getTabid($request->get('source_module')));
        $recordStructureInstance = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($recordModel, Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_EDIT);
        $recordStructure = [];

        foreach ($recordStructureInstance->getStructure() as $value) {
            foreach ($value as $key2 => $value2) {
                $recordStructure[$key2] = $value2;
            }
        }

        $hardFormattedRecordStructure = [];
        $processed = [];

        foreach ($this->hardCodedFields as $fieldName) {
            $label = vtranslate($recordStructure[$fieldName]->get('label'), 'InventoryItem');

            if ($fieldName === 'productid') {
                $seType = $itemType;
                $entityFieldNames = getEntityFieldNames($seType);
                $entityFieldName = $entityFieldNames['fieldname'];
                $entityField = Vtiger_Field_Model::getInstance($entityFieldName, Vtiger_Module_Model::getInstance($seType));
                $label = getTranslatedString($entityField->label, $seType);
            }

            $hardFormattedRecordStructure[$fieldName] = [$label, $recordStructure[$fieldName]];
            $processed[] = $fieldName;
        }

        $structure = [];

        foreach ($selectedFields as $fieldName) {
            if (in_array($fieldName, $processed)) {
                continue;
            }

            if (in_array($fieldName, $this->hardCodedFields)) {
                continue;
            }

            $label = vtranslate($recordStructure[$fieldName]->get('label'), 'InventoryItem');
            $structure[$fieldName] = [$label, $recordStructure[$fieldName]];
            $processed[] = $fieldName;
        }

        $sourceRecordModel = Vtiger_Record_Model::getInstanceById($request->get('source_record'), $request->get('source_module'));
        $currencyInfo = Vtiger_Functions::getCurrencyInfo($sourceRecordModel->get('currency_id'));
        $data = $request->get('data', []);

        if (isset($data['insert_after_sequence'])) {
            $viewer->assign('INSERT_AFTER_SEQUENCE', $data['insert_after_sequence']);
        }

        $viewer->assign('MODULE_NAME', $moduleName);
        $viewer->assign('MODULE', $moduleName);
        $viewer->assign('RECORD', $recordId);
        $viewer->assign('SOURCE_MODULE', $request->get('source_module'));
        $viewer->assign('SOURCE_RECORD', $request->get('source_record'));
        $viewer->assign('INVENTORY_ITEM_COLUMNS', $selectedFields);
        $viewer->assign('RECORD_STRUCTURE', $recordStructure);
        $viewer->assign('FORMATTED_RECORD_STRUCTURE', $structure);
        $viewer->assign('HARD_FORMATTED_RECORD_STRUCTURE', $hardFormattedRecordStructure);
        $viewer->assign('ITEM_TYPE', $itemType);
        $viewer->assign('CURRENCY_NAME', $currencyInfo['currency_name']);
        $viewer->assign('CURRENCY_SYMBOL', $currencyInfo['currency_symbol']);
        $viewer->assign('DATA', $itemData);
        $viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
        $viewer->view('PopupEdit.tpl', $moduleName);
    }

    /**
     * @inheritDoc
     */
    function preProcess(Vtiger_Request $request, $display = true)
    {
    }

    /**
     * @inheritDoc
     */
    public function postProcess(Vtiger_Request $request)
    {
        $viewer = $this->getViewer($request);
        $moduleName = $request->getModule();
        $viewer->view('PopupItemEditFooter.tpl', $moduleName);
    }

    /**
     * @inheritDoc
     */
    public function getHeaderScripts(Vtiger_Request $request)
    {
        $headerScriptInstances = parent::getHeaderScripts($request);
        $jsFileNames = ['modules.Vtiger.resources.CkEditor'];
        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);

        return array_merge($headerScriptInstances, $jsScriptInstances);
    }

    private function getItemData($recordId): array
    {
        if (!$recordId) {
            return [];
        }

        $db = PearDatabase::getInstance();
        $sql = 'SELECT df_inventoryitem.*, df_inventoryitemcf.*, vtiger_crmentity.description 
            FROM df_inventoryitem
            LEFT JOIN vtiger_crmentity ON vtiger_crmentity.crmid = df_inventoryitem.inventoryitemid
            LEFT JOIN df_inventoryitemcf ON df_inventoryitemcf.inventoryitemid = df_inventoryitem.inventoryitemid
            WHERE vtiger_crmentity.deleted = 0
            AND df_inventoryitem.inventoryitemid = ?
            ORDER BY df_inventoryitem.sequence, vtiger_crmentity.crmid';
        $result = $db->pquery($sql, [$recordId]);

        if (!$db->num_rows($result)) {
            return [];
        }

        $row = $db->fetchByAssoc($result);

        if (empty($row['productid']) && !empty($row['item_text'])) {
            $row['entityType'] = 'Text';
        } else {
            $row['entityType'] = getSalesEntityType($row['productid']);

            if (empty($row['item_text'])) {
                $row['item_text'] = getEntityName($row['entityType'], $row['productid'])[$row['productid']];
            }
        }

        $decimals = InventoryItem_Utils_Helper::fetchDecimals();

        foreach ($decimals as $fieldName => $decimalsCount) {
            if (isset($row[$fieldName])) {
                $row[$fieldName] = number_format($row[$fieldName], $decimalsCount, '.', '');
            }
        }

        $row['taxes'] = InventoryItem_TaxesForItem_Model::fetchTaxes((int)$row['inventoryitemid'], (int)$row['productid'], (int)$row['parentid']);

        foreach ($row['taxes'] as $taxData) {
            if ($taxData['selected']) {
                $percentage = $taxData['percentage'];
                $regions = json_decode($taxData['regions'], true);

                if ($percentage != $row['tax']) {
                    $found = false;
                    foreach ($regions as $regionTax) {
                        if ($regionTax == $row['tax']) {
                            $found = true;
                            break;
                        }
                    }

                    if (!$found) {
                        $row['taxes'][0] = [
                            'tax_label' => vtranslate('SAVED_TAX_VALUE_FOR', 'InventoryItem') . ' ' . $taxData['tax_label'],
                            'percentage' => $row['tax'],
                            'method' => 'Simple',
                            'compound_on' => '[]',
                            'regions' => '',
                            'deleted' => 0,
                            'active' => 1,
                            'taxid' => $taxData['taxid'],
                            'selected' => true,
                        ];
                        $row['taxes'][$taxData['taxid']]['selected'] = false;
                        ksort($row['taxes']);
                    }
                }
            }
        }

        return $row;
    }
}