<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Core_Attachment_Model extends Core_DatabaseData_Model {

    protected string $tableId = 'attachmentsid';
    protected string $table = 'vtiger_attachments';
    protected string $tableName = 'name';



    /**
     * @throws AppException
     */
    public static function getInstance($module = 'Core', $record = null)
    {
        $className = Vtiger_Loader::getComponentClassName('Model', 'Attachment', $module);

        if (class_exists($className)) {
            $instance = new $className();
        } else {
            $instance = new self();
        }

        $instance->set('module', $module);
        $instance->retrieveDB();

        if($record) {
            $instance->retriveData();
        }

        return $instance;
    }

    /**
     * @throws Exception
     */
    public function retrieveData(): void
    {
        $record = $this->getId();
        $data = $this->getData();

        $entityResult = $this->db->pquery('SELECT * FROM vtiger_crmentity WHERE crmid=?', [$record]);
        $entityData = (array)$this->db->query_result_rowdata($entityResult);

        $attachmentResult = $this->db->pquery('SELECT * FROM vtiger_attachments WHERE attachmentsid=?', [$record]);
        $attachmentData = (array)$this->db->query_result_rowdata($attachmentResult);

        $this->setData(array_merge($data, $entityData, $attachmentData));
    }

    public function retrieveDefault($fileName)
    {
        $fileName = sanitizeUploadFileName($fileName, vglobal('upload_badext'));
        $id = $this->getDB()->getUniqueId('vtiger_crmentity');

        $this->setId($id);
        $this->setName($fileName);
        $this->setDescription($fileName);
        $this->setStoredName($fileName);
        $this->setPath(decideFilePath());
        $this->setType(mime_content_type($fileName));
    }

    public function setType($value)
    {
        $this->set('type', $value);
    }

    public function setPath($value): void
    {
        $this->set('path', $value);
    }

    public function setStoredName($value): void
    {
        $this->set('storedname', $value);
    }

    public function setDescription($value): void
    {
        $this->set('description', $value);
    }

    public function saveFile($content)
    {
        if ($this->isEmpty('storedname') || $this->isEmpty('path')) {
            throw new AppException('Missing stored name or path for file save');
        }

        $this->set('content', $content);
        $this->set('is_saved_file', file_put_contents($this->getSaveFile(), $content));
    }

    public function getSaveFile()
    {
        return sprintf('%s/%s_%s', $this->get('path'), $this->getId(), $this->get('storedname'));
    }

    public function getAttachmentTable(): self
    {
        return $this->getTable($this->table, $this->tableId);
    }

    public function validateSaveFile(): bool
    {
        if ($this->isEmpty('is_saved_file')) {
            throw new AppException('Attachment file is not saved');
        }

        return true;
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return (string)$this->get('content');
    }

    /**
     * @throws AppException
     */
    public function save(): void
    {
        $currentUserModel = Users_Record_Model::getCurrentUserModel();
        $time = $this->getDB()->formatDate(date('Y-m-d H:i:s'), true);
        $entityTable = $this->getTable('vtiger_crmentity', 'crmid');
        $entityParams = [
            'crmid' => $this->getId(),
            'smcreatorid' => $currentUserModel->getId(),
            'smownerid' => $currentUserModel->getId(),
            'modifiedby' => $currentUserModel->getId(),
            'setype' => $this->get('module') . ' Attachment',
            'description' => $this->getName(),
            'createdtime' => $time,
            'modifiedtime' => $time,
            'presence' => 1,
            'deleted' => 0,
        ];
        $attachmentTable = $this->getAttachmentTable();
        $attachmentParams = [
            'attachmentsid' => $this->getId(),
            'name' => $this->getName(),
            'storedname' => $this->get('storedname'),
            'description' => $this->get('description'),
            'type' => $this->get('type'),
            'path' => $this->get('path'),
        ];

        $entityData = $entityTable->selectData(['crmid'], ['crmid' => $this->getId()]);

        if (empty($entityData['crmid'])) {
            $entityTable->insertData($entityParams);
            $attachmentTable->insertData($attachmentParams);
        } else {
            $entityTable->updateData($entityParams, ['crmid' => $this->getId()]);
            $attachmentTable->updateData($attachmentParams, ['attachmentsid' => $this->getId()]);
        }
    }
}
