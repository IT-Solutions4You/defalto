<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Core_Base_Mapping extends Vtiger_Base_Model
{
    /**
     * @var array custom mapping can be set in child classes. The structure has to be $targetFieldName => $sourceFieldName.
     * Example for Accounts_Contacts_Mapping, which means we are mapping Contact onto Account:
     * public static array $mapping = ['accountname' => 'last_name']
     */
    public static array $mapping = [];
    public static array $excludedFields = ['record_id', 'record_module', 'id'];
    protected Vtiger_Record_Model $recordModel;
    protected Vtiger_Record_Model $sourceRecordModel;

    /**
     * @param Vtiger_Record_Model $recordModel
     * @param Vtiger_Record_Model $sourceRecordModel
     *
     * @return self
     */
    public static function getInstance(Vtiger_Record_Model $recordModel, Vtiger_Record_Model $sourceRecordModel)
    {
        $moduleName = $recordModel->getModuleName();
        $sourceModuleName = $sourceRecordModel->getModuleName();

        try {
            $moduleSpecificFilePath = Vtiger_Loader::getComponentClassName('Mapping', $sourceModuleName, $moduleName);
            $instance = new $moduleSpecificFilePath();
        } catch (Exception $e) {
            $instance = new self();
        }

        $instance->recordModel = $recordModel;
        $instance->sourceRecordModel = $sourceRecordModel;

        return $instance;
    }

    /**
     * Executes mapping in four steps:
     * 1. Set all fields with the same name in both modules
     * 2. Try to find the relational field based on vtiger_fieldmodulerel table (uitype 10)
     * 3. Set fields based on the static::$mapping array
     * 4. Set fields based on the mapping configured by an administrator for the conversion, which also decides about the fields it excludes from copying
     * In all cases checks the static::$excludedFields
     *
     * @return void
     */
    public function mapFields()
    {
        $entity = $this->sourceRecordModel->getEntity();
        $fieldNames = $entity->column_fields->getColumnFieldNames();
        $targetFieldNames = $this->recordModel->getEntity()->column_fields->getColumnFieldNames();
        $configuredMapping = $this->getConfiguredMapping();

        foreach ($fieldNames as $fieldName) {
            if (in_array($fieldName, static::$excludedFields) || !in_array($fieldName, $targetFieldNames) || isset($configuredMapping[$fieldName])) {
                continue;
            }

            $this->recordModel->set($fieldName, $this->sourceRecordModel->get($fieldName));
        }

        $db = PearDatabase::getInstance();
        $sql = 'SELECT fieldname FROM vtiger_field WHERE fieldid = (SELECT fieldid FROM vtiger_fieldmodulerel WHERE module = ? AND relmodule = ?)';
        $res = $db->pquery($sql, [$this->recordModel->getModuleName(), $this->sourceRecordModel->getModuleName()]);

        if ($db->num_rows($res)) {
            $row = $db->fetchByAssoc($res);

            if (!in_array($row['fieldname'], static::$excludedFields) && !isset($configuredMapping[$row['fieldname']])) {
                $this->recordModel->set($row['fieldname'], $this->sourceRecordModel->getId());
            }
        }

        foreach (static::$mapping as $targetFieldName => $sourceFieldName) {
            if (in_array($targetFieldName, static::$excludedFields) || isset($configuredMapping[$targetFieldName])) {
                continue;
            }

            $this->recordModel->set($targetFieldName, $this->sourceRecordModel->get($sourceFieldName));
        }

        foreach ($configuredMapping as $targetFieldName => $sourceFieldName) {
            if ('' === $sourceFieldName || in_array($targetFieldName, static::$excludedFields)) {
                continue;
            }

            $this->recordModel->set($targetFieldName, $this->sourceRecordModel->get($sourceFieldName));
        }
    }

    /**
     * Returns the mapping configured for the conversion of the source record to the target record.
     *
     * @return array [targetFieldName => sourceFieldName], an empty sourceFieldName means that the target field is not copied
     * @throws Exception
     */
    protected function getConfiguredMapping(): array
    {
        return InventoryItem_ConversionMapping_Model::getMapping($this->sourceRecordModel->getModuleName(), $this->recordModel->getModuleName());
    }
}
