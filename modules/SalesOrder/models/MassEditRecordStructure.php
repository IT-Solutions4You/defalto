<?php
/**
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (c) vtiger.
 * Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
 * All Rights Reserved.
 */

/**
 * Mass Edit Record Structure Model
 */
class SalesOrder_MassEditRecordStructure_Model extends Vtiger_MassEditRecordStructure_Model
{

    public function getStructure()
    {
        if (!empty($this->structuredValues)) {
            return $this->structuredValues;
        }

        $values = [];
        $recordModel = $this->getRecord();
        $recordExists = !empty($recordModel);
        $moduleModel = $this->getModule();
        $blockModelList = $moduleModel->getBlocks();

        foreach ($blockModelList as $blockLabel => $blockModel) {
            if ($blockLabel === 'Recurring Invoice Information') {
                continue;
            }

            $fieldModelList = $blockModel->getFields();

            if (!empty ($fieldModelList)) {
                $values[$blockLabel] = [];

                foreach ($fieldModelList as $fieldName => $fieldModel) {
                    if ($fieldModel->isEditable() && $fieldModel->isMassEditable()) {
                        if ($fieldModel->isViewable() && $this->isFieldRestricted($fieldModel)) {
                            if ($recordExists) {
                                $fieldModel->set('fieldvalue', $recordModel->get($fieldName));
                            }

                            $values[$blockLabel][$fieldName] = $fieldModel;
                        }
                    }
                }
            }
        }

        $this->structuredValues = $values;

        return $values;
    }
}
