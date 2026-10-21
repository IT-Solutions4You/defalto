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
 * Outgoing mail of a mailbox — sending a reply / forward / new mail and
 * saving a draft: the copy on the mail server (Sent / Drafts), the immediate
 * cache row, the consumed or replaced draft and the traces on CRM records
 *
 * Both entry points take a $mail array built from the composer request:
 *   'to', 'cc', 'bcc' => string[] (parsed), 'subject', 'body' => string (HTML),
 *   'thread', 'reply_message', 'quote_message', 'draft_message' => int,
 *   'compose_mode' => string, 'to_records' => int[],
 *   'forward_attachments' => string (comma separated ids),
 *   'document_attachments' => string (comma separated Documents ids, EMAIL Maker template),
 *   'uploads' => array|null (the $_FILES entry of the composer)
 *
 * Sending goes through the unified Defalto flow (07-SENDING.md, N17): an
 * ITS4YouEmails record is built and sent with ITS4YouEmails_Record_Model::send();
 * this class stores the copy in the mailbox Sent folder, flags the original and
 * keeps the local cache. Drafts are IMAP APPEND to Drafts, no ITS4YouEmails record.
 */
class MailManager_ProOutbox_Model
{
    /** Message ids kept in the References header: the thread root + the newest ones */
    private const MAX_REFERENCES = 10;

    /** CRM end records a sent mail is related to, the most specific first */
    public const RELATED_MODULES = ['HelpDesk', 'Project', 'Potentials', 'Leads', 'Contacts', 'Accounts'];

    /**
     * @param string $value comma/semicolon separated addresses
     * @return string[] valid, lowercased
     */
    public static function parseAddresses($value)
    {
        $addresses = [];
        foreach (preg_split('/[,;]+/', (string)$value) as $chunk) {
            $email = strtolower(trim($chunk));
            if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $addresses[] = $email;
            }
        }

