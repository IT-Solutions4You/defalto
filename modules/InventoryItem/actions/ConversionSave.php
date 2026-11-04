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
 * Creates the target record of a conversion directly from the item review modal, without opening the edit view of the target record.
 */
class InventoryItem_ConversionSave_Action extends Vtiger_Save_Action
{
    /**
     * @inheritDoc
     */
    public function requiresPermission(Vtiger_Request $request): array
    {
        return [
            ['module_parameter' => 'sourceModule', 'action' => 'DetailView', 'record_parameter' => 'sourceRecord'],
            ['module_parameter' => 'targetModule', 'action' => 'CreateView'],
        ];
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

        InventoryItem_Conversion_Model::storeItems($targetModule, $sourceRecord, $conversion->validateItems($sourceRecord, (array)$request->get('items', [])));

        try {
            $recordModel = $this->createTargetRecord($request, $conversion);
        } finally {
            // The reviewed items are valid for one save only.
            InventoryItem_Conversion_Model::clearStoredItems($targetModule, $sourceRecord);
        }

        $response = new Vtiger_Response();
        $response->setResult(['id' => $recordModel->getId(), 'url' => $recordModel->getFullDetailViewUrl()]);
        $response->emit();
    }

    /**
     * Saves the target record. Items are copied by the save handlers of the target module, which read the reviewed items and the source record.
     *
     * @param Vtiger_Request                 $request
     * @param InventoryItem_Conversion_Model $conversion
     *
     * @return Vtiger_Record_Model
     * @throws Exception
     */
    protected function createTargetRecord(Vtiger_Request $request, InventoryItem_Conversion_Model $conversion): Vtiger_Record_Model
    {
        $targetModule = (string)$conversion->get('target_module');
        $recordModel = Vtiger_Record_Model::getCleanInstance($targetModule);
        $recordModel->set('mode', '');

        Core_Base_Mapping::getInstance($recordModel, Vtiger_Record_Model::getInstanceById((int)$request->get('sourceRecord'), (string)$conversion->get('source_module')))->mapFields();

        $this->setDefaultValues($recordModel);
        $this->setPostedValues($request, $recordModel);
        $this->validateMandatoryValues($recordModel);

        $recordModel->save();

        return $recordModel;
    }

    /**
     * The edit view takes the default values from the field templates, so the values which are not mapped have to be filled here.
     *
     * @param Vtiger_Record_Model $recordModel
     *
     * @return void
     */
    protected function setDefaultValues(Vtiger_Record_Model $recordModel): void
    {
        foreach ($recordModel->getModule()->getFields() as $fieldName => $fieldModel) {
            if (!$fieldModel->isEditable() || !self::isEmptyValue($recordModel->get($fieldName))) {
                continue;
            }

            if ('assigned_user_id' === $fieldName) {
                $recordModel->set($fieldName, Users_Record_Model::getCurrentUserModel()->getId());
            } elseif ($fieldModel->hasDefaultValue()) {
                $recordModel->set($fieldName, $fieldModel->getDefaultFieldValue());
            }
        }
    }

    /**
     * Applies the fields which were displayed in the modal.
     *
     * @param Vtiger_Request      $request
     * @param Vtiger_Record_Model $recordModel
     *
     * @return void
     */
    protected function setPostedValues(Vtiger_Request $request, Vtiger_Record_Model $recordModel): void
    {
        foreach ($recordModel->getModule()->getFields() as $fieldName => $fieldModel) {
            if (!$request->has($fieldName) || !$fieldModel->isEditable() || InventoryItem_Conversion_Model::isCalculatedHeaderField($fieldName)) {
                continue;
            }

            $fieldValue = $fieldModel->getUITypeModel()->getRequestValue($request->get($fieldName, null));

            if (null !== $fieldValue) {
                $recordModel->set($fieldName, $fieldValue);
            }
        }
    }

    /**
     * The modal validates the mandatory fields in the browser, this is the check which cannot be bypassed.
     *
     * @param Vtiger_Record_Model $recordModel
     *
     * @return void
     * @throws Exception
     */
    protected function validateMandatoryValues(Vtiger_Record_Model $recordModel): void
    {
        foreach ($recordModel->getModule()->getFields() as $fieldName => $fieldModel) {
            if (InventoryItem_Conversion_Model::isMandatoryModalField($fieldModel) && self::isEmptyValue($recordModel->get($fieldName))) {
                throw new Exception(vtranslate($fieldModel->get('label'), $recordModel->getModuleName()) . ': ' . vtranslate('LBL_MANDATORY_FIELDS_ARE_EMPTY', 'Core'));
            }
        }
    }

    /**
     * @param mixed $value
     *
     * @return bool
     */
    protected static function isEmptyValue(mixed $value): bool
    {
        return null === $value || '' === $value || [] === $value;
    }
}
