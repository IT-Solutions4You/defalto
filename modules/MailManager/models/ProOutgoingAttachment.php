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
 * Attachments of an outgoing mail, all in one shape
 * (['content', 'filename', 'mime_type']) — files uploaded with the composer
 * and attachments of a forwarded message fetched from the mail server
 */
class MailManager_ProOutgoingAttachment_Model
{
    /**
     * Total size of the forwarded attachments one mail may carry — the same
     * cap as the attachment download; checked BEFORE fetching, the parts are
     * held in memory while the mail is built
     */
    private const MAX_FORWARD_BYTES = 26214400;

    /**
     * Attachment name as the sender sees it — the file never touches the
     * disk, so only what breaks a MIME header goes (path, control chars);
     * vtiger's upload sanitizer would rename it ("Offer & Terms.pdf" →
     * "Offer_Terms.pdf", "page.html" → "page.htmlfile.txt")
     *
     * @param string $name name reported by the browser
     * @return string
     */
    public static function cleanFileName($name)
    {
        $name = basename(str_replace('\\', '/', (string)$name));
        $name = trim(preg_replace('/[\x00-\x1F\x7F]/u', '', $name));

        return $name !== '' ? $name : 'attachment';
    }

    /** Upload limit when the CRM has no $upload_maxsize (bytes) */
    public const DEFAULT_UPLOAD_LIMIT = 10485760;

    /**
     * Total size the composer may upload: the CRM $upload_maxsize, which Defalto keeps in MB.
     *
     * @return int bytes
     */
    public static function getUploadLimit(): int
    {
        return (int)Vtiger_Util_Helper::getMaxUploadSizeInBytes() ?: self::DEFAULT_UPLOAD_LIMIT;
    }

    /**
     * Files uploaded with the composer, in the same shape as the forwarded
     * ones. The total size follows the CRM $upload_maxsize setting; a file
     * the browser could not deliver (PHP limits) is reported, never dropped
     * silently
     *
     * @param array|null $uploads the $_FILES entry of the composer file input
     * @return array of ['content', 'filename', 'mime_type']
     * @throws Exception when a file failed to upload or the limit is exceeded
     */
    public static function collectUploadedAttachments($uploads)
    {
        if (!$uploads || !is_array($uploads['name'] ?? null)) {
            return [];
        }

        $limit = self::getUploadLimit();
        $attachments = [];
        $total = 0;
        foreach ($uploads['name'] as $index => $name) {
            if ((int)$uploads['error'][$index] === UPLOAD_ERR_NO_FILE) {
                continue;
            }
            if ((int)$uploads['error'][$index] !== UPLOAD_ERR_OK || !is_uploaded_file($uploads['tmp_name'][$index])) {
                throw new Exception(vtranslate('LBL_ATTACHMENT_UPLOAD_FAILED', 'MailManager'));
            }
            $total += (int)$uploads['size'][$index];
            if ($total > $limit) {
                throw new Exception(vtranslate('LBL_ATTACHMENTS_TOO_LARGE', 'MailManager'));
            }
            $content = file_get_contents($uploads['tmp_name'][$index]);
            if ($content === false) {
                throw new Exception(vtranslate('LBL_ATTACHMENT_UPLOAD_FAILED', 'MailManager'));
            }
            $attachments[] = [
                'content' => $content,
                'filename' => self::cleanFileName($name),
                'mime_type' => $uploads['type'][$index] ?: 'application/octet-stream',
            ];
        }

        return $attachments;
    }

