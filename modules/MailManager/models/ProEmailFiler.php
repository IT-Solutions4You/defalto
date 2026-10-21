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
 * Messages of linked conversations filed as ITS4YouEmails records — the same
 * records Basic MailManager creates (MailManager_Relate_Model::associate()),
 * carrying `mail_message_key` = md5(Message-ID), so Basic shows the mail as
 * linked even after the Pro edition expires (shared contract, N4/N15).
 *
 * New mail of a linked conversation goes to ONE end record — the case
 * (Project task, Ticket, Project, Opportunity, Lead) when the conversation has one,
 * otherwise the person, otherwise the company. Linking a conversation to a
 * record files its messages on that record.
 */
class MailManager_ProEmailFiler_Model
{
    /** End record priority — the most specific linked record wins */
    public const TARGET_MODULES = ['ProjectTask', 'HelpDesk', 'Project', 'Potentials', 'Leads', 'Contacts', 'Accounts'];

    /**
     * End records that do not collect the mail's files as their Documents.
     * None now: a project collects them too.
     */
    public const NO_DOCUMENT_MODULES = [];

    /**
     * A message is "new" when it is not older than the link minus this
     * tolerance (seconds) — covers the delay between sending and the sync.
     */
    public const LINK_TOLERANCE = 3600;

    /** Source marker of the ITS4YouEmails record, the value Basic MailManager uses */
    public const EMAIL_FLAG = 'MAIL MANAGER';

    /** The CRM module the messages are filed as */
    public const EMAIL_MODULE = 'ITS4YouEmails';

    /**
     * Whether new mail of linked conversations is filed automatically (the
     * AI settings switch "e-mail filing"). Linking itself always files.
     *
     * @return bool
     */
    public static function isEnabled(): bool
    {
        return MailManager_ProAiConfig_Model::get('ai_feature_emailfiling', '1') === '1'
            && vtlib_isModuleActive(self::EMAIL_MODULE);
    }

    /**
     * Files a freshly stored message as an ITS4YouEmails record on the end
     * record of its conversation; attachments of an inbound message go to the
     * same record as Documents. Idempotent (crm_emailid) and best-effort — a
     * failure never breaks the sync or the sending.
     *
     * @param int   $messageId   df_mailmanager_message id
     * @param array $fallbackIds crmids used when the conversation has no link yet (outgoing mail: the picked recipients)
     * @param array $files       files of an outgoing mail (['content', 'filename', 'mime_type']) attached to the e-mail record
     *
     * @return int ITS4YouEmails record id, 0 when nothing was filed
     */
    public static function fileMessage($messageId, array $fallbackIds = [], array $files = [])
    {
        if (!self::isEnabled()) {
            return 0;
        }

        try {
            $message = self::getMessage((int)$messageId);

            if (!$message || (int)$message['is_draft'] || self::isLiveEmailRecord((int)$message['crm_emailid'])) {
                return 0;
            }

            $target = self::getLinkedTarget((int)$message['threadid']);

            if ($target) {
                // mail from before the link is history — it stays in the inbox
                $sentAt = strtotime($message['sent_at'] . ' UTC');

                if ($sentAt < strtotime($target['linked_at']) - self::LINK_TOLERANCE) {
                    return 0;
                }
            } else {
                $target = self::pickTarget($fallbackIds);
            }

            if (!$target) {
                return 0;
            }

            return self::fileOnRecord($message, (int)$target['crmid'], $files);
        } catch (Throwable $e) {
            return 0;
        }
    }

