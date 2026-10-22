<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

/**
 * Lets the user review the items of the source record before the target record is created.
 */
class InventoryItem_ConversionModal_View extends Vtiger_IndexAjax_View
{
    /**
     * @inheritDoc
     */
    public function requiresPermission(Vtiger_Request $request): array
    {
        return [['module_parameter' => 'sourceModule', 'action' => 'DetailView', 'record_parameter' => 'sourceRecord']];
    }

    /**
     * @inheritDoc
     */
    public function process(Vtiger_Request $request)
    {
        $sourceModule = (string)$request->get('sourceModule');
        $targetModule = (string)$request->get('targetModule');
        $sourceRecord = (int)$request->get('sourceRecord');
        $conversion = InventoryItem_Conversion_Model::getConversion($sourceModule, $targetModule);

        if (empty($conversion)) {
            throw new Exception(vtranslate('LBL_PERMISSION_DENIED'));
        }

        $conversion->checkAccess($sourceRecord);

        $sourceRecordModel = Vtiger_Record_Model::getInstanceById($sourceRecord, $sourceModule);
        $currencyInfo = Vtiger_Functions::getCurrencyInfo($sourceRecordModel->get('currency_id'));
        $decimals = InventoryItem_Utils_Helper::fetchDecimals();
        $items = [];

        foreach (InventoryItem_Utils_Helper::fetchItems($sourceRecord) as $item) {
            if ('Text' === $item['entityType']) {
                continue;
            }

            // The same format as the item edit form uses: the decimals of the settings and the dot as a decimal separator.
            $item['quantity'] = number_format((float)$item['quantity'], $decimals['quantity'] ?? 2, '.', '');
            $item['price'] = number_format((float)$item['price'], $decimals['price'] ?? 2, '.', '');
            $items[] = $item;
        }

        $fieldModels = $this->getHeaderFieldModels($conversion, $sourceRecordModel);
        $fieldsInfo = [];

        foreach ($fieldModels as $fieldName => $fieldModel) {
            $fieldsInfo[$fieldName] = $fieldModel->getFieldInfo();
        }

        $viewer = $this->getViewer($request);
        $viewer->assign('MODULE_NAME', 'InventoryItem')
            ->assign('MODULE', 'InventoryItem')
            ->assign('SOURCE_MODULE', $sourceModule)
            ->assign('TARGET_MODULE', $targetModule)
            ->assign('SOURCE_RECORD', $sourceRecord)
            ->assign('SOURCE_RECORD_NAME', $sourceRecordModel->getName())
            ->assign('CURRENCY_NAME', $currencyInfo['currency_name'])
            ->assign('CURRENCY_SYMBOL', $currencyInfo['currency_symbol'])
            ->assign('EDITABLE_COLUMNS', array_intersect(InventoryItem_Conversion_Model::SUPPORTED_COLUMNS, $conversion->getEditableColumns()))
            ->assign('INVENTORY_ITEMS', $items)
            ->assign('HEADER_FIELD_MODELS', $fieldModels)
            ->assign('FIELDS_INFO', json_encode($fieldsInfo))
            ->assign('SCRIPTS', $this->getHeaderScripts($request))
            ->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());

        Core_Modifiers_Model::modifyForClass(get_class($this), 'process', $request->getModule(), $viewer, $request);

        $viewer->view('ConversionModal.tpl', 'InventoryItem');
    }

    /**
     * Returns the fields of the target record which the user can change in the modal, filled in the same way as the create view of the target record fills them.
     * These are the fields selected in the settings and all mandatory fields.
     *
     * @param InventoryItem_Conversion_Model $conversion
     * @param Vtiger_Record_Model            $sourceRecordModel
     *
     * @return Vtiger_Field_Model[]
     * @throws Exception
     */
    protected function getHeaderFieldModels(InventoryItem_Conversion_Model $conversion, Vtiger_Record_Model $sourceRecordModel): array
    {
        $headerFieldNames = $conversion->getHeaderFields();
        $targetRecordModel = Vtiger_Record_Model::getCleanInstance($conversion->get('target_module'));

        Core_Base_Mapping::getInstance($targetRecordModel, $sourceRecordModel)->mapFields();

        $structure = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($targetRecordModel, Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_EDIT)->getStructure();
        $fieldModels = [];

        foreach ($structure as $blockFields) {
            foreach ($blockFields as $fieldName => $fieldModel) {
                $isSelected = in_array($fieldName, $headerFieldNames, true) && InventoryItem_Conversion_Model::isSelectableHeaderField($fieldModel);

                if ($isSelected || InventoryItem_Conversion_Model::isMandatoryModalField($fieldModel)) {
                    $fieldModels[$fieldName] = $fieldModel;
                }
            }
        }

        return $fieldModels;
    }

    /**
     * The generic edit script registers the reference, owner and picklist events of the fields. The module edit scripts handle the line items and must not run in the modal.
     *
     * @inheritDoc
     */
    public function getHeaderScripts(Vtiger_Request $request): array
    {
        $jsFileNames = ['modules.Vtiger.resources.Edit'];

        return array_merge($this->checkAndConvertJsScripts($jsFileNames), $this->getBlockUiTypeScripts((string)$request->get('targetModule')));
    }
}
