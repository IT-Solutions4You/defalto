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

        // A repeated or canceled conversion must not reuse the values of the previous attempt.
        InventoryItem_Conversion_Model::clearStoredItems($targetModule, $sourceRecord);

        $sourceRecordModel = Vtiger_Record_Model::getInstanceById($sourceRecord, $sourceModule);
        $currencyInfo = Vtiger_Functions::getCurrencyInfo($sourceRecordModel->get('currency_id'));
        $items = array_filter(InventoryItem_Utils_Helper::fetchItems($sourceRecord), static function (array $item): bool {
            return 'Text' !== $item['entityType'];
        });

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
            ->assign('HEADER_FIELD_MODELS', $this->getHeaderFieldModels($conversion, $sourceRecordModel))
            ->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());

        Core_Modifiers_Model::modifyForClass(get_class($this), 'process', $request->getModule(), $viewer, $request);

        $viewer->view('ConversionModal.tpl', 'InventoryItem');
    }

    /**
     * Returns the fields of the target record which the user can change in the modal, filled in the same way as the create view of the target record fills them.
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

        if (empty($headerFieldNames)) {
            return [];
        }

        $targetRecordModel = Vtiger_Record_Model::getCleanInstance($conversion->get('target_module'));

        Core_Base_Mapping::getInstance($targetRecordModel, $sourceRecordModel)->mapFields();

        $structure = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($targetRecordModel, Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_EDIT)->getStructure();
        $fieldModels = [];

        foreach ($structure as $blockFields) {
            foreach ($blockFields as $fieldName => $fieldModel) {
                if (in_array($fieldName, $headerFieldNames, true) && InventoryItem_Conversion_Model::isSelectableHeaderField($fieldModel)) {
                    $fieldModels[$fieldName] = $fieldModel;
                }
            }
        }

        return $fieldModels;
    }
}
