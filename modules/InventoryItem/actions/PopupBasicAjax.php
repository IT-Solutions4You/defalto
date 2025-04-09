<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class InventoryItem_PopupBasicAjax_Action extends Vtiger_BasicAjax_Action
{

    public function process(Vtiger_Request $request)
    {
        $searchValue = $request->get('search_value');
        $searchModule = $request->get('search_module');

        $parentRecordId = $request->get('parent_id');
        $parentModuleName = $request->get('parent_module');
        $relatedModule = $request->get('module');

        $searchModuleModel = Vtiger_Module_Model::getInstance($searchModule);
        $records = $searchModuleModel->searchRecord($searchValue, $parentRecordId, $parentModuleName, $relatedModule);
        $fields = $searchModuleModel->getFields();
        $noFieldName = $possibleNoFieldName = $priceFieldName = $possiblePriceFieldName = '';

        foreach ($fields as $fieldName => $fieldModel) {
            if ($fieldModel->get('uitype') === Vtiger_Field_Model::UITYPE_RECORD_NO) {
                $noFieldName = $fieldName;
            }

            if ($noFieldName == '' && $possibleNoFieldName == '' && str_ends_with($fieldName, '_no')) {
                $possibleNoFieldName = $fieldName;
            }

            if ($fieldName === 'unit_price') {
                $priceFieldName = $fieldName;
            }

            if ($priceFieldName == '' && $possiblePriceFieldName == '' && str_contains($fieldName, 'price')) {
                $possiblePriceFieldName = $fieldName;
            }
        }

        if ($noFieldName === '' && $possibleNoFieldName !== '') {
            $noFieldName = $possibleNoFieldName;
        }

        if ($priceFieldName === '' && $possiblePriceFieldName !== '') {
            $priceFieldName = $possiblePriceFieldName;
        }

        if (method_exists($searchModuleModel, 'searchRecordsOnSequenceNumber')) {
            foreach ($records as $moduleName => $recordModels) {
                foreach ($recordModels as $recordId => $recordModel) {
                    $records[$moduleName][$recordId] = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
                }
            }

            $sequenceBasedRecords = $searchModuleModel->searchRecordsOnSequenceNumber($searchValue, $relatedModule);

            if ($sequenceBasedRecords) {
                foreach ($sequenceBasedRecords as $recordId => $recordModel) {
                    $records[$searchModule][$recordId] = $recordModel;
                }
            }
        }

        $baseRecordId = $request->get('base_record');
        $result = [];

        foreach ($records as $recordModels) {
            foreach ($recordModels as $recordModel) {
                if ($recordModel->getId() != $baseRecordId) {
                    $recordLabel = '';

                    if ($noFieldName) {
                        $recordLabel = $recordModel->get($noFieldName) . ' - ';
                    }

                    $recordLabel .= decode_html($recordModel->getName());

                    if ($searchModule === 'Products' && $recordModel->get('productcode')) {
                        $recordLabel .= ' - ' . $recordModel->get('productcode');
                    }

                    if ($priceFieldName) {
                        $recordLabel .= ' (' . $recordModel->get($priceFieldName) . ')';
                    }

                    $result[] = ['label' => $recordLabel, 'value' => $recordLabel, 'id' => $recordModel->getId()];
                }
            }
        }

        $response = new Vtiger_Response();
        $response->setResult($result);
        $response->emit();
    }
}