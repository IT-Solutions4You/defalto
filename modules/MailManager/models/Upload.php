<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

require_once 'modules/MailManager/MailManager.php';

/**
 * A file uploaded in the Basic composer becomes a Documents record with its attachment
 * (the XHR and the form upload of MailManager_Upload_Action share it).
 */
class MailManager_Upload_Model
{
    /**
     * Creates a Document of the uploaded file.
     *
     * @param qqUploadedFileXhr|qqUploadedFileForm $file              the upload (getName, getSize, save)
     * @param string|null                          $translationModule module of the denied message
     *
     * @return array|false ['success' => true, 'docid', 'attachid'], ['success' => true, 'error'] without
     *                     write access to Documents, false when the file was not saved
     * @throws Exception
     */
    public static function createDocument($file, ?string $translationModule)
    {
        $db = PearDatabase::getInstance();
        $currentUserModel = Users_Record_Model::getCurrentUserModel();

        if (!MailManager::checkModuleWriteAccessForCurrentUser('Documents')) {
            $errorMessage = getTranslatedString('LBL_WRITE_ACCESS_FOR', $translationModule) . " " . getTranslatedString('Documents') . " " . getTranslatedString(
                    'LBL_MODULE_DENIED',
                    $translationModule
                );

            return ['success' => true, 'error' => $errorMessage];
        }

        require_once 'data/CRMEntity.php';
        $document = CRMEntity::getInstance('Documents');

        $attachid = self::saveAttachment($file);

        if ($attachid !== false) {
            // Create document record
            $document = new Documents();
            $document->column_fields['notes_title'] = $file->getName();
            $document->column_fields['filename'] = $file->getName();
            $document->column_fields['filestatus'] = 1;
            $document->column_fields['filelocationtype'] = 'I';
            $document->column_fields['folderid'] = 1;
            $document->column_fields['filesize'] = $file->getSize();
            $document->column_fields['assigned_user_id'] = $currentUserModel->getId();
            $document->save('Documents');

            // Link file attached to document
            $db->pquery(
                "INSERT INTO vtiger_seattachmentsrel(crmid, attachmentsid) VALUES(?,?)",
                [$document->id, $attachid]
            );

            return ['success' => true, 'docid' => $document->id, 'attachid' => $attachid];
        }

        return false;
    }

    /**
     * Saves the uploaded file as an attachment (storage file, crmentity and attachments rows).
     *
     * @param qqUploadedFileXhr|qqUploadedFileForm $file the upload (getName, save)
     *
     * @return int|false attachment id, false when the file was not saved
     */
    public static function saveAttachment($file)
    {
        $db = PearDatabase::getInstance();
        $currentUserModel = Users_Record_Model::getCurrentUserModel();

        $uploadPath = decideFilePath();
        $fileName = $file->getName();

        if (!empty($fileName)) {
            $attachid = $db->getUniqueId('vtiger_crmentity');

            //sanitize the filename
            $binFile = sanitizeUploadFileName($fileName, vglobal('upload_badext'));
            $fileName = ltrim(basename(" " . $binFile));

            $saveAttachment = $file->save($uploadPath . $attachid . "_" . $fileName);

            if ($saveAttachment) {
                $description = $fileName;
                $date_var = $db->formatDate(date('YmdHis'), true);
                $usetime = $db->formatDate($date_var, true);

                $db->pquery(
                    "INSERT INTO vtiger_crmentity(crmid, creator_user_id, assigned_user_id,
				modifiedby, setype, description, createdtime, modifiedtime, presence, deleted)
				VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                    [$attachid, $currentUserModel->getId(), $currentUserModel->getId(), $currentUserModel->getId(), "Documents Attachment", $description, $usetime, $usetime, 1, 0]
                );

                $mimetype = Settings_MailConverter_MailAttachmentMIME_Handler::detect($uploadPath . $attachid . "_" . $fileName);

                $db->pquery(
                    "INSERT INTO vtiger_attachments SET attachmentsid=?, name=?, description=?, type=?, path=?",
                    [$attachid, $fileName, $description, $mimetype, $uploadPath]
                );

                return $attachid;
            }
        }

        return false;
    }
}
