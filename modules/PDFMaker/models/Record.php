<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class PDFMaker_Record_Model extends Vtiger_Record_Model
{

    /**
     * Function to get the instance of Custom View module, given custom view id
     * @param <Integer> $cvId
     * @return CustomView_Record_Model instance, if exists. Null otherwise
     */
    public static function getInstanceById($templateId, $module = null)
    {
        $db = PearDatabase::getInstance();
        $sql = 'SELECT vtiger_pdfmaker.* FROM vtiger_pdfmaker WHERE vtiger_pdfmaker.templateid = ?';
        $params = array($templateId);
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
     * @param <type> $value - id value
     * @return <Object> - current instance
     */
    public function setId($value)
    {
        return $this->set('templateid', $value);
    }

    /**
     * Function to delete the email template
     * @param type $recordIds
     */
    public function delete()
    {
        $this->getModule()->deleteRecord($this);
    }

    /**
     * Function to delete all the email templates
     * @param type $recordIds
     */
    public function deleteAllRecords()
    {
        $this->getModule()->deleteAllRecords();
    }

    /**
     * Function to get the Email Template Record
     * @param type $record
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
    public function getId()
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

    public function getName()
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

}