    /**
     * Files of CRM Documents (the attachments of an EMAIL Maker template) — internal
     * files the user may see only; the upload limit counts them too.
     *
     * @param string $ids comma separated Documents record ids
     *
     * @return array of ['content', 'filename', 'mime_type']
     * @throws Exception when the files exceed the upload limit
     */
    public static function collectDocumentAttachments($ids)
    {
        $documentIds = array_unique(array_filter(array_map('intval', explode(',', (string)$ids))));
        $attachments = [];
        $total = 0;
        $limit = self::getUploadLimit();

        foreach ($documentIds as $documentId) {
            if ('Documents' !== getSalesEntityType($documentId)
                || !Users_Privileges_Model::isPermitted('Documents', 'DetailView', $documentId)) {
                continue;
            }

            $document = Vtiger_Record_Model::getInstanceById($documentId, 'Documents');
            $file = 'I' === $document->get('filelocationtype') ? $document->getFileDetails() : [];

            if (!$file) {
                continue;
            }

            // relative (storage/…) or an absolute $storage_directory outside the web root
            $path = decode_html(Core_Storage_Model::getInstance()->getAttachmentFilePath($file));
            $content = is_file($path) ? file_get_contents($path) : false;

            if (false === $content) {
                continue;
            }

            $total += strlen($content);

            if ($total > $limit) {
                throw new Exception(vtranslate('LBL_ATTACHMENTS_TOO_LARGE', 'MailManager'));
            }

            $attachments[] = [
                'content' => $content,
                'filename' => self::cleanFileName(decode_html((string)$file['name'])),
                'mime_type' => (string)$file['type'] ?: 'application/octet-stream',
            ];
        }

        return $attachments;
    }

    /**
     * Fetches the selected forward attachments from the mail server (on demand)
     *
     * @param array $mailbox encrypted row
     * @param string $ids comma separated attachmentids
     * @param int $threadId scope (only attachments of this thread)
     * @return array of ['content', 'filename', 'mime_type']
     * @throws Exception when the selected attachments exceed MAX_FORWARD_BYTES
     */
    public static function collectForwardAttachments(array $mailbox, $ids, $threadId)
    {
        $attachmentIds = array_filter(array_map('intval', explode(',', $ids)));
        if (!$attachmentIds || !$threadId) {
            return [];
        }
        $transport = MailManager_ProTransport_Model::getForMailbox($mailbox);
        if (!$transport->supports(MailManager_ProBaseTransport_Helper::CAP_ATTACHMENTS)) {
            return [];
        }

        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            'SELECT a.attachmentid, a.filename, a.mime_type, a.remote_part, a.size_bytes, m.remote_uid, m.folderid
             FROM df_mailmanager_attachment a
             INNER JOIN df_mailmanager_message m ON m.messageid = a.messageid
             WHERE m.threadid = ? AND a.attachmentid IN (' . generateQuestionMarks($attachmentIds) . ')
               AND m.remote_uid IS NOT NULL',
            array_merge([$threadId], $attachmentIds)
        );
        $count = $adb->num_rows($result);
        if (!$count) {
            return [];
        }

        $totalBytes = 0;
        for ($i = 0; $i < $count; $i++) {
            $totalBytes += (int)$adb->raw_query_result_rowdata($result, $i)['size_bytes'];
        }
        if ($totalBytes > self::MAX_FORWARD_BYTES) {
            throw new Exception(vtranslate('LBL_ATTACHMENTS_TOO_LARGE', 'MailManager'));
        }

        $attachments = [];
        try {
            for ($i = 0; $i < $count; $i++) {
                $row = $adb->raw_query_result_rowdata($result, $i);
                $folderResult = $adb->pquery('SELECT * FROM df_mailmanager_folder WHERE folderid = ?', [(int)$row['folderid']]);
                if (!$adb->num_rows($folderResult)) {
                    continue;
                }
                $content = $transport->fetchAttachmentPart(
                    $adb->raw_query_result_rowdata($folderResult, 0),
                    (int)$row['remote_uid'],
                    (string)$row['remote_part']
                );
                if ($content !== null && $content !== '') {
                    $attachments[] = [
                        'content' => $content,
                        'filename' => (string)$row['filename'],
                        'mime_type' => $row['mime_type'] ?: 'application/octet-stream',
                    ];
                }
            }
        } finally {
            $transport->close();
        }

        return $attachments;
    }
}
