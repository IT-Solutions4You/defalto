<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Settings_LayoutEditor_HeaderFields_Model extends Vtiger_Field_Model
{
    /**
     * @var PearDatabase
     */
    protected PearDatabase $db;

    /**
     * @return PearDatabase
     */
    protected function db()
    {
        if (!isset($this->db)) {
            $this->db = PearDatabase::getInstance();
        }

        return $this->db;
    }

    /**
     * @throws Exception
     */
    public function saveHeaderFields($moduleName, $headerFields): void
    {
        $table = (new Vtiger_Field_Model())->getFieldTable();
        $table->updateData(['headerfieldsequence' => null, 'headerfield' => null,], ['tabid' => getTabid($moduleName)]);

        foreach ($headerFields as $key => $fieldName) {
            $table->updateData(['headerfieldsequence' => $key + 1, 'headerfield' => 1,], ['tabid' => getTabid($moduleName), 'fieldname' => $fieldName]);
        }
    }

    /**
     * @param $moduleName
     *
     * @return array
     */
    public function getHeaderFields($moduleName): array
    {
        return $this->db()->run_query_allrecords(
            sprintf(
                'SELECT fieldname, headerfieldsequence, fieldlabel 
                FROM vtiger_field 
                WHERE tabid="%s" AND headerfield=1 
                ORDER BY headerfieldsequence ASC',
                getTabid($moduleName),
            ),
        );
    }

    /**
     * @param $field
     *
     * @return string[]
     */
    public static function getFieldModules($field)
    {
        if ('owner' === $field->getFieldDataType()) {
            $modules = ['Users'];
        } else {
            $modules = $field->getReferenceList();
        }

        return $modules;
    }

    /**
     * @param $moduleName
     *
     * @return array
     * @throws Exception
     */
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

                    $options[implode(':', [$fieldName, $referenceModuleName])][implode(':', [$fieldName, $referenceModuleName, $referenceFieldName])] = vtranslate(
                            $referenceField->block->label,
                            $referenceField->getModuleName()
                        ) . '##' . vtranslate($referenceFieldLabel, $referenceModuleName);
                }
            }
        }

        return $options;
    }

    /**
     * @param string $moduleName
     * @param array  $labels
     *
     * @return array
     * @throws Exception
     */
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

                    $options[implode(':', [$fieldName, $referenceModuleName, $referenceFieldName])] = vtranslate($fieldLabel, $moduleName) . ' - ' . vtranslate(
                            $referenceFieldLabel,
                            $referenceModuleName
                        );
                }
            }
        }

        return $options;
    }
}