    /**
     * Files the messages of a conversation on a record the conversation was
     * just linked to (N15): every message gets its ITS4YouEmails record with
     * `mail_message_key`, a message filed earlier is related to the record as
     * well. Only the newest inbound message brings its attachments along as
     * Documents — the files of the history stay in the inbox. Best-effort.
     *
     * @param int $threadId df_mailmanager_thread id
     * @param int $crmid    the linked record
     *
     * @return int number of messages filed or related on the record
     */
    public static function fileThreadOnRecord($threadId, $crmid): int
    {
        if (!vtlib_isModuleActive(self::EMAIL_MODULE)) {
            return 0;
        }

        $filed = 0;

        try {
            $messages = self::getThreadMessages((int)$threadId);
            $newestInboundId = 0;

            foreach ($messages as $message) {
                if ('in' === $message['direction']) {
                    $newestInboundId = (int)$message['messageid'];
                }
            }

            foreach ($messages as $message) {
                $emailId = (int)$message['crm_emailid'];

                if (self::isLiveEmailRecord($emailId)) {
                    // filed already (on the person or the company) — the same record shows here
                    // too, and on the project of a task
                    foreach (array_merge([(int)$crmid], self::getAlsoRecordIds((int)$crmid)) as $recordId) {
                        self::relateEmailRecord($emailId, $recordId);
                    }
                    $filed++;

                    continue;
                }

                $withDocuments = (int)$message['messageid'] === $newestInboundId;

                if (self::fileOnRecord($message, (int)$crmid, [], $withDocuments)) {
                    $filed++;
                }
            }
        } catch (Throwable $e) {
            // filing is an assist, the link itself already succeeded
        }

        return $filed;
    }

    /**
     * Shows an existing ITS4YouEmails record on one more record — the core
     * relation (vtiger_crmentityrel through relateEntities()), never
     * vtiger_seactivityrel.
     *
     * @param int $emailId ITS4YouEmails record id
     * @param int $crmid   record the e-mail is shown on
     *
     * @return void
     */
    private static function relateEmailRecord(int $emailId, int $crmid): void
    {
        $moduleName = getSalesEntityType($crmid);

        if (!$moduleName || !isRecordExists($crmid)) {
            return;
        }

        $focus = CRMEntity::getInstance($moduleName);
        $focus->id = $crmid;

        relateEntities($focus, $moduleName, $crmid, self::EMAIL_MODULE, $emailId);
    }

    /**
     * Files one message on a record — the ITS4YouEmails record (an existing
     * one with the same mail_message_key is reused, e.g. the one Basic made),
     * the message marker and, for inbound mail, the attachments as Documents
     * on the same record. The files of an outgoing mail belong to the e-mail
     * record only.
     *
     * @param array $message       message row + mailbox_email, owner_userid
     * @param int   $crmid         target record
     * @param array $files         files of an outgoing mail (['content', 'filename', 'mime_type'])
     * @param bool  $withDocuments inbound attachments are saved as Documents of the record
     *
     * @return int ITS4YouEmails record id, 0 when nothing was filed
     */
    private static function fileOnRecord(array $message, int $crmid, array $files = [], bool $withDocuments = true): int
    {
        $messageKey = self::getMessageKey($message);
        $emailId = '' !== $messageKey ? self::getEmailRecordIdByKey($messageKey) : 0;
        $isNew = !$emailId;

        if ($isNew) {
            $emailId = self::createEmailRecord($message, $crmid, $messageKey);
        }

        if (!$emailId) {
            return 0;
        }

        // the end record and the records that show its mail too (the project of a task) —
        // ONE e-mail record
        $recordIds = array_merge([$crmid], self::getAlsoRecordIds($crmid));

        foreach ($recordIds as $recordId) {
            self::relateEmailRecord($emailId, $recordId);
        }

        self::saveMessageEmailId((int)$message['messageid'], $emailId);

        if ($isNew && $files) {
            self::attachFilesToEmail($emailId, $files);
        }

        if ($withDocuments && 'in' === $message['direction'] && (int)$message['has_attachments']
            && !in_array(getSalesEntityType($crmid), self::NO_DOCUMENT_MODULES, true)) {
            $mailbox = self::getMailbox((int)$message['mailboxid']);

            // one document per attachment — the further records get it related
            foreach ($mailbox ? $recordIds : [] as $recordId) {
                MailManager_ProDocumentFiler_Model::saveThreadAttachmentsToRecord(
                    $mailbox,
                    (int)$message['threadid'],
                    $recordId,
                    (int)$message['owner_userid'],
                    (int)$message['messageid']
                );
            }
        }

        return $emailId;
    }

