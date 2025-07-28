<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o <info@its4you.sk>
 *
 * This file is licensed under the GNU AGPL v3 License.
 * For the full copyright and license information, please view the LICENSE-AGPLv3.txt
 * file that was distributed with this source code.
 */

class InventoryItem_PopupBasicAjax_Action extends Vtiger_BasicAjax_Action
{
    /**
     * @inheritDoc
     */
    public function process(Vtiger_Request $request)
    {
        $searchValue = $request->get('search_value');
        $searchModule = $request->get('search_module');

        $parentRecordId = $request->get('parent_id');
        $parentModuleName = $request->get('parent_module');
        $relatedModule = $request->get('module');

        $baseRecordId = $request->get('base_record');
        $baseRecordModel = Vtiger_Record_Model::getInstanceById($baseRecordId);
        $currencyInfo = getCurrencySymbolandCRate($baseRecordModel->get('currency_id'));
        $currencySymbol = $currencyInfo['symbol'];

        $searchModuleModel = Vtiger_Module_Model::getInstance($searchModule);

        if (!empty($searchValue)) {
            $records = $searchModuleModel->searchRecord($searchValue, $parentRecordId, $parentModuleName, $relatedModule);
        } else {
            $records = $this->searchRecord($searchModule);
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
                    if ($recordId) {
                        $records[$searchModule][$recordId] = $recordModel;
                    }
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

                    $recordLabel .= '<b>' . decode_html($recordModel->getName()) . '</b>';

                    if ($searchModule === 'Products' && $recordModel->get('productcode')) {
                        $recordLabel .= ' - ' . $recordModel->get('productcode');
                    }

                    if ($priceFieldName) {
                        $priceData = InventoryItem_Utils_Helper::decideItemPriceAndPriceBook(
                            (int)$recordModel->getId(),
                            (int)$baseRecordModel->get('currency_id'),
                            (int)$baseRecordModel->get('pricebookid')
                        );
                        $recordLabel .= ' (' . $priceData['price'] . ' ' . $currencySymbol . ')';
                    }

                    if (method_exists($recordModel, 'isBundle') && $recordModel->isBundle()) {
                        $recordLabel .= ' - <i>' . vtranslate('LBL_PRODUCT_BUNDLE') . '</i>';
                    }

                    $result[] = ['label' => $recordLabel, 'value' => $recordLabel, 'id' => $recordModel->getId()];
                }
            }
        }

        $response = new Vtiger_Response();
        $response->setResult($result);
        $response->emit();
    }

    /**
     * @param string $searchModule
     *
     * @return array
     */
    protected function searchRecord(string $searchModule)
    {
        $db = PearDatabase::getInstance();
        $matchingRecords = [];

        $query = 'SELECT DISTINCT crmid, setype, createdtime 
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