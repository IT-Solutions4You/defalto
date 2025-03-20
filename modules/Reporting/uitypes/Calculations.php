<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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