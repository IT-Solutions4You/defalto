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
        'tax',
        'tax_amount',
        'price_total',
        'purchase_cost',
        'margin',
        'margin_amount',
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

        $selectedFields = InventoryItem_Module_Model::getSelectedFields(getTabid($request->get('source_module')));
        $recordModel = Vtiger_Record_Model::getCleanInstance('InventoryItem');
        $recordStructureInstance = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($recordModel, Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_EDIT);
        $recordStructure = [];

        foreach ($recordStructureInstance->getStructure() as $value) {
            foreach ($value as $key2 => $value2) {
                $recordStructure[$key2] = $value2;
            }
        }

        $hardFormattedRecordStructure = [];
        $structure = [];
        $processed = [];

        foreach ($selectedFields as $fieldName) {
            if (in_array($fieldName, $processed)) {
                continue;
            }

            $label = vtranslate($recordStructure[$fieldName]->get('label'), 'InventoryItem');

            if (in_array($fieldName, $this->hardCodedFields)) {
                if ($fieldName === 'productid') {
                    $seType = $request->get('item_type');
                    $entityFieldNames = getEntityFieldNames($seType);
                    $entityFieldName = $entityFieldNames['fieldname'];
                    $entityField = Vtiger_Field_Model::getInstance($entityFieldName, Vtiger_Module_Model::getInstance($seType));
                    $label = getTranslatedString($entityField->label, $seType);
                }

                $hardFormattedRecordStructure[$fieldName] = [$label, $recordStructure[$fieldName]];
                continue;
            }

            $processed[] = $fieldName;
            $structure[$fieldName] = [$label, $recordStructure[$fieldName]];
        }

        $sourceRecordModel = Vtiger_Record_Model::getInstanceById($request->get('source_record'), $request->get('source_module'));
        $currencyInfo = Vtiger_Functions::getCurrencyInfo($sourceRecordModel->get('currency_id'));

        $viewer->assign('MODULE_NAME', $moduleName);
        $viewer->assign('MODULE', $moduleName);
        $viewer->assign('RECORD', $request->get('record'));
        $viewer->assign('SOURCE_MODULE', $request->get('source_module'));
        $viewer->assign('SOURCE_RECORD', $request->get('source_record'));
        $viewer->assign('INVENTORY_ITEM_COLUMNS', $selectedFields);
        $viewer->assign('RECORD_STRUCTURE', $recordStructure);
        $viewer->assign('FORMATTED_RECORD_STRUCTURE', $structure);
        $viewer->assign('HARD_FORMATTED_RECORD_STRUCTURE', $hardFormattedRecordStructure);
        $viewer->assign('ITEM_TYPE', $request->get('item_type'));
        $viewer->assign('CURRENCY_NAME', $currencyInfo['currency_name']);
        $viewer->assign('CURRENCY_SYMBOL', $currencyInfo['currency_symbol']);
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
}