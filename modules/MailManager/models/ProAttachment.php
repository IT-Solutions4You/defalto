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
 * Received mail attachments of the Pro local store. The content itself is never stored:
 * it is fetched from the mail server on demand (MailManager_ProAttachment_View).
 */
class MailManager_ProAttachment_Model
{
    /**
     * The only image types ever rendered inline – the sender chooses the declared type,
     * so HTML/SVG (script in the CRM origin) must always download.
     */
    public const INLINE_TYPES = ['image/png', 'image/jpeg', 'image/pjpeg', 'image/gif', 'image/webp', 'image/bmp'];

    /** The document type shown in the PDF viewer (served without the sandbox) */
    public const PDF_TYPE = 'application/pdf';

    /** Preview kinds of getPreviewType() */
    public const PREVIEW_IMAGE = 'image';
    public const PREVIEW_PDF = 'pdf';

    /** Largest attachment streamed (25 MB) */
    public const MAX_BYTES = 26214400;

    /**
     * How an attachment can be looked at in the browser — by its declared type only;
     * everything else (SVG, HTML, Office, …) is a download.
     *
     * @param string $mimeType declared type of the attachment
     *
     * @return string PREVIEW_IMAGE, PREVIEW_PDF or '' (download only)
     */
    public static function getPreviewType(string $mimeType): string
    {
        $mimeType = strtolower(trim($mimeType));

        if (in_array($mimeType, self::INLINE_TYPES, true)) {
            return self::PREVIEW_IMAGE;
        }

        return self::PDF_TYPE === $mimeType ? self::PREVIEW_PDF : '';
    }

    /**
     * Returns the attachment row with the message pointers needed to fetch it from the
     * mail server (remote_uid, message_folderid, mailboxid).
     *
     * @param int $attachmentId
     *
     * @return array|null
     */
    public static function getSource(int $attachmentId): ?array
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            'SELECT a.*, m.remote_uid, m.folderid AS message_folderid, m.mailboxid
             FROM df_mailmanager_attachment a
             INNER JOIN df_mailmanager_message m ON m.messageid = a.messageid
             WHERE a.attachmentid = ?',
            [$attachmentId]
        );

        return $adb->num_rows($result) ? $adb->raw_query_result_rowdata($result, 0) : null;
    }

    /**
     * Returns the folder row the attachment's message lies in.
     *
     * @param int $folderId
     *
     * @return array|null
     */
    public static function getFolder(int $folderId): ?array
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery('SELECT * FROM df_mailmanager_folder WHERE folderid = ?', [$folderId]);

        return $adb->num_rows($result) ? $adb->raw_query_result_rowdata($result, 0) : null;
    }

    /**
     * Fetches one MIME part from the mail server (nothing is stored on disk).
     *
     * @param array $attachment attachment row of getSource()
     * @param array $mailbox    accessible mailbox of the attachment
     *
     * @return array ['content' => string] or ['code' => HTTP status, 'message' => translated reason]
     */
    public static function retrieveContent(array $attachment, array $mailbox): array
    {
        $transport = MailManager_ProTransport_Model::getForMailbox($mailbox);

        if (!$transport->supports(MailManager_ProBaseTransport_Helper::CAP_ATTACHMENTS) || empty($attachment['remote_uid'])) {
            return ['code' => 404, 'message' => vtranslate('LBL_ATTACHMENT_NOT_AVAILABLE', 'MailManager')];
        }

        if ((int)$attachment['size_bytes'] > self::MAX_BYTES) {
            return ['code' => 413, 'message' => vtranslate('LBL_ATTACHMENT_TOO_LARGE', 'MailManager')];
        }

        $folder = self::getFolder((int)$attachment['message_folderid']);

        if (!$folder) {
            return ['code' => 404, 'message' => vtranslate('LBL_ATTACHMENT_NOT_FOUND', 'MailManager')];
        }

        try {
            $content = $transport->fetchAttachmentPart($folder, (int)$attachment['remote_uid'], (string)$attachment['remote_part']);
            $transport->close();
        } catch (Exception $e) {
            return ['code' => 502, 'message' => vtranslate('LBL_ATTACHMENT_SERVER_ERROR', 'MailManager')];
        }

        if (null === $content || '' === $content) {
            return ['code' => 404, 'message' => vtranslate('LBL_ATTACHMENT_NOT_FOUND', 'MailManager')];
        }

        return ['content' => (string)$content];
    }

    /**
     * The safe response headers of an attachment: a download unless an image may be
     * shown inline or a PDF is asked for by the preview. A PDF needs no sandbox — the
     * PDF viewer of the browser (or pdf.js) does not run in a sandboxed document;
     * nosniff keeps any other content declared as PDF from being rendered as a page.
     *
     * @param array  $attachment  attachment row
     * @param string $disposition inline | preview | '' (download)
     * @param int    $length      content length in bytes
     *
     * @return string[] header lines
     */
    public static function getResponseHeaders(array $attachment, string $disposition, int $length): array
    {
        $mimeType = strtolower(trim((string)$attachment['mime_type']));
        $previewType = self::getPreviewType($mimeType);
        $pdf = 'preview' === $disposition && self::PREVIEW_PDF === $previewType;
        $inline = $pdf || ('inline' === $disposition && self::PREVIEW_IMAGE === $previewType);

        if (!$inline) {
            // declared types are attacker-controlled – a download never renders
            $mimeType = 'application/octet-stream';
        }

        $filename = str_replace(['"', '\\', "\r", "\n"], '', (string)$attachment['filename']);
        $asciiName = preg_replace('/[^\x20-\x7E]/', '_', $filename);
        $headers = [
            'Content-Type: ' . $mimeType,
            'Content-Disposition: ' . ($inline ? 'inline' : 'attachment') . '; filename="' . $asciiName . '"; filename*=UTF-8\'\'' . rawurlencode($filename),
            'Content-Length: ' . $length,
            'X-Content-Type-Options: nosniff',
        ];

        if (!$pdf) {
            // even if a browser ever renders the body, it gets no script and no origin
            $headers[] = 'Content-Security-Policy: sandbox';
        }

        return $headers;
    }
}
