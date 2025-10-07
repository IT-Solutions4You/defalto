<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Core_Attachment_Model extends Core_DatabaseData_Model
{
    protected string $tableId = 'attachmentsid';
    protected string $table = 'vtiger_attachments';
    protected string $tableName = 'name';

    /**
     * @throws Exception
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

        if ($record) {
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
            throw new Exception('Missing stored name or path for file save');
        }

        $this->set('content', $content);
        $this->set('is_saved_file', file_put_contents($this->getSaveFile(), $content));
    }

    /**
     * @param $file
     * @return void
     * @throws Exception
     */
    public function copyFile($file): void
    {
        if ($this->isEmpty('storedname') || $this->isEmpty('path')) {
            throw new Exception('Missing stored name or path for file save');
        }

        $this->set('is_saved_file', copy($file, $this->getSaveFile()));

        $originalSize = filesize($file);
        $copiedSize = filesize($this->getSaveFile());

        if ($originalSize !== $copiedSize) {
            throw new Exception("Súbor bol poškodený pri kopírovaní. Veľkosť nesedí.");
        }
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
            throw new Exception('Attachment file is not saved');
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
     * @throws Exception
     */
    public function save(): void
    {
        $currentUserModel = Users_Record_Model::getCurrentUserModel();
        $time = $this->getDB()->formatDate(date('Y-m-d H:i:s'), true);
        $entityTable = $this->getTable('vtiger_crmentity', 'crmid');
        $entityParams = [
            'crmid'            => $this->getId(),
            'creator_user_id'  => $currentUserModel->getId(),
            'assigned_user_id' => $currentUserModel->getId(),
            'modifiedby'       => $currentUserModel->getId(),
            'setype'           => $this->getModuleName() . ' ' . $this->getAttachmentType(),
            'description'      => $this->getName(),
            'createdtime'      => $time,
            'modifiedtime'     => $time,
            'presence'         => 1,
            'deleted'          => 0,
        ];
        $attachmentTable = $this->getAttachmentTable();
        $attachmentParams = [
            'attachmentsid' => $this->getId(),
            'name'          => $this->getName(),
            'storedname'    => $this->get('storedname'),
            'description'   => $this->get('description'),
            'type'          => $this->get('type'),
            'path'          => $this->get('path'),
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

    /**
     * @return string
     */
    public function getAttachmentType()
    {
        return $this->isEmpty('attachment_type') ? 'Attachment' : $this->get('attachment_type');
    }

    /**
     * @param string $value
     * @return void
     */
    public function setAttachmentType(string $value): void
    {
        $this->set('attachment_type', $value);
    }

    /**
     * @return string
     */
    public function getModuleName(): string
    {
        return (string)$this->get('module');
    }
}