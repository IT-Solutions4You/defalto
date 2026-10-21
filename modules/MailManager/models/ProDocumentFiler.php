<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

/**
 * Mail attachments saved to a CRM record as Documents — fetched on demand
 * from the mail server, never cached. Files are stored through
 * Core_Attachment_Model and the Documents record model, exactly like Basic
 * MailManager saves attachments (N16), never by raw inserts.
 */
class MailManager_ProDocumentFiler_Model
{
    /** Source marker of the Documents records made from mail */
    public const DOCUMENT_SOURCE = 'MailManager';

    /** The default document folder */
    public const DEFAULT_FOLDER_ID = 1;

    /**
     * Saves the real (non-inline) attachments of a thread as Documents linked
     * to a CRM record. One attachment is ONE document: it remembers the
     * document made of it (df_mailmanager_attachment.documentid), and every
     * further record gets that document related — no second upload.
     *
     * @param array $mailbox   mailbox row (encrypted secrets)
     * @param int   $threadId  df_mailmanager_thread id
     * @param int   $crmid     target record
     * @param int   $userId    owner of the new documents
     * @param int   $messageId only this message's attachments (0 = the whole thread)
     *
     * @return int number of documents filed on the record (created or related)
     * @throws Exception
     */
    public static function saveThreadAttachmentsToRecord(array $mailbox, $threadId, $crmid, $userId, $messageId = 0)
    {
        $attachments = self::getThreadAttachments((int)$threadId, (int)$messageId);

        if (!$attachments) {
            return 0;
        }

        $transport = MailManager_ProTransport_Model::getForMailbox($mailbox);

        if (!$transport->supports(MailManager_ProBaseTransport_Helper::CAP_ATTACHMENTS)) {
            return 0;
        }

        $saved = 0;
        $folders = [];

        try {
            foreach ($attachments as $row) {
                // the document this attachment already is — only related to the record
                if (self::isLiveDocument((int)$row['documentid'])) {
                    $saved += self::relateDocument((int)$crmid, (int)$row['documentid']) ? 1 : 0;

                    continue;
                }

                // a repeated save (double click, retry) must not file the same attachment twice
                if (self::hasDocument((int)$crmid, (string)$row['filename'])) {
                    continue;
                }

                $folderId = (int)$row['folderid'];

                if (!array_key_exists($folderId, $folders)) {
                    $folders[$folderId] = self::getFolder($folderId);
                }

                if (!$folders[$folderId]) {
                    continue;
                }

                $content = $transport->fetchAttachmentPart($folders[$folderId], (int)$row['remote_uid'], (string)$row['remote_part']);

                if (null === $content || '' === $content) {
                    continue;
                }

                $documentId = self::createDocument(
                    (int)$crmid,
                    (int)$userId,
                    (string)$row['filename'],
                    (string)$row['mime_type'],
                    $content
                );

                if ($documentId) {
                    self::saveAttachmentDocumentId((int)$row['attachmentid'], $documentId);
                    $saved++;
                }
            }
        } finally {
            $transport->close();
        }

        return $saved;
    }

    /**
     * Makes a Documents record of a file without relating it to a record —
     * a knowledge file in its document folder.
     *
     * @param int    $userId   owner
     * @param string $filename file name
     * @param string $mimeType MIME type
     * @param string $content  binary content
     * @param int    $folderId document folder
     *
     * @return int id of the document, 0 when it could not be made
     * @throws Exception
     */
    public static function createStandaloneDocument($userId, $filename, $mimeType, $content, $folderId)
    {
        return self::createDocument(0, (int)$userId, (string)$filename, (string)$mimeType, (string)$content, (int)$folderId);
    }

