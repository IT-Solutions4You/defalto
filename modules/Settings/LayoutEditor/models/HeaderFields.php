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
        $headerFields = $this->filterModuleFieldNames($moduleName, (array)$headerFields);
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
         */
        foreach ($fields as $field) {
            $options['default'][$field->get('name')] = vtranslate($field->block->label, $field->getModuleName()) . '##' . vtranslate($field->get('label'), $field->getModuleName());
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
         */
        foreach ($fields as $field) {
            $options[$field->get('name')] = vtranslate($field->get('label'), $field->getModuleName());
        }

        return $options;
    }

    /**
     * @param string $moduleName
     * @param array  $fieldNames
     *
     * @return array
     * @throws Exception
     */
    public function filterModuleFieldNames(string $moduleName, array $fieldNames): array
    {
        if (empty($moduleName) || empty($fieldNames)) {
            return [];
        }

        $module = Vtiger_Module_Model::getInstance($moduleName);
        $moduleFieldNames = array_keys($module->getFields());

        return array_values(array_intersect($fieldNames, $moduleFieldNames));
    }
}
