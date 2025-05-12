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

        if (!empty($searchValue)) {
            $records = $searchModuleModel->searchRecord($searchValue, $parentRecordId, $parentModuleName, $relatedModule);
        } else {
            $records = $this->searchRecord($searchModule, $searchValue, $parentRecordId, $parentModuleName, $relatedModule);
        }

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

        if (method_exists($searchModuleModel, 'searchRecordsOnNumber')) {
            foreach ($records as $moduleName => $recordModels) {
                foreach ($recordModels as $recordId => $recordModel) {
                    $records[$moduleName][$recordId] = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
                }
            }

            $sequenceBasedRecords = $searchModuleModel->searchRecordsOnNumber($searchValue, $relatedModule);

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

    protected function searchRecord($searchModule, $searchValue, $parentRecordId, $parentModuleName, $relatedModule)
    {
        $db = PearDatabase::getInstance();
        $matchingRecords = [];

        $query = 'SELECT label, crmid, setype, createdtime 
                FROM vtiger_crmentity 
                WHERE vtiger_crmentity.deleted = 0
                    AND setype = ?
                ORDER BY label
                LIMIT 0, 20';
        $result = $db->pquery($query, [$searchModule]);

        while ($row = $db->fetchByAssoc($result)) {
            if (Users_Privileges_Model::isPermitted($row['setype'], 'DetailView', $row['crmid'])) {
                $matchingRecords[$searchModule][$row['crmid']] = Vtiger_Record_Model::getInstanceById($row['crmid'], $searchModule);
            }
        }

        return $matchingRecords;
    }
}