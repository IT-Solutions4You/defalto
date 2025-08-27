<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class PDFMaker_Record_Model extends Vtiger_Record_Model
{
    /**
     * Function to get the instance of Custom View module, given custom view id
     *
     * @param <Integer> $cvId
     *
     * @return CustomView_Record_Model instance, if exists. Null otherwise
     */
    public static function getInstanceById($templateId, $module = null)
    {
        $db = PearDatabase::getInstance();
        $sql = 'SELECT vtiger_pdfmaker.* FROM vtiger_pdfmaker WHERE vtiger_pdfmaker.templateid = ?';
        $params = [$templateId];
        $result = $db->pquery($sql, $params);

        if ($db->num_rows($result) > 0) {
            $row = $db->query_result_rowdata($result, 0);
            $recordModel = new self();
            $row['label'] = $row['filename'];

            return $recordModel->setData($row)->setId($templateId)->setModule($row['module']);
        }

        throw new Exception(vtranslate('LBL_RECORD_DELETE', 'Vtiger'), 1);
    }

    /**
     * Function to set the id of the record
     *
     * @param <type> $value - id value
     *
     * @return <Object> - current instance
     */
    public function setId($id): self
    {
        $this->set('templateid', $id);

        return $this;
    }

    /**
     * Function to delete the email template
     *
     * @param type $recordIds
     */
    public function delete(): void
    {
        $this->getModule()->deleteRecord($this);
    }

    /**
     * Function to delete all the email templates
     *
     * @param type $recordIds
     */
    public function deleteAllRecords()
    {
        $this->getModule()->deleteAllRecords();
    }

    /**
     * Function to get the Email Template Record
     *
     * @param type $record
     *
     * @return <EmailTemplate_Record_Model>
     */

    public function getTemplateData($record)
    {
        return $this->getModule()->getTemplateData($record);
    }

    /**
     * Function to get the Edit View url for the record
     * @return <String> - Record Edit View Url
     */
    public function getEditViewUrl()
    {
        $module = $this->getModule();

        return 'index.php?module=PDFMaker&view=EditFree&templateid=' . $this->getId();
    }

    /**
     * Function to get the id of the record
     * @return <Number> - Record Id
     */
    public function getId(): int
    {
        return $this->get('templateid');
    }

    /**
     * Function to get the Detail View url for the record
     * @return <String> - Record Detail View Url
     */
    public function getDetailViewUrl()
    {
        $module = $this->getModule();

        return 'index.php?module=PDFMaker&view=DetailFree&templateid=' . $this->getId();
    }

    public function getName(): string
    {
        return $this->get('filename');
    }

    public function isDeleted()
    {
        if ($this->get('deleted') == '1') {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Function returns valuetype of the field filter
     * @return <String>
     */
    function getFieldFilterValueType($fieldname)
    {
        $conditions = $this->get('conditions');

        if (!empty($conditions) && is_array($conditions)) {
            foreach ($conditions as $filter) {
                if ($fieldname == $filter['fieldname']) {
                    return $filter['valuetype'];
                }
            }
        }

        return false;
    }

    /**
     * @return self
     */
    public function getPDFMakerSettingsTable(): self
    {
        return $this->getTable('vtiger_pdfmaker_settings', 'templateid');
    }

    /**
     * @return self
     */
    public function getPDFMakerTable(): self
    {
        return $this->getTable('vtiger_pdfmaker', 'templateid');
    }

    /**
     * @param string $name
     * @return bool
     */
    public function isTemplateExists($name): bool
    {
        return !empty($this->getTemplateIdByName($name));
    }

    /**
     * @param string $name
     * @return int
     * @throws Exception
     */
    public function getTemplateIdByName(string $name): int
    {
        $data = $this->getPDFMakerTable()->selectData(['templateid'], ['filename' => $name]);

        return (int)$data['templateid'];
    }

    /**
     * @param array $data
     * @param array $dataSettings
     * @return void
     * @throws Exception
     */
    public function updateTemplate(array $data, array $dataSettings): void
    {
        if (empty($data['templateid'])) {
            $this->retrieveDB();
            $dataSettings['templateid'] = $data['templateid'] = $this->getDB()->getUniqueID('vtiger_pdfmaker');

            $this->getPDFMakerTable()->insertData($data);
            $this->getPDFMakerSettingsTable()->insertData($dataSettings);
        } elseif (defined('MIGRATE_DATA')) {
            $search = ['templateid' => $data['templateid']];

            $this->getPDFMakerTable()->updateData($data, $search);
            $this->getPDFMakerSettingsTable()->updateData($dataSettings, $search);
        }
    }

}