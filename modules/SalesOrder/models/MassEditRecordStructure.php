<?php
/*************************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * Modifications and additions by IT-Solutions4You (ITS4YOU) are Copyright (c) IT-Solutions4You s.r.o.
 *
 * These contributions are licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
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