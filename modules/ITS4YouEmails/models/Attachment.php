<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class ITS4YouEmails_Attachment_Model extends Vtiger_Base_Model
{
    /**
     * @var PearDatabase
     */
    public $db;

    /**
     * @param null|int $record
     * @return self
     * @throws Exception
     */
    public static function getInstance($record = null)
    {
        $instance = new self();
        $instance->db = PearDatabase::getInstance();
        $instance->set('id', $record);
        $instance->retrieveData();

        return $instance;
    }

    public function save()
    {
        $recordId = $this->get('id');
        $currentDate = $this->db->formatDate(date('Y-m-d H:i:s'), true);
        $params1 = array(
            'crmid' => $recordId,
            'smcreatorid' => $this->get('creator_id'),
            'smownerid' => $this->get('owner_id'),
            'setype' => $this->get('module') . ' Attachment',
            'description' => $this->get('description'),
            'createdtime' => $currentDate,
            'modifiedtime' => $currentDate,
        );
        $params2 = array(
            'attachmentsid' => $recordId,
            'name' => $this->get('file_name'),
            'description' => $this->get('description'),
            'type' => $this->get('file_type'),
            'path' => $this->get('file_path'),
        );

        if (columnExists('storedname', 'vtiger_attachments')) {
            $params2['storedname'] = $this->get('stored_name');
        }

        $this->db->pquery(
            $this->getInsertQuery('vtiger_crmentity', $params1),
            $params1
        );
        $this->db->pquery(
            $this->getInsertQuery('vtiger_attachments', $params2),
            $params2
        );
    }

    public function getInsertQuery($table, $params)
    {
        return sprintf('INSERT INTO %s (%s) VALUES (%s)',
            $table, implode(',', array_keys($params)), generateQuestionMarks($params)
        );
    }

    /**
     * @throws Exception
     */
    public function retrieveData()
    {
        $record = $this->get('id');
        $data = $this->getData();

        $entityResult = $this->db->pquery('SELECT * FROM vtiger_crmentity WHERE crmid=?', [$record]);
        $entityData = (array)$this->db->query_result_rowdata($entityResult);

        $attachmentResult = $this->db->pquery('SELECT * FROM vtiger_attachments WHERE attachmentsid=?', [$record]);
        $attachmentData = (array)$this->db->query_result_rowdata($attachmentResult);

        $this->setData(array_merge($data, $entityData, $attachmentData));
    }

    public static function getParentRecords($recordId)
    {
        $parentRecordModel = Vtiger_Record_Model::getInstanceById($recordId);
        $parentModuleModel = $parentRecordModel->getModule();

        $relatedModuleModel = Vtiger_Module_Model::getInstance('Documents');
        $relationModel = Vtiger_Relation_Model::getInstance($parentModuleModel, $relatedModuleModel);

        $sql = $relationModel->getQuery($parentRecordModel);
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery($sql);
        $records = [];

        while ($row = $adb->fetchByAssoc($result)) {
            $documentRecordId = $row['crmid'];

            if (isPermitted('Documents', 'DetailView', $documentRecordId)) {
                $folder = Documents_Folder_Model::getInstanceById($row['folderid']);

                if ($folder) {
                    $row['foldername'] = $folder->getName();
                }

                $records[$documentRecordId] = $row;
            }
        }

        return $records;
    }
}