    /**
     * Saves the files of a conversation as Documents of a record and of the records
     * that get them along (the project of a task — related, no second upload).
     *
     * @param array $mailbox    mailbox row
     * @param int   $threadId
     * @param int   $crmid      the record the user may edit
     * @param int   $userId
     * @param bool  $latestOnly only the files of the newest received mail (a task made
     *                          from it) — not everything the conversation ever carried
     *
     * @return int documents saved to the record
     * @throws Exception when a file cannot be fetched or saved
     */
    public static function saveAttachmentsToRecords(array $mailbox, int $threadId, int $crmid, int $userId, bool $latestOnly): int
    {
        $messageId = 0;

        if ($latestOnly) {
            $messageId = self::getLatestInboundMessageId($threadId) ?: -1;
        }

        $saved = $messageId < 0 ? 0 : self::saveThreadAttachmentsToRecord($mailbox, $threadId, $crmid, $userId, $messageId);

        // the project of a task gets the same documents related (no second upload)
        foreach ($messageId < 0 ? [] : MailManager_ProEmailFiler_Model::getAlsoRecordIds($crmid) as $recordId) {
            self::saveThreadAttachmentsToRecord($mailbox, $threadId, $recordId, $userId, $messageId);
        }

        return $saved;
    }

    /**
     * The newest received (non-draft) message of a conversation — "save the
     * files of the mail I am reading" uses only its attachments.
     *
     * @param int $threadId df_mailmanager_thread id
     *
     * @return int df_mailmanager_message id, 0 when the conversation has no inbound mail
     */
    public static function getLatestInboundMessageId(int $threadId): int
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            "SELECT messageid FROM df_mailmanager_message
             WHERE threadid = ? AND direction = 'in' AND is_draft = 0 ORDER BY sent_at DESC LIMIT 1",
            [$threadId]
        );

        return $adb->num_rows($result) ? (int)$adb->query_result($result, 0, 'messageid') : 0;
    }

    /**
     * The non-inline attachments of a thread (or of one message) that still
     * have a server pointer.
     *
     * @param int $threadId  df_mailmanager_thread id
     * @param int $messageId one message only, 0 = the whole thread
     *
     * @return array of attachment rows + remote_uid, folderid
     */
    private static function getThreadAttachments(int $threadId, int $messageId): array
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            'SELECT a.attachmentid, a.filename, a.mime_type, a.remote_part, a.documentid, m.remote_uid, m.folderid
             FROM df_mailmanager_attachment a
             INNER JOIN df_mailmanager_message m ON m.messageid = a.messageid
             WHERE m.threadid = ? AND a.is_inline = 0 AND m.remote_uid IS NOT NULL
               AND (? = 0 OR m.messageid = ?)',
            [$threadId, $messageId, $messageId]
        );
        $rows = [];

        for ($i = 0, $count = $adb->num_rows($result); $i < $count; $i++) {
            $rows[] = $adb->raw_query_result_rowdata($result, $i);
        }

        return $rows;
    }

    /**
     * Loads a cached folder row for the attachment fetch.
     *
     * @param int $folderId df_mailmanager_folder id
     *
     * @return array|null
     */
    private static function getFolder(int $folderId): ?array
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery('SELECT * FROM df_mailmanager_folder WHERE folderid = ?', [$folderId]);

        return $adb->num_rows($result) ? $adb->raw_query_result_rowdata($result, 0) : null;
    }

    /**
     * Remembers the document made of an attachment.
     *
     * @param int $attachmentId df_mailmanager_attachment id
     * @param int $documentId   Documents record id
     *
     * @return void
     */
    private static function saveAttachmentDocumentId(int $attachmentId, int $documentId): void
    {
        PearDatabase::getInstance()->pquery(
            'UPDATE df_mailmanager_attachment SET documentid = ? WHERE attachmentid = ?',
            [$documentId, $attachmentId]
        );
    }

    /**
     * Checks that a remembered document still exists.
     *
     * @param int $documentId Documents record id
     *
     * @return bool the document exists and is not deleted
     */
    private static function isLiveDocument(int $documentId): bool
    {
        if ($documentId <= 0) {
            return false;
        }

        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            "SELECT 1 FROM vtiger_crmentity WHERE crmid = ? AND setype = 'Documents' AND deleted = 0",
            [$documentId]
        );

        return (bool)$adb->num_rows($result);
    }

    /**
     * Relates an existing document to a record through the Documents record
     * model (Core_Relation_Model, idempotent).
     *
     * @param int $crmid      target record
     * @param int $documentId Documents record id
     *
     * @return bool false when the record had the document already
     */
    private static function relateDocument(int $crmid, int $documentId): bool
    {
        if ($crmid <= 0 || self::isDocumentRelated($crmid, $documentId)) {
            return false;
        }

        /** @var Documents_Record_Model $documentRecord */
        $documentRecord = Vtiger_Record_Model::getCleanInstance('Documents');
        $documentRecord->saveDocumentsRelation($crmid, $documentId);

        return true;
    }

    /**
     * Checks whether a document is related to a record already (either
     * relation table the core uses for Documents).
     *
     * @param int $crmid      record id
     * @param int $documentId Documents record id
     *
     * @return bool
     */
    private static function isDocumentRelated(int $crmid, int $documentId): bool
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            "SELECT 1 FROM vtiger_senotesrel WHERE crmid = ? AND notesid = ?
             UNION SELECT 1 FROM vtiger_crmentityrel WHERE crmid = ? AND relcrmid = ? AND relmodule = 'Documents'
             LIMIT 1",
            [$crmid, $documentId, $crmid, $documentId]
        );

        return (bool)$adb->num_rows($result);
    }

    /**
     * Checks whether the record already has a document of this title.
     *
     * @param int    $crmid target record
     * @param string $title attachment name the document would get
     *
     * @return bool
     */
    private static function hasDocument(int $crmid, string $title): bool
    {
        $title = '' !== $title ? $title : 'attachment';
        $adb = PearDatabase::getInstance();
        // the title may be stored html-encoded — both spellings count
        $result = $adb->pquery(
            "SELECT 1 FROM vtiger_notes n
             INNER JOIN vtiger_crmentity ce ON ce.crmid = n.notesid AND ce.deleted = 0
             WHERE n.notes_title IN (?, ?) AND (
                n.notesid IN (SELECT notesid FROM vtiger_senotesrel WHERE crmid = ?)
                OR n.notesid IN (SELECT relcrmid FROM vtiger_crmentityrel WHERE crmid = ? AND relmodule = 'Documents')
             ) LIMIT 1",
            [$title, htmlspecialchars($title, ENT_QUOTES, 'UTF-8'), $crmid, $crmid]
        );

        return (bool)$adb->num_rows($result);
    }

    /**
     * Creates one Documents record from binary content and relates it to a
     * record: the file through Core_Attachment_Model (sanitized name,
     * encrypted stored name in the storage folder), the document through the
     * Documents record model, the relations through the Documents record
     * model helpers — the same steps Basic MailManager takes.
     *
     * @param int    $crmid    target record, 0 = no relation
     * @param int    $userId   owner
     * @param string $filename file name chosen by the sender
     * @param string $mimeType MIME type
     * @param string $content  binary content
     * @param int    $folderId document folder
     *
     * @return int id of the document, 0 when it could not be made
     * @throws Exception
     */
    private static function createDocument(int $crmid, int $userId, string $filename, string $mimeType, string $content, int $folderId = self::DEFAULT_FOLDER_ID): int
    {
        $filename = '' !== $filename ? str_replace(['/', '\\'], '-', $filename) : 'attachment';

        $attachment = Core_Attachment_Model::getInstance('Documents');
        $attachment->retrieveDefault($filename);
        $attachment->setType('' !== $mimeType ? $mimeType : 'application/octet-stream');
        $attachment->saveFile($content);

        if (!$attachment->validateSaveFile()) {
            return 0;
        }

        $attachment->save();
        $attachmentId = (int)$attachment->getId();

        /** @var Documents_Record_Model $document */
        $document = Vtiger_Record_Model::getCleanInstance('Documents');
        $document->set('notes_title', $filename);
        $document->set('filename', $attachment->getName());
        $document->set('filesize', mb_strlen($content, '8bit'));
        $document->set('filetype', $attachment->get('type'));
        $document->set('filestatus', 1);
        $document->set('filelocationtype', 'I');
        $document->set('folderid', $folderId > 0 ? $folderId : self::DEFAULT_FOLDER_ID);
        $document->set('assigned_user_id', $userId ?: (int)Users_Record_Model::getCurrentUserModel()->getId());
        $document->set('source', self::DOCUMENT_SOURCE);
        $document->save();

        $documentId = (int)$document->getId();

        if (!$documentId) {
            return 0;
        }

        $document->saveAttachmentsRelation($documentId, $attachmentId);

        if ($crmid > 0) {
            $document->saveDocumentsRelation($crmid, $documentId);
        }

        return $documentId;
    }
}
