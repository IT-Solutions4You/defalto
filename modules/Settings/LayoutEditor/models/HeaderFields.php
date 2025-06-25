<?php
/*+**********************************************************************************
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 ************************************************************************************/


class Settings_LayoutEditor_HeaderFields_Model extends Vtiger_Field_Model {

    protected PearDatabase $db;

    protected function db()
    {
        if (!isset($this->db)) {
            $this->db = PearDatabase::getInstance();
        }

        return $this->db;
    }

    public function saveHeaderFields($moduleName, $headerFields)
    {
        $this->db()->pquery('UPDATE vtiger_field SET headerfieldsequence=NULL WHERE tabid=?', [getTabid($moduleName)]);

        foreach ($headerFields as $key => $fieldName) {
            $this->db()->pquery('UPDATE vtiger_field SET headerfieldsequence=? WHERE tabid=? AND fieldname=?', [
                $key + 1,
                getTabid($moduleName),
                $fieldName
            ]);
        }
    }

    public function getHeaderFields($moduleName)
    {
        return $this->db()->run_query_allrecords(sprintf(
            'SELECT fieldname, headerfieldsequence, fieldlabel 
                FROM vtiger_field 
                WHERE tabid="%s" 
                AND headerfieldsequence IS NOT NULL
                ORDER BY headerfieldsequence ASC
            ',
            getTabid($moduleName)
        ));
    }

    public function getHeaderFieldNames($moduleName): array
    {
        $fieldNames = [];
        $fieldNameRows = $this->db()->run_query_allrecords(sprintf(
            'SELECT fieldname 
                FROM vtiger_field 
                WHERE tabid="%s" 
                AND headerfieldsequence IS NOT NULL
                ORDER BY headerfieldsequence ASC
            ',
            getTabid($moduleName)
        ));

        foreach ($fieldNameRows as $fieldNameRow) {
            $fieldNames[] = $fieldNameRow['fieldname'];
        }

        return $fieldNames;
    }

    public static function getFieldModules($field)
    {
        if ('owner' === $field->getFieldDataType()) {
            $modules = ['Users'];
        } else {
            $modules = $field->getReferenceList();
        }

        return $modules;
    }

    public function getModuleOptions($moduleName): array
    {
        $options = [
            '' => vtranslate($moduleName, $moduleName),
        ];

        if (empty($moduleName)) {
            return $options;
        }

        $module = Vtiger_Module_Model::getInstance($moduleName);
        $fields = $module->getFieldsByType(['reference', 'owner']);

        /**
         * @var Vtiger_Field_Model $field
         * @var Vtiger_Field_Model $referenceField
         */
        foreach ($fields as $field) {
            $modules = self::getFieldModules($field);

            foreach ($modules as $module) {
                $options[sprintf('%s:%s', $field->get('name'), $module)] = sprintf('%s (%s)', vtranslate($field->get('label'), $moduleName), vtranslate($module, $module));
            }
        }

        return $options;
    }

    public function getFieldOptions($moduleName): array
    {
        $options = [];

        if (empty($moduleName)) {
            return $options;
        }

        $module = Vtiger_Module_Model::getInstance($moduleName);
        $fields = $module->getFields();

        /**
         * @var Vtiger_Field_Model $field
         * @var Vtiger_Field_Model $referenceField
         */
        foreach ($fields as $field) {
            $options['default'][$field->get('name')] = vtranslate($field->block->label, $field->getModuleName()) . '##' . vtranslate($field->get('label'), $field->getModuleName());
        }

        $fields = $module->getFieldsByType(['reference', 'owner']);

        foreach ($fields as $field) {
            $fieldName = $field->get('name');
            $fieldLabel = $field->get('label');
            $referenceModuleNames = self::getFieldModules($field);

            foreach ($referenceModuleNames as $referenceModuleName) {
                $reference = Vtiger_Module_Model::getInstance($referenceModuleName);
                $referenceFields = $reference->getFields();

                foreach ($referenceFields as $referenceField) {
                    $referenceFieldName = $referenceField->get('name');
                    $referenceFieldLabel = $referenceField->get('label');

                    $options[implode(':', [$fieldName, $referenceModuleName])][implode(':', [$fieldName, $referenceModuleName, $referenceFieldName])] = vtranslate($referenceField->block->label, $referenceField->getModuleName()) . '##' . vtranslate($referenceFieldLabel, $referenceModuleName);
                }
            }
        }

        return $options;
    }

    public function getLabelOptions(string $moduleName, array $labels): array
    {
        $options = [];

        if (empty($moduleName)) {
            return $options;
        }

        $module = Vtiger_Module_Model::getInstance($moduleName);
        $fields = $module->getFields();

        /**
         * @var Vtiger_Field_Model $field
         * @var Vtiger_Field_Model $referenceField
         */
        foreach ($fields as $field) {
            $options[$field->get('name')] = vtranslate($field->get('label'), $field->getModuleName());
        }

        $fields = $module->getFieldsByType(['reference', 'owner']);

        foreach ($fields as $field) {
            $fieldName = $field->get('name');
            $fieldLabel = $field->get('label');
            $referenceModuleNames = self::getFieldModules($field);

            foreach ($referenceModuleNames as $referenceModuleName) {
                $reference = Vtiger_Module_Model::getInstance($referenceModuleName);
                $referenceFields = $reference->getFields();

                foreach ($referenceFields as $referenceField) {
                    $referenceFieldName = $referenceField->get('name');
                    $referenceFieldLabel = $referenceField->get('label');

                    $options[implode(':', [$fieldName, $referenceModuleName, $referenceFieldName])] = vtranslate($fieldLabel, $moduleName) . ' - ' . vtranslate($referenceFieldLabel, $referenceModuleName);
                }
            }
        }

        return $options;
    }

}