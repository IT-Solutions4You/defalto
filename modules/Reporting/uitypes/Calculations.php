<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Reporting_Calculations_UIType extends Core_Data_UIType
{
    public function getNumberFields($moduleName): array
    {
        $options = [];

        if (empty($moduleName)) {
            return $options;
        }

        $fieldTypes = ['double', 'currency', 'percentage', 'integer'];
        $module = Vtiger_Module_Model::getInstance($moduleName);
        $fields = $module->getFieldsByType($fieldTypes);

        /**
         * @var Vtiger_Field_Model $field
         */
        foreach ($fields as $field) {
            $options[] = $field->get('name');
        }

        $fields = $module->getFieldsByType('reference');

        foreach ($fields as $field) {
            foreach ($field->getReferenceList() as $referenceModule) {
                $referenceModule = Vtiger_Module_Model::getInstance($referenceModule);
                $referenceFields = $referenceModule->getFieldsByType($fieldTypes);

                foreach ($referenceFields as $referenceField) {
                    $options[] = implode(':', [$field->get('name'), $referenceModule->getName(), $referenceField->getName()]);
                }
            }
        }

        return $options;
    }
}