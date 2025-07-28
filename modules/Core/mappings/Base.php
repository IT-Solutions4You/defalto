<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o <info@its4you.sk>
 *
 * This file is licensed under the GNU AGPL v3 License.
 * For the full copyright and license information, please view the LICENSE-AGPLv3.txt
 * file that was distributed with this source code.
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
     * Executes mapping in three steps:
     * 1. Set all fields with the same name in both modules
     * 2. Try to find the relational field based on vtiger_fieldmodulerel table (uitype 10)
     * 3. Set fields based on the static::$mapping array
     * In all cases checks the static::$excludedFields
     *
     * @return void
     */
    public function mapFields()
    {
        $entity = $this->sourceRecordModel->getEntity();
        $fieldNames = $entity->column_fields->getColumnFieldNames();
        $targetFieldNames = $this->recordModel->getEntity()->column_fields->getColumnFieldNames();

        foreach ($fieldNames as $fieldName) {
            if (in_array($fieldName, static::$excludedFields) || !in_array($fieldName, $targetFieldNames)) {
                continue;
            }

            $this->recordModel->set($fieldName, $this->sourceRecordModel->get($fieldName));
        }

        $db = PearDatabase::getInstance();
        $sql = 'SELECT fieldname FROM vtiger_field WHERE fieldid = (SELECT fieldid FROM vtiger_fieldmodulerel WHERE module = ? AND relmodule = ?)';
        $res = $db->pquery($sql, [$this->recordModel->getModuleName(), $this->sourceRecordModel->getModuleName()]);

        if ($db->num_rows($res)) {
            $row = $db->fetchByAssoc($res);

            if (!in_array($row['fieldname'], static::$excludedFields)) {
                $this->recordModel->set($row['fieldName'], $this->sourceRecordModel->getId());
            }
        }

        foreach (static::$mapping as $targetFieldName => $sourceFieldName) {
            if (in_array($targetFieldName, static::$excludedFields)) {
                continue;
            }

            $this->recordModel->set($targetFieldName, $this->sourceRecordModel->get($sourceFieldName));
        }
    }
}