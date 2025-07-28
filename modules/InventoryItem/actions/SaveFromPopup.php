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

class InventoryItem_SaveFromPopup_Action extends Vtiger_SaveAjax_Action
{
    /**
     * @inheritDoc
     */
    public function process(Vtiger_Request $request)
    {
        $response = new Vtiger_Response();

        try {
            $moduleName = 'InventoryItem';

            if ($request->has('insert_after_sequence') && !empty($request->get('insert_after_sequence'))) {
                $allItems = InventoryItem_Module_Model::fetchItemsForId((int)$request->get('source_record'), true);

                foreach ($allItems as $item) {
                    $itemModel = Vtiger_Record_Model::getInstanceById($item['inventoryitemid'], $moduleName);

                    if ($itemModel->get('sequence') > $request->get('insert_after_sequence')) {
                        $itemModel->set('sequence', $itemModel->get('sequence') + 1);
                        $itemModel->set('mode', 'edit');
                        $itemModel->save();
                    }

                    unset($itemModel);
                }

                $request->set('sequence', (int)$request->get('insert_after_sequence') + 1);
            }

            $itemModel = Vtiger_Record_Model::getCleanInstance($moduleName);

            if ($request->get('record')) {
                $itemModel = Vtiger_Record_Model::getInstanceById($request->get('record'), $moduleName);
                $itemModel->set('mode', 'edit');
            }

            $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
            $fieldModelList = $moduleModel->getFields();

            foreach ($fieldModelList as $fieldName => $fieldModel) {
                $fieldValue = $request->get($fieldName, null);
                $fieldValue = $fieldModel->getUITypeModel()->getRequestValue($fieldValue);

                if (null !== $fieldValue) {
                    $itemModel->set($fieldName, $fieldValue);
                }
            }

            $itemModel->set('parentid', $request->get('source_record'));
            $itemModel->set('sequence', $this->decideSequence((int)$request->get('sequence'), (int)$request->get('source_record')));
            $itemModel->save();
            $itemModel->saveTaxId((int)$request->get('taxid'));

            InventoryItem_ParentEntity_Model::updateTotals((int)$request->get('source_record'));

            $entity = $itemModel->getEntity();
            $response->setResult($entity->column_fields->getColumnFields());
        } catch (Exception $e) {
            $response->setError($e->getMessage());
        }

        $response->emit();
    }

    /**
     * If the record comes with sequence, return this sequence
     * If there are some lines in the parent record, get the max sequence + 1 and return it
     * Otherwise return 1 - supposing it is the newly created first line
     *
     * @param int $sequence
     * @param int $parentId
     *
     * @return int
     */
    protected function decideSequence(int $sequence, int $parentId): int
    {
        if ($sequence > 0) {
            return $sequence;
        }

        $db = PearDatabase::getInstance();
        $sql = 'SELECT COALESCE(MAX(sequence)+1, 1) AS new_sequence 
                FROM df_inventoryitem
                INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = df_inventoryitem.inventoryitemid AND vtiger_crmentity.deleted = 0
                WHERE parentid = ?';
        $res = $db->pquery($sql, [$parentId]);

        if ($db->num_rows($res)) {
            $row = $db->fetchByAssoc($res);

            return $row['new_sequence'];
        }

        return 1;
    }
}