    /**
     * Records that show a filed mail besides its end record — the project of a
     * task: the task is the work, the project collects all of it
     *
     * @param int $crmid end record
     *
     * @return int[] live record ids
     */
    public static function getAlsoRecordIds(int $crmid): array
    {
        if ('ProjectTask' !== getSalesEntityType($crmid)) {
            return [];
        }

        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            'SELECT pt.projectid FROM vtiger_projecttask pt
             INNER JOIN vtiger_crmentity ce ON ce.crmid = pt.projectid AND ce.deleted = 0
             WHERE pt.projecttaskid = ?',
            [$crmid]
        );
        $projectId = $adb->num_rows($result) ? (int)$adb->query_result($result, 0, 'projectid') : 0;

        return $projectId ? [$projectId] : [];
    }

    /**
     * Files attached to an ITS4YouEmails record the Defalto way — a
     * Core_Attachment_Model of the e-mail module related through
     * vtiger_seattachmentsrel, so the e-mail detail lists and downloads them.
     *
     * @param int   $emailId ITS4YouEmails record id
     * @param array $files   of ['content', 'filename', 'mime_type']
     *
     * @return void
     * @throws Exception
     */
    private static function attachFilesToEmail(int $emailId, array $files): void
    {
        /** @var Documents_Record_Model $documentRecord */
        $documentRecord = Vtiger_Record_Model::getCleanInstance('Documents');

        foreach ($files as $file) {
            $content = (string)($file['content'] ?? '');

            if ('' === $content) {
                continue;
            }

            $attachment = Core_Attachment_Model::getInstance(self::EMAIL_MODULE);
            $attachment->retrieveDefault((string)($file['filename'] ?? '') ?: 'attachment');
            $attachment->setType((string)($file['mime_type'] ?? '') ?: 'application/octet-stream');
            $attachment->saveFile($content);

            if ($attachment->validateSaveFile()) {
                $attachment->save();
                $documentRecord->saveAttachmentsRelation($emailId, (int)$attachment->getId());
            }
        }
    }

    /**
     * CRM records carrying exactly these addresses — the e-mail lookup of
     * EMAIL Maker covers every module with an email field (the same source
     * as the composer recipient picker).
     *
     * @param array $emails e-mail addresses
     *
     * @return array crmids
     */
    public static function resolveRecipientRecords(array $emails)
    {
        if (!vtlib_isModuleActive('EMAILMaker')) {
            return [];
        }

        $crmIds = [];
        $moduleModel = Vtiger_Module_Model::getInstance('EMAILMaker');

        foreach ($emails as $email) {
            $email = strtolower(trim((string)$email));

            if ('' === $email) {
                continue;
            }

            try {
                $matches = $moduleModel->searchEmails($email);
            } catch (Exception $e) {
                continue;
            }

            foreach ($matches as $records) {
                foreach ($records as $crmId => $recordMatches) {
                    foreach ($recordMatches as $match) {
                        if (0 === strcasecmp((string)$match['value'], $email)) {
                            $crmIds[] = (int)$crmId;
                        }
                    }
                }
            }
        }

        return array_values(array_unique($crmIds));
    }

    /**
     * The record the conversation is about — the end record its mail is filed on
     * (EMAIL Maker templates fill their variables from it).
     *
     * @param int $threadId df_mailmanager_thread id
     *
     * @return array|null ['crmid', 'module', 'linked_at']
     */
    public static function getEndRecord(int $threadId): ?array
    {
        return self::getLinkedTarget($threadId);
    }

    /**
     * The end record among the conversation's links, in TARGET_MODULES order.
     *
     * @param int $threadId df_mailmanager_thread id
     *
     * @return array|null ['crmid', 'module', 'linked_at']
     */
    private static function getLinkedTarget(int $threadId): ?array
    {
        $adb = PearDatabase::getInstance();
        $modules = self::TARGET_MODULES;
        $result = $adb->pquery(
            'SELECT r.crmid, ce.setype, r.createdtime FROM df_mailmanager_rel r
             INNER JOIN vtiger_crmentity ce ON ce.crmid = r.crmid AND ce.deleted = 0
             WHERE r.threadid = ? AND ce.setype IN (' . generateQuestionMarks($modules) . ')
             ORDER BY FIELD(ce.setype, ' . generateQuestionMarks($modules) . '), r.relid DESC LIMIT 1',
            array_merge([$threadId], $modules, $modules)
        );

        if (!$adb->num_rows($result)) {
            return null;
        }

        $row = $adb->raw_query_result_rowdata($result, 0);

        return ['crmid' => (int)$row['crmid'], 'module' => $row['setype'], 'linked_at' => (string)$row['createdtime']];
    }

    /**
     * The end record among loose candidates (recipients of a mail whose
     * conversation has no link yet) — live, visible, in TARGET_MODULES order.
     *
     * @param array $crmIds candidate record ids
     *
     * @return array|null ['crmid', 'module']
     */
    private static function pickTarget(array $crmIds): ?array
    {
        $crmIds = array_values(array_filter(array_unique(array_map('intval', $crmIds))));

        if (!$crmIds) {
            return null;
        }

        $adb = PearDatabase::getInstance();
        $modules = self::TARGET_MODULES;
        $result = $adb->pquery(
            'SELECT crmid, setype FROM vtiger_crmentity
             WHERE deleted = 0 AND crmid IN (' . generateQuestionMarks($crmIds) . ')
               AND setype IN (' . generateQuestionMarks($modules) . ')
             ORDER BY FIELD(setype, ' . generateQuestionMarks($modules) . ')',
            array_merge($crmIds, $modules, $modules)
        );

        while ($row = $adb->fetchByAssoc($result)) {
            if (Users_Privileges_Model::isPermitted($row['setype'], 'DetailView', (int)$row['crmid'])) {
                return ['crmid' => (int)$row['crmid'], 'module' => $row['setype']];
            }
        }

        return null;
    }

    /**
     * Creates the ITS4YouEmails record of a message exactly like Basic does
     * (Settings_MailConverter_MailScannerAction_Handler::createNewEmail()):
     * subject, body, addresses, `related_to` = the target, the person /
     * company / lead / vendor field of the target and `mail_message_key`.
     *
     * @param array  $message    message row + mailbox_email, owner_userid
     * @param int    $crmid      target record
     * @param string $messageKey md5 of the Message-ID ('' when the mail has none)
     *
     * @return int ITS4YouEmails record id, 0 when the user may not create e-mails
     * @throws Exception
     */
    private static function createEmailRecord(array $message, int $crmid, string $messageKey): int
    {
        if (!Users_Privileges_Model::isPermitted(self::EMAIL_MODULE, 'CreateView')) {
            return 0;
        }

        $addresses = static function ($json): string {
            return implode(',', array_filter(array_column(json_decode((string)$json, true) ?: [], 'email')));
        };
        // the e-mail detail shows the body outside any sandbox — mail HTML goes
        // through the HTML Purifier first; a body it empties falls back to the text
        $body = MailManager_ProMailText_Model::purifyHtml((string)$message['body_html']);

        if ('' === trim(strip_tags($body, '<img>'))) {
            $body = nl2br(htmlspecialchars((string)$message['body_plain'], ENT_QUOTES, 'UTF-8'));
        }

        $from = 'out' === $message['direction'] ? (string)$message['mailbox_email'] : (string)$message['from_email'];

        $recordModel = Vtiger_Record_Model::getCleanInstance(self::EMAIL_MODULE);
        // core columns may be utf8mb3 — an emoji would cut the value off
        $recordModel->set('subject', MailManager_ProMailText_Model::toCoreCharset((string)$message['subject']));
        $recordModel->set('body', MailManager_ProMailText_Model::toCoreCharset($body, true));
        $recordModel->set('assigned_user_id', (int)$message['owner_userid'] ?: (int)Users_Record_Model::getCurrentUserModel()->getId());
        $recordModel->set('email_flag', self::EMAIL_FLAG);
        $recordModel->set('from_email', $from);
        $recordModel->set('to_email', $addresses($message['to_addrs']));
        $recordModel->set('cc_email', $addresses($message['cc_addrs']));
        $recordModel->set('bcc_email', $addresses($message['bcc_addrs'] ?? '[]'));
        $recordModel->set('related_to', $crmid);

        $relationField = MailManager_Message_Model::RELATIONS_MAPPING[self::EMAIL_MODULE][getSalesEntityType($crmid)] ?? '';

        if ('' !== $relationField) {
            $recordModel->set($relationField, $crmid);
        }

        if ('' !== $messageKey) {
            $recordModel->set('mail_message_key', $messageKey);
        }

        $recordModel->save();

        return (int)$recordModel->getId();
    }

    /**
     * The CRM link key of a message — the stored mail_message_key, otherwise
     * computed from the Message-ID the same way the sync does.
     *
     * @param array $message message row
     *
     * @return string md5 of the Message-ID, '' when the mail has none
     */
    private static function getMessageKey(array $message): string
    {
        $key = trim((string)($message['mail_message_key'] ?? ''));

        if ('' !== $key) {
            return $key;
        }

        return MailManager_ProMimeParser_Helper::getMessageKey((string)($message['header_message_id'] ?? ''));
    }

    /**
     * Loads one message with its mailbox address and owner.
     *
     * @param int $messageId df_mailmanager_message id
     *
     * @return array|null message row + mailbox_email, owner_userid
     */
    private static function getMessage(int $messageId): ?array
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            'SELECT m.*, mb.email AS mailbox_email, mb.owner_userid FROM df_mailmanager_message m
             INNER JOIN df_mailmanager_mailbox mb ON mb.mailboxid = m.mailboxid
             WHERE m.messageid = ?',
            [$messageId]
        );

        return $adb->num_rows($result) ? $adb->raw_query_result_rowdata($result, 0) : null;
    }

    /**
     * Loads the non-draft messages of a conversation, oldest first.
     *
     * @param int $threadId df_mailmanager_thread id
     *
     * @return array of message rows + mailbox_email, owner_userid
     */
    private static function getThreadMessages(int $threadId): array
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            'SELECT m.*, mb.email AS mailbox_email, mb.owner_userid FROM df_mailmanager_message m
             INNER JOIN df_mailmanager_mailbox mb ON mb.mailboxid = m.mailboxid
             WHERE m.threadid = ? AND m.is_draft = 0
             ORDER BY m.sent_at, m.messageid',
            [$threadId]
        );
        $messages = [];

        for ($i = 0, $count = $adb->num_rows($result); $i < $count; $i++) {
            $messages[] = $adb->raw_query_result_rowdata($result, $i);
        }

        return $messages;
    }

    /**
     * Loads a mailbox row (encrypted secrets) for the attachment transport.
     *
     * @param int $mailboxId df_mailmanager_mailbox id
     *
     * @return array|null
     */
    private static function getMailbox(int $mailboxId): ?array
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery('SELECT * FROM df_mailmanager_mailbox WHERE mailboxid = ?', [$mailboxId]);

        return $adb->num_rows($result) ? $adb->raw_query_result_rowdata($result, 0) : null;
    }

    /**
     * The live ITS4YouEmails record carrying this message key — Basic (or an
     * earlier Pro link) may have filed the same mail already.
     *
     * @param string $messageKey md5 of the Message-ID
     *
     * @return int record id, 0 when none
     */
    private static function getEmailRecordIdByKey(string $messageKey): int
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            'SELECT e.its4you_emails_id FROM its4you_emails e
             INNER JOIN vtiger_crmentity ce ON ce.crmid = e.its4you_emails_id AND ce.deleted = 0
             WHERE e.mail_message_key = ? ORDER BY e.its4you_emails_id LIMIT 1',
            [$messageKey]
        );

        return $adb->num_rows($result) ? (int)$adb->query_result($result, 0, 'its4you_emails_id') : 0;
    }

    /**
     * Checks that a remembered e-mail record still exists.
     *
     * @param int $emailId ITS4YouEmails record id
     *
     * @return bool
     */
    private static function isLiveEmailRecord(int $emailId): bool
    {
        if ($emailId <= 0) {
            return false;
        }

        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            'SELECT 1 FROM vtiger_crmentity WHERE crmid = ? AND setype = ? AND deleted = 0',
            [$emailId, self::EMAIL_MODULE]
        );

        return (bool)$adb->num_rows($result);
    }

    /**
     * Remembers the ITS4YouEmails record of a message (df_mailmanager_message.crm_emailid).
     *
     * @param int $messageId df_mailmanager_message id
     * @param int $emailId   ITS4YouEmails record id
     *
     * @return void
     */
    private static function saveMessageEmailId(int $messageId, int $emailId): void
    {
        PearDatabase::getInstance()->pquery(
            'UPDATE df_mailmanager_message SET crm_emailid = ? WHERE messageid = ?',
            [$emailId, $messageId]
        );
    }
}
