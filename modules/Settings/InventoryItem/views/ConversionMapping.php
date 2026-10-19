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
 * Modal with the field mapping of one conversion.
 */
class Settings_InventoryItem_ConversionMapping_View extends Settings_Vtiger_IndexAjax_View
{
    /**
     * @inheritDoc
     */
    public function process(Vtiger_Request $request)
    {
        $conversion = InventoryItem_Conversion_Model::getConversionById((int)$request->get('conversion'));

        if (empty($conversion)) {
            throw new Exception(vtranslate('LBL_RECORD_NOT_FOUND'));
        }

        $sourceModule = $conversion->get('source_module');
        $targetModule = $conversion->get('target_module');
        $sourceFieldOptions = [];
        $targetFieldOptions = [];

        foreach (InventoryItem_ConversionMapping_Model::getSourceFields($sourceModule) as $fieldName => $fieldModel) {
            $sourceFieldOptions[$fieldName] = vtranslate($fieldModel->get('label'), $sourceModule);
        }

        foreach (InventoryItem_ConversionMapping_Model::getTargetFields($targetModule) as $fieldName => $fieldModel) {
            $targetFieldOptions[$fieldName] = vtranslate($fieldModel->get('label'), $targetModule);
        }

        $viewer = $this->getViewer($request);
        $viewer->assign('QUALIFIED_MODULE', $request->getModule(false))
            ->assign('CONVERSION_ID', $conversion->getId())
            ->assign('SOURCE_LABEL', vtranslate($sourceModule, $sourceModule))
            ->assign('TARGET_LABEL', vtranslate($targetModule, $targetModule))
            ->assign('SOURCE_FIELD_OPTIONS', $sourceFieldOptions)
            ->assign('TARGET_FIELD_OPTIONS', $targetFieldOptions)
            ->assign('MAPPING', InventoryItem_ConversionMapping_Model::getConversionMapping($conversion->getId()));

        Core_Modifiers_Model::modifyForClass(get_class($this), 'process', $request->getModule(), $viewer, $request);

        $viewer->view('ConversionMapping.tpl', $request->getModule(false));
    }
}