        return array_values(array_unique($addresses));
    }

    /**
     * The mail to send from what the composer sent.
     *
     * @param array $input to, cc, bcc, mark_done, subject and body (raw), thread, reply_message,
     *                     quote_message, draft_message, compose_mode, to_records,
     *                     forward_attachments, document_attachments, uploads and
     *                     scheduled_at ('' = send now)
     *
     * @return array see the class docblock; scheduled_at (UTC) only for "Send later"
     */
    public static function getMail(array $input): array
    {
        $mail = [
            'to' => self::parseAddresses($input['to']),
            'cc' => self::parseAddresses($input['cc']),
            'bcc' => self::parseAddresses($input['bcc']),
            'mark_done' => '1' === $input['mark_done'],
            // the request purifier treats the subject as HTML and mangles plain text
            // ("Price < 100", "... (week 1)") – it comes raw; a header must stay
            // single-line, PHPMailer encodes the rest
            'subject' => trim(str_replace(["\r", "\n"], ' ', (string)$input['subject'])),
            // the highlights of unverified AI facts stay in the composer (I16)
            'body' => MailManager_ProDraftFacts_Helper::stripMarks((string)$input['body']),
            'thread' => (int)$input['thread'],
            'reply_message' => (int)$input['reply_message'],
            'quote_message' => (int)$input['quote_message'],
            'draft_message' => (int)$input['draft_message'],
            'compose_mode' => (string)$input['compose_mode'],
            'to_records' => array_filter(array_map('intval', explode(',', (string)$input['to_records']))),
            'forward_attachments' => (string)$input['forward_attachments'],
            'document_attachments' => (string)$input['document_attachments'],
            'uploads' => $input['uploads'],
        ];

        // "Send later": the user's wall-clock time in the user's CRM time zone, kept as UTC
        if ('' !== (string)$input['scheduled_at']) {
            $mail['scheduled_at'] = MailManager_ProMailFormat_Model::userInputToUtc((string)$input['scheduled_at']);
        }

        return $mail;
    }

    /**
     * Why the mail cannot go out: a "Send later" time that is not valid, a missing
     * recipient, subject or text.
     *
     * @param array $mail mail of getMail()
     *
     * @return string the message for the user, '' = the mail can go out
     */
    public static function getMailError(array $mail): string
    {
        if (isset($mail['scheduled_at']) && !MailManager_ProScheduled_Model::isValidTime($mail['scheduled_at'])) {
            return vtranslate('LBL_SCHEDULE_INVALID', 'MailManager');
        }

        if (!$mail['to'] || '' === $mail['subject'] || '' === trim(strip_tags($mail['body']))) {
            return vtranslate('LBL_FILL_REQUIRED', 'MailManager');
        }

        return '';
    }

    /**
     * Sends the mail and records it everywhere it belongs
     *
     * @param array $mailbox df_mailmanager_mailbox row
     * @param array $mail see the class docblock
     * @return array ['success' => true, 'append_warning' => string, 'filed_on_records' => bool]
     * @throws Exception on an attachment upload error or a send failure
     */
    public static function send(array $mailbox, array $mail)
    {
        $mailbox = self::withSenderName($mailbox);
        $to = $mail['to'];
        $cc = $mail['cc'];
        $bcc = $mail['bcc'] ?? [];
        $subject = $mail['subject'];
        // the user's paragraphs keep their gaps in every mail program
        $body = MailManager_ProMailText_Model::spaceParagraphs($mail['body']);

        // Threading headers from the replied message
        $threadId = self::resolveThreadId($mailbox, (int)$mail['thread']);
        [$inReplyTo, $references] = self::threadingHeaders((int)$mail['reply_message'], $threadId);

        $attachments = MailManager_ProOutgoingAttachment_Model::collectForwardAttachments(
            $mailbox,
            (string)$mail['forward_attachments'],
            $threadId
        );

        // Files picked in the composer (multipart upload) and the Documents of an
        // EMAIL Maker template
        $attachments = array_merge(
            $attachments,
            MailManager_ProOutgoingAttachment_Model::collectUploadedAttachments($mail['uploads']),
            MailManager_ProOutgoingAttachment_Model::collectDocumentAttachments((string)($mail['document_attachments'] ?? ''))
        );

        // The composer editor holds only the reply — the quoted original is
        // appended here so the recipient gets the standard mail history
        $ownBody = $body;
        $quoteMessageId = (int)$mail['quote_message'];
        if ($quoteMessageId) {
            $body = self::appendQuote(
                $body,
                MailManager_ProComposer_Model::getQuoteBlockForMessage($quoteMessageId, (int)$mailbox['mailboxid'])
            );
        }

        $relatedTo = self::resolveRelatedRecord($threadId, (array)($mail['to_records'] ?? []));
        $emailRecord = self::saveEmailRecord($mailbox, $to, $cc, $bcc, $subject, $body, $attachments, $relatedTo);
        $context = [
            'thread' => $threadId,
            'in_reply_to' => $inReplyTo,
            'references' => $references,
            'to' => $to,
            'cc' => $cc,
            'bcc' => $bcc,
            'subject' => $subject,
            'body' => $body,
            'own_body' => $ownBody,
            'has_attachments' => !empty($attachments),
            'related_to' => $relatedTo,
            'compose_mode' => (string)$mail['compose_mode'],
            'reply_message' => (int)$mail['reply_message'],
            'quote_message' => (int)$mail['quote_message'],
            'draft_message' => (int)$mail['draft_message'],
            'mark_done' => !empty($mail['mark_done']),
        ];

        // "Send later" — the saved record waits for the scheduled sending (cron)
        if (!empty($mail['scheduled_at'])) {
            MailManager_ProScheduled_Model::add(
                (int)$mailbox['mailboxid'],
                (int)Users_Record_Model::getCurrentUserModel()->getId(),
                (int)$emailRecord->getId(),
                (string)$mail['scheduled_at'],
                $context
            );

            return ['success' => true, 'scheduled' => true, 'crm_emailid' => (int)$emailRecord->getId()];
        }

        return self::deliver($mailbox, (int)$emailRecord->getId(), $context);
    }

    /**
     * Sends a saved ITS4YouEmails record and does what follows a sent mail: the Sent
     * copy on the server and in the cache, the answered flag, the consumed draft, the
     * conversation state, the history and the ticket comment. The direct sending and
     * the scheduled one (MailManager_ProScheduled_Model) take the same way.
     *
     * @param array $mailbox df_mailmanager_mailbox row
     * @param int   $emailId the SAVED ITS4YouEmails record
     * @param array $context what send() collected (threading, recipients, flags)
     *
     * @return array ['success', 'append_warning', 'filed_on_records', 'crm_emailid']
     * @throws Exception when sending failed
     */
    public static function deliver(array $mailbox, int $emailId, array $context): array
    {
        $mailbox = self::withSenderName($mailbox);
        $threadId = (int)$context['thread'];
        $inReplyTo = (string)$context['in_reply_to'];
        $references = (string)$context['references'];
        $to = (array)$context['to'];
        $cc = (array)$context['cc'];
        $bcc = (array)$context['bcc'];
        $subject = (string)$context['subject'];
        $body = (string)$context['body'];
        $ownBody = (string)$context['own_body'];
        $relatedTo = (int)$context['related_to'];
        $mail = [
            'compose_mode' => (string)$context['compose_mode'],
            'reply_message' => (int)$context['reply_message'],
            'quote_message' => (int)$context['quote_message'],
            'draft_message' => (int)$context['draft_message'],
            'mark_done' => !empty($context['mark_done']),
        ];
        $emailRecord = self::deliverEmailRecord($mailbox, $emailId, $inReplyTo, $references);
        $mailer = $emailRecord->getMailer();
        $rawMessage = (string)$mailer->getMailString();
        $messageId = (string)($mailer->getLastMessageID() ?: $mailer->MessageID);

        $appendWarning = self::storeOnServer($mailbox, $rawMessage, 'sent', '\\Seen');
        $cachedId = self::storeInCache($mailbox, $threadId, $messageId, $inReplyTo, $references, $to, $cc, $subject, $body,
            strlen($rawMessage), !empty($context['has_attachments']), false, $bcc);

        // the cached copy knows its CRM e-mail record (no second filing)
        PearDatabase::getInstance()->pquery(
            'UPDATE df_mailmanager_message SET crm_emailid = ?, mail_message_key = ? WHERE messageid = ?',
            [(int)$emailRecord->getId(), MailManager_ProMimeParser_Helper::getMessageKey($messageId), $cachedId]
        );

        // the answered / forwarded mail gets its flag on the server — the
        // other mail clients of the mailbox (Thunderbird, Outlook, a phone)
        // show the reply arrow on it
        self::flagAnswered(
            $mailbox,
            (int)$mail['reply_message'] ?: (int)$mail['quote_message'],
            $mail['compose_mode'] === 'forward'
        );

        // Sending a continued draft consumes it — server and cache copy
        self::deleteDraft($mailbox, (int)$mail['draft_message']);

        // whoever answers a conversation nobody has takes it; "Send and mark
        // done" closes it with the same click
        MailManager_ProThreadState_Model::onReply(
            self::getMessageThreadId($cachedId),
            (int)Users_Record_Model::getCurrentUserModel()->getId(),
            !empty($mail['mark_done'])
        );

        // who sent what — a deputy's replies included
        MailManager_ProHistory_Model::logThread(
            self::getMessageThreadId($cachedId),
            MailManager_ProHistory_Model::ACTION_SEND,
            MailManager_ProHistory_Model::SOURCE_USER,
            null,
            implode(', ', array_merge($to, $cc, $bcc))
        );

        // Every outgoing mail of a ticket-linked thread leaves a trace on the
        // ticket as a comment: a reply logs the OWN text, a forward the full
        // forwarded content (subject-prefix fallback covers forwards
        // continued from a draft)
        if ($threadId) {
            $isForward = $mail['compose_mode'] === 'forward' || stripos($subject, 'fwd:') === 0;
            MailManager_ProCrmLog_Model::commentOutgoingMail(
                $threadId,
                $to,
                $cc,
                $subject,
                $isForward ? $body : $ownBody,
                $isForward
            );
        }

        return [
            'success' => true,
            'append_warning' => $appendWarning,
            'filed_on_records' => $relatedTo > 0,
            'crm_emailid' => (int)$emailRecord->getId(),
        ];
    }

    /**
     * Builds and saves the ITS4YouEmails record exactly like the ITS4YouEmails
     * composer does (07-SENDING.md steps 1–3)
     *
     * @param array $mailbox df_mailmanager_mailbox row (sender name resolved)
     * @param string[] $to
     * @param string[] $cc
     * @param string[] $bcc
     * @param string $subject
     * @param string $body HTML incl. the quote
     * @param array $attachments of ['content', 'filename', 'mime_type']
     * @param int $relatedTo CRM record the mail belongs to, 0 = none
     * @return ITS4YouEmails_Record_Model the SAVED record with its attachments
     * @throws Exception
     */
    private static function saveEmailRecord(array $mailbox, array $to, array $cc, array $bcc, $subject, $body, array $attachments, $relatedTo)
    {
        $moduleName = 'ITS4YouEmails';
        $email = (string)$mailbox['email'];
        $name = decode_html((string)$mailbox['name']);
        $isCustomSmtp = ($mailbox['smtp_mode'] ?? 'crm') === 'custom' && (string)$mailbox['smtp_server'] !== '';
        $emailIds = function (array $addresses) {
            return implode(',', array_map(function ($address) {
                return 'email|' . $address . '|';
            }, $addresses));
        };

        // The SMTP host is checked by MailManager_ProHostGuard_Model when the mailbox settings
        // are saved (owner decision): an internal server configured by the administrator stays
        // usable for every member of a shared mailbox when sending.

        /** @var ITS4YouEmails_Record_Model $record */
        $record = Vtiger_Record_Model::getCleanInstance($moduleName);
        $record->set('assigned_user_id', (int)Users_Record_Model::getCurrentUserModel()->getId());
        $record->set('subject', $subject);
        $record->set('body', $body);
        $record->set('email_flag', ITS4YouEmails_Record_Model::$FLAG_SAVED);
        $record->set('from_email', $email);
        $record->set('from_name', $name);
        $record->set('from_email_ids', 'email|' . $email . '|' . $name);
        $record->set('to_email', implode(',', $to));
        $record->set('to_email_ids', $emailIds($to));

        if ($cc) {
            $record->set('cc_email', implode(',', $cc));
            $record->set('cc_email_ids', $emailIds($cc));
        }

        if ($bcc) {
            $record->set('bcc_email', implode(',', $bcc));
            $record->set('bcc_email_ids', $emailIds($bcc));
        }

        if (!$isCustomSmtp) {
            // the CRM outgoing server signs in as ONE account and the mail
            // server rejects any other From — the answers still come back here
            $record->set('reply_email', $email);
            $record->set('reply_email_ids', 'email|' . $email . '|' . $name);
        }

        if ($relatedTo) {
            $record->set('related_to', $relatedTo);
        }

        $record->save();

        foreach ($attachments as $attachment) {
            self::saveEmailAttachment($record, $attachment);
        }

        return $record;
    }

    /**
     * Sends a saved ITS4YouEmails record (07-SENDING.md steps 4–5): threading
     * headers, the mailbox's own SMTP server, then ITS4YouEmails send()
     *
     * @param array $mailbox df_mailmanager_mailbox row (sender name resolved)
     * @param int $emailId the SAVED record
     * @param string $inReplyTo
     * @param string $references
     * @return ITS4YouEmails_Record_Model the sent record (its mailer holds the MIME)
     * @throws Exception when sending failed
     */
    private static function deliverEmailRecord(array $mailbox, int $emailId, $inReplyTo, $references)
    {
        $moduleName = 'ITS4YouEmails';
        $email = (string)$mailbox['email'];
        $name = decode_html((string)$mailbox['name']);
        $isCustomSmtp = ($mailbox['smtp_mode'] ?? 'crm') === 'custom' && (string)$mailbox['smtp_server'] !== '';

        /** @var ITS4YouEmails_Record_Model $record */
        $record = Vtiger_Record_Model::getInstanceById($emailId, $moduleName);
        $mailer = $record->getMailer();

        // Pro stores the Sent copy itself, in the mailbox the user sent from
        $record->set('skip_save_email_to_sent_folder', true);

        // Threading of the answered message. Hook H1 (X01) makes
        // retrieveInReplyTo() add nothing of its own; until X01 is merged
        // the flag has no effect – the 'email|…|' recipients already make
        // retrieveInReplyTo() return early
        $record->set('skip_in_reply_to', true);
        $domain = (string)substr((string)strstr($email, '@'), 1) ?: 'crm.local';
        $mailer->MessageID = '<mmpro.' . str_replace('.', '', uniqid('', true)) . '@' . $domain . '>';

        if ($inReplyTo !== '') {
            $mailer->addCustomHeader('In-Reply-To', $inReplyTo);
            $mailer->addCustomHeader('References', $references);
        }

        if ($isCustomSmtp) {
            require_once 'include/utils/encryption.php';

            $encryption = new Encryption();
            $smtpEncryption = (string)$mailbox['smtp_encryption'];
            $smtpSecure = 'none' === $smtpEncryption ? '' : ('ssl' === $smtpEncryption ? 'ssl' : 'tls');
            $username = (string)$mailbox['smtp_username'];

            $mailer->setSMTP(
                (string)$mailbox['smtp_server'],
                $username,
                $encryption->decrypt((string)$mailbox['smtp_password']),
                '' !== trim($username),
                $smtpSecure,
                (int)$mailbox['smtp_port'] ?: ('ssl' === $smtpSecure ? 465 : 587)
            );
            // the account owns the mailbox address — the mail goes out as the mailbox
            $mailer->setFrom($email, $name);
            // Hook H2 (X01): send() skips its own SMTP selection. Until X01 is
            // merged this flag has no effect and the CRM outgoing server is used
            $record->set('skip_smtp_retrieval', true);
        }

        if (!$record->send()) {
            throw new Exception((string)$record->get('result') ?: vtranslate('LBL_PRO_SEND_FAILED', 'MailManager'));
        }

        return $record;
    }

    /**
     * Stores one outgoing file as an attachment of the e-mail record —
     * ITS4YouEmails_Record_Model::send() attaches it from there and the CRM
     * record shows what was sent
     *
     * @param ITS4YouEmails_Record_Model $record saved record
     * @param array $attachment ['content', 'filename', 'mime_type']
     * @return void
     * @throws Exception when the file cannot be written
     */
    private static function saveEmailAttachment($record, array $attachment)
    {
        $fileName = MailManager_ProOutgoingAttachment_Model::cleanFileName($attachment['filename']);
        $adb = PearDatabase::getInstance();

        $attachmentModel = ITS4YouEmails_Attachment_Model::getInstance('ITS4YouEmails');
        $attachmentModel->setId((int)$adb->getUniqueID('vtiger_crmentity'));
        $attachmentModel->setName($fileName);
        $attachmentModel->setDescription($fileName);
        // the stored file name passes the upload sanitizer, the sent name stays as it was
        $attachmentModel->setStoredName(sanitizeUploadFileName($fileName, vglobal('upload_badext')));
        $attachmentModel->setPath(decideFilePath());
        $attachmentModel->setType($attachment['mime_type'] ?: 'application/octet-stream');
        $attachmentModel->saveFile($attachment['content']);
        $attachmentModel->validateSaveFile();
        $attachmentModel->save();

        $record->saveAttachmentRelation($attachmentModel->getId());
    }

    /**
     * The CRM record the sent mail belongs to: the conversation's linked end
     * record, otherwise a record picked as recipient in the composer
     *
     * @param int $threadId 0 = new conversation
     * @param array $recordIds crmids picked in the composer
     * @return int crmid, 0 = none
     */
    private static function resolveRelatedRecord($threadId, array $recordIds)
    {
        $adb = PearDatabase::getInstance();
        $modules = self::RELATED_MODULES;

        if ($threadId) {
            $result = $adb->pquery(
                'SELECT r.crmid, ce.setype FROM df_mailmanager_rel r
                 INNER JOIN vtiger_crmentity ce ON ce.crmid = r.crmid AND ce.deleted = 0
                 WHERE r.threadid = ? AND ce.setype IN (' . generateQuestionMarks($modules) . ')
                 ORDER BY FIELD(ce.setype, ' . generateQuestionMarks($modules) . '), r.relid DESC',
                array_merge([$threadId], $modules, $modules)
            );

            while ($row = $adb->fetchByAssoc($result)) {
                if (Users_Privileges_Model::isPermitted($row['setype'], 'DetailView', (int)$row['crmid'])) {
                    return (int)$row['crmid'];
                }
            }
        }

        $recordIds = array_values(array_filter(array_unique(array_map('intval', $recordIds))));

        if (!$recordIds) {
            return 0;
        }

        $result = $adb->pquery(
            'SELECT crmid, setype FROM vtiger_crmentity
             WHERE deleted = 0 AND crmid IN (' . generateQuestionMarks($recordIds) . ')
               AND setype IN (' . generateQuestionMarks($modules) . ')
             ORDER BY FIELD(setype, ' . generateQuestionMarks($modules) . ')',
            array_merge($recordIds, $modules, $modules)
        );

        while ($row = $adb->fetchByAssoc($result)) {
            if (Users_Privileges_Model::isPermitted($row['setype'], 'DetailView', (int)$row['crmid'])) {
                return (int)$row['crmid'];
            }
        }

        return 0;
    }

    /**
     * Saves the composer content as a draft: MIME into the IMAP Drafts folder
     * (\Draft flag — visible in Thunderbird/mobile too) + an immediate cache
     * row. Re-saving replaces the previous version (IMAP drafts are
     * immutable — update = delete + append). No ITS4YouEmails record
     *
     * @param array $mailbox df_mailmanager_mailbox row
     * @param array $mail see the class docblock (uploads are not kept; the
     *                    forwarded attachments are remembered by id)
     * @return array ['success' => true, 'draft_message' => int, 'append_warning' => string]
     */
    public static function saveDraft(array $mailbox, array $mail)
    {
        $mailbox = self::withSenderName($mailbox);
        $to = $mail['to'];
        $cc = $mail['cc'];
        $bcc = $mail['bcc'] ?? [];
        $subject = $mail['subject'];
        $body = $mail['body'];

        $threadId = self::resolveThreadId($mailbox, (int)$mail['thread']);
        [$inReplyTo, $references] = self::threadingHeaders((int)$mail['reply_message'], $threadId);

        // The previous version of this draft is replaced
        self::deleteDraft($mailbox, (int)$mail['draft_message']);

        // PHPMailer refuses a message without recipients — the own address
        // stands in for the MIME copy only; the cache keeps the real (empty)
        // recipients and the reopened composer shows them as the user left them
        $mimeTo = $to ?: [$mailbox['email']];
        // The server copy carries the quoted / forwarded mail like the sent
        // one will (another client continuing the draft sees it); the cache
        // keeps the own text only — the composer shows the quote apart
        $quoteMessageId = (int)$mail['quote_message'];
        $mimeBody = $body;
        if ($quoteMessageId) {
            $mimeBody = self::appendQuote($mimeBody, MailManager_ProComposer_Model::getQuoteBlockForMessage(
                $quoteMessageId,
                (int)$mailbox['mailboxid']
            ));
        }
        $messageId = '';
        $appendWarning = '';

        try {
            $rawMessage = self::buildDraftMime($mailbox, $mimeTo, $cc, $bcc, $subject, $mimeBody, $inReplyTo, $references, $messageId);
            $appendWarning = self::storeOnServer($mailbox, $rawMessage, 'drafts', '\\Draft \\Seen');
        } catch (Exception $e) {
            $appendWarning = $e->getMessage();
        }

        $draftId = self::storeInCache($mailbox, $threadId, $messageId, $inReplyTo, $references, $to, $cc, $subject,
            $body, strlen($body), false, true, $bcc);

        // A forward has no In-Reply-To — the reopened composer finds the
        // forwarded mail and the picked attachments here
        PearDatabase::getInstance()->pquery(
            'UPDATE df_mailmanager_message SET draft_context = ? WHERE messageid = ?',
            [
                json_encode([
                    'quote_message' => $quoteMessageId,
                    'forward_attachments' => array_values(array_filter(
                        array_map('intval', explode(',', (string)$mail['forward_attachments']))
                    )),
                ]),
                $draftId,
            ]
        );

        return ['success' => true, 'draft_message' => $draftId, 'append_warning' => $appendWarning];
    }

    /**
     * MIME source of a draft — built with the Defalto mailer class, never sent
     *
     * @param array $mailbox
     * @param string[] $to
     * @param string[] $cc
     * @param string[] $bcc
     * @param string $subject
     * @param string $body HTML
     * @param string $inReplyTo
     * @param string $references
     * @param string $messageId set to the generated Message-ID
     * @return string raw MIME
     * @throws Exception when the MIME cannot be built
     */
    private static function buildDraftMime(array $mailbox, array $to, array $cc, array $bcc, $subject, $body, $inReplyTo, $references, &$messageId)
    {
        $mailer = ITS4YouEmails_Mailer_Model::getCleanInstance();
        $mailer->setFrom((string)$mailbox['email'], decode_html((string)$mailbox['name']));
        $mailer->isHTML(true);
        $mailer->Subject = $subject;
        $mailer->Body = $body;
        $mailer->AltBody = trim(strip_tags($body));

        foreach ($to as $address) {
            $mailer->addAddress($address);
        }
        foreach ($cc as $address) {
            $mailer->addCC($address);
        }
        foreach ($bcc as $address) {
            $mailer->addBCC($address);
        }

        $domain = (string)substr((string)strstr((string)$mailbox['email'], '@'), 1) ?: 'crm.local';
        $messageId = '<mmpro.' . str_replace('.', '', uniqid('', true)) . '@' . $domain . '>';
        $mailer->MessageID = $messageId;

        if ($inReplyTo !== '') {
            $mailer->addCustomHeader('In-Reply-To', $inReplyTo);
            $mailer->addCustomHeader('References', $references);
        }

        if (!$mailer->preSend()) {
            throw new Exception($mailer->ErrorInfo ?: 'Draft MIME build failed');
        }

        return $mailer->getSentMIMEMessage();
    }

    /**
     * In-Reply-To / References headers of the replied cached message
     *
     * @param int $replyMessageId
     * @param int $threadId
     * @return array [inReplyTo, references]
     */
    public static function threadingHeaders($replyMessageId, $threadId)
    {
        if (!$replyMessageId || !$threadId) {
            return ['', ''];
        }
        $adb = PearDatabase::getInstance();
        $replyResult = $adb->pquery(
            'SELECT header_message_id, header_references FROM df_mailmanager_message WHERE messageid = ? AND threadid = ?',
            [$replyMessageId, $threadId]
        );
        if (!$adb->num_rows($replyResult)) {
            return ['', ''];
        }
        $replyRow = $adb->raw_query_result_rowdata($replyResult, 0);
        $inReplyTo = (string)$replyRow['header_message_id'];

        return [$inReplyTo, self::limitReferences(trim($replyRow['header_references'] . ' ' . $inReplyTo))];
    }

    /**
     * Keeps the References header short — the thread root plus the newest
     * ids. Every reply appends one id; past ~980 characters PHPMailer
     * encodes the header and receiving clients stop threading the mail
     *
     * @param string $references space separated message ids, oldest first
     * @return string
     */
    private static function limitReferences($references)
    {
        $ids = array_values(array_unique(preg_split('/\s+/', trim($references), -1, PREG_SPLIT_NO_EMPTY)));
        if (count($ids) > self::MAX_REFERENCES) {
            $ids = array_merge([$ids[0]], array_slice($ids, -(self::MAX_REFERENCES - 1)));
        }

        return implode(' ', $ids);
    }

    /**
     * Removes a draft version — the IMAP copy in Drafts and the cached row
     * (best-effort; a failed server delete leaves an orphan the user can
     * still delete manually in any mail client)
     *
     * @param array $mailbox encrypted row
     * @param int $draftId cached messageid of the draft
     * @return void
     */
    public static function deleteDraft(array $mailbox, $draftId)
    {
        if (!$draftId) {
            return;
        }
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            'SELECT messageid, threadid, folderid, remote_uid, header_message_id FROM df_mailmanager_message
             WHERE messageid = ? AND mailboxid = ? AND is_draft = 1',
            [$draftId, $mailbox['mailboxid']]
        );
        if (!$adb->num_rows($result)) {
            return;
        }
        $draft = $adb->raw_query_result_rowdata($result, 0);

        $transport = MailManager_ProTransport_Model::getForMailbox($mailbox);
        if ($transport->supports(MailManager_ProBaseTransport_Helper::CAP_MANAGE)) {
            try {
                $folderResult = $adb->pquery('SELECT * FROM df_mailmanager_folder WHERE folderid = ?', [(int)$draft['folderid']]);
                if ($adb->num_rows($folderResult)) {
                    $folder = $adb->raw_query_result_rowdata($folderResult, 0);
                    // A draft saved by the CRM carries no UID until a sync of
                    // the Drafts folder adopts it — the server copy is then
                    // found by its Message-ID, otherwise every re-save left
                    // one more stale draft on the server
                    $uids = $draft['remote_uid'] !== null
                        ? [(int)$draft['remote_uid']]
                        : $transport->findMessageUids($folder, (string)$draft['header_message_id']);
                    if ($uids) {
                        $transport->deleteMessages($folder, $uids);
                    }
                }
            } catch (Exception $e) {
                // best-effort — see the docblock
            } finally {
                $transport->close();
            }
        }

        $adb->pquery('DELETE FROM df_mailmanager_message WHERE messageid = ?', [(int)$draft['messageid']]);
        $adb->pquery(
            'UPDATE df_mailmanager_thread t
             SET t.message_count = (SELECT COUNT(*) FROM df_mailmanager_message m WHERE m.threadid = t.threadid)
             WHERE t.threadid = ?',
            [(int)$draft['threadid']]
        );
    }

    /**
     * The thread a reply/draft belongs to — only when it is a thread of this
     * mailbox; 0 starts a new conversation
     *
     * @param array $mailbox
     * @param int $requestThreadId thread id sent by the composer
     * @return int
     */
    private static function resolveThreadId(array $mailbox, $requestThreadId)
    {
        if (!$requestThreadId) {
            return 0;
        }
        $thread = MailManager_ProInbox_Model::getThread($requestThreadId, (int)$mailbox['mailboxid']);

        return $thread ? (int)$thread['threadid'] : 0;
    }

    /**
     * Best-effort MIME copy on the mail server — the Sent copy (other
     * clients see the reply) or the draft with the \Draft flag
     *
     * @param array $mailbox df_mailmanager_mailbox row
     * @param string $rawMessage
     * @param string $folderType 'sent' | 'drafts'
     * @param string $flags server flags of the stored copy
     * @return string warning text ('' when stored fine)
     */
    private static function storeOnServer(array $mailbox, $rawMessage, $folderType, $flags)
    {
        $transport = MailManager_ProTransport_Model::getForMailbox($mailbox);
        if (!$transport->supports(MailManager_ProBaseTransport_Helper::CAP_APPEND)) {
            return '';
        }

        $adb = PearDatabase::getInstance();
        $folderResult = $adb->pquery(
            'SELECT * FROM df_mailmanager_folder WHERE mailboxid = ? AND folder_type = ? LIMIT 1',
            [$mailbox['mailboxid'], $folderType]
        );
        if (!$adb->num_rows($folderResult)) {
            return 'no_' . $folderType . '_folder';
        }

        try {
            $transport->appendMessage($adb->raw_query_result_rowdata($folderResult, 0), $rawMessage, $flags);

            return '';
        } catch (Exception $e) {
            return $e->getMessage();
        } finally {
            $transport->close();
        }
    }

    /**
     * Sets \Answered ($Forwarded for a forward) on the message a sent mail
     * reacts to. Best effort — the mail has left, a flag that cannot be set
     * (server unreachable, keyword not kept) is nothing to report
     *
     * @param array $mailbox df_mailmanager_mailbox row
     * @param int $messageId cached message that was answered / forwarded
     * @param bool $forwarded
     * @return void
     */
    private static function flagAnswered(array $mailbox, $messageId, $forwarded)
    {
        if (!$messageId) {
            return;
        }
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            'SELECT f.*, m.remote_uid FROM df_mailmanager_message m
             INNER JOIN df_mailmanager_folder f ON f.folderid = m.folderid
             WHERE m.messageid = ? AND m.mailboxid = ? AND m.remote_uid IS NOT NULL',
            [$messageId, $mailbox['mailboxid']]
        );
        if (!$adb->num_rows($result)) {
            return;
        }
        $folder = $adb->raw_query_result_rowdata($result, 0);

        $transport = MailManager_ProTransport_Model::getForMailbox($mailbox);
        if (!$transport->supports(MailManager_ProBaseTransport_Helper::CAP_MANAGE)) {
            return;
        }

        try {
            $transport->markAnswered($folder, [(int)$folder['remote_uid']], $forwarded);
        } catch (Exception $e) {
            // best effort only
        } finally {
            $transport->close();
        }
    }

    /**
     * Puts the quoted original under the reply — inside the reply's document
     * (the editor writes a whole <html>…</body>), not after its end
     *
     * @param string $body reply HTML
     * @param string $quote quote block
     * @return string
     */
    private static function appendQuote($body, $quote)
    {
        if ($quote === '') {
            return $body;
        }
        if (stripos($body, '</body>') !== false) {
            return preg_replace('~</body>~i', str_replace(['\\', '$'], ['\\\\', '\\$'], $quote) . '</body>', $body, 1);
        }

        return $body . $quote;
    }

    /**
     * The mailbox as the sender — a deputy writes from the owner's personal
     * mailbox under the own name
     *
     * @param array $mailbox df_mailmanager_mailbox row
     * @return array
     */
    private static function withSenderName(array $mailbox)
    {
        $mailbox['name'] = MailManager_ProSharing_Model::getSenderName(
            $mailbox,
            (int)Users_Record_Model::getCurrentUserModel()->getId()
        );

        return $mailbox;
    }

    /**
     * @param int $messageId cached message
     * @return int its conversation, 0 = none
     */
    private static function getMessageThreadId($messageId)
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery('SELECT threadid FROM df_mailmanager_message WHERE messageid = ?', [$messageId]);

        return $adb->num_rows($result) ? (int)$adb->query_result($result, 0, 'threadid') : 0;
    }

    /**
     * Immediate cache insert so the sent mail / draft shows in the thread
     * without waiting for the next sync; the next sync adopts the server copy
     * by its Message-ID
     *
     * @param array $mailbox
     * @param int $threadId 0 = new conversation
     * @param string $messageId generated Message-ID
     * @param string $inReplyTo
     * @param string $references
     * @param string[] $to real recipients (may be empty for a draft)
     * @param string[] $cc
     * @param string $subject
     * @param string $body HTML
     * @param int $sizeBytes
     * @param bool $hasAttachments
     * @param bool $isDraft cached in Drafts instead of Sent
     * @param string[] $bcc blind copies — kept in the cache only, the copy on
     *                      the server carries no header naming them
     * @return int cached messageid
     */
    private static function storeInCache(array $mailbox, $threadId, $messageId, $inReplyTo, $references, array $to, array $cc, $subject, $body, $sizeBytes, $hasAttachments, $isDraft, array $bcc = [])
    {
        $adb = PearDatabase::getInstance();
        $now = gmdate('Y-m-d H:i:s');

        if (!$threadId) {
            $adb->pquery(
                'INSERT INTO df_mailmanager_thread (mailboxid, thread_key, subject, last_message_at) VALUES (?,?,?,?)',
                [$mailbox['mailboxid'], mb_substr($messageId, 0, 255), mb_substr($subject, 0, 500), $now]
            );
            $threadId = (int)$adb->getLastInsertID();
        }

        // Cache location: the Sent / Drafts folder (fallback: any folder of the mailbox)
        $folderResult = $adb->pquery(
            'SELECT folderid FROM df_mailmanager_folder WHERE mailboxid = ? ORDER BY folder_type = ? DESC LIMIT 1',
            [$mailbox['mailboxid'], $isDraft ? 'drafts' : 'sent']
        );
        $folderId = $adb->num_rows($folderResult) ? (int)$adb->query_result($folderResult, 0, 'folderid') : 0;

        $bodyHtml = MailManager_ProMailText_Model::sanitizeHtml($body);
        $bodyPlain = trim(strip_tags($bodyHtml));
        $snippet = mb_substr(trim(preg_replace('/\s+/u', ' ', $bodyPlain)), 0, 200);
        $addressList = function (array $emails) {
            return json_encode(array_map(function ($email) {
                return ['email' => $email, 'name' => ''];
            }, $emails), JSON_UNESCAPED_UNICODE);
        };

        $adb->pquery(
            'INSERT INTO df_mailmanager_message
                (threadid, mailboxid, folderid, remote_uid, header_message_id, header_in_reply_to, header_references,
                 direction, is_draft, is_read, from_email, from_name, to_addrs, cc_addrs, bcc_addrs,
                 subject, body_html, body_plain, snippet, sent_at, size_bytes, has_attachments, source)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)',
            [
                $threadId, $mailbox['mailboxid'], $folderId, null, $messageId, $inReplyTo, $references,
                'out', $isDraft ? 1 : 0, 1, $mailbox['email'], mb_substr($mailbox['name'], 0, 150),
                $addressList($to), $addressList($cc), $addressList($bcc),
                mb_substr($subject, 0, 500), $bodyHtml, $bodyPlain, $snippet, $now, $sizeBytes, $hasAttachments ? 1 : 0, 'crm',
            ]
        );
        $cachedId = (int)$adb->getLastInsertID();

        MailManager_ProSync_Model::updateThreadAggregates($threadId, [
            'is_read' => 1,
            'attachments' => $hasAttachments ? [['is_inline' => 0]] : [],
            'sent_at' => $now,
        ], $snippet, 'out');

        return $cachedId;
    }

    /**
     * The draft to save from what the composer sent.
     *
     * @param array $input to, cc, bcc, subject and body (raw), thread, reply_message,
     *                     draft_message, compose_mode, quote_message, forward_attachments
     *
     * @return array the draft for saveDraft()
     */
    public static function getDraftMail(array $input): array
    {
        return [
            'to' => self::parseAddresses($input['to']),
            'cc' => self::parseAddresses($input['cc']),
            'bcc' => self::parseAddresses($input['bcc']),
            'subject' => trim(str_replace(["\r", "\n"], ' ', (string)$input['subject'])),
            'body' => MailManager_ProDraftFacts_Helper::stripMarks((string)$input['body']),
            'thread' => (int)$input['thread'],
            'reply_message' => (int)$input['reply_message'],
            'draft_message' => (int)$input['draft_message'],
            'compose_mode' => (string)$input['compose_mode'],
            'quote_message' => (int)$input['quote_message'],
            'forward_attachments' => (string)$input['forward_attachments'],
        ];
    }

    /**
     * A draft needs a subject or some text.
     *
     * @param array $mail draft of getDraftMail()
     *
     * @return bool
     */
    public static function isEmptyDraft(array $mail): bool
    {
        return '' === $mail['subject'] && '' === trim(strip_tags($mail['body']));
    }
}
