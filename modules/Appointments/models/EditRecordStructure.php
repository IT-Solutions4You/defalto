<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Appointments_EditRecordStructure_Model extends Vtiger_EditRecordStructure_Model
{
    /**
     * @return array
     */
    public function getStructure(): array
    {
        $structure = parent::getStructure();

        if (!$this->getRecord()->isAllDayEvent()) {
            return $structure;
        }

        $recordModel = $this->getRecord();

        foreach ($structure as $fields) {
            foreach ($fields as $fieldModel) {
                if ('datetime' === $fieldModel->getFieldDataType()) {
                    $fieldName = $fieldModel->getName();
                    $fieldModel->set('is_all_day', true);

                    if ($recordModel->getId()) {
                        $fieldModel->set('all_day_date', $recordModel->getAllDayDate($fieldName));
                    }
                }
            }
        }

        return $structure;
    }
}
