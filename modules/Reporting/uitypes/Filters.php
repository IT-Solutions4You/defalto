<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Reporting_Filters_UIType extends Core_Data_UIType
{
    public object $moduleModel;

    /**
     * @param string $moduleName
     *
     * @return array
     */
    public function getRecordStructure(string $moduleName): array
    {
        $moduleModel = $this->getModuleModel($moduleName);
        $recordStructure = Vtiger_RecordStructure_Model::getInstanceForModule($moduleModel, Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_FILTER);

        return $recordStructure->getStructure();
    }

    public function getModuleModel(string $moduleName): object
    {
        if (empty($this->moduleModel)) {
            $this->moduleModel = Vtiger_Module_Model::getInstance($moduleName);
        }

        return $this->moduleModel;
    }

    /**
     * @param string|null $value
     *
     * @return array
     */
    public function getAdvanceCriteria(string|null $value): array
    {
        $value = (string)$value;

        if (empty($value)) {
            return [
                1 => [
                    'columns' => [],
                ],
                2 => [
                    'columns' => [],
                ],
            ];
        }

        return json_decode(decode_html($value), true);
    }
}