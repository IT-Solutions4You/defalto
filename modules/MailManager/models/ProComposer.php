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
 * Composer prefills — reply / reply all / forward, a continued draft and
 * the quoted original appended to the outgoing mail
 */
class MailManager_ProComposer_Model
{
    /**
     * Composer prefill for reply / reply all / forward
     *
     * @param array $thread thread row
     * @param string $ownEmail mailbox address
     * @param string $mode reply | replyall | forward
     * @return array ['to', 'cc', 'subject', 'quote_header', 'quote_html', 'quote_message', 'reply_message', 'thread', 'attachments']
     */
    public static function getComposerPrefill(array $thread, $ownEmail, $mode)
    {
        $messages = MailManager_ProThread_Model::getThreadMessages((int)$thread['threadid']);
        if (!$messages) {
            return [
                'to' => '', 'cc' => '', 'subject' => '', 'quote_header' => '', 'quote_html' => '',
                'quote_message' => 0, 'reply_message' => 0, 'draft_body' => '', 'draft_message' => 0,
                'thread' => (int)$thread['threadid'], 'attachments' => [],
            ];
        }

        // Reply target = the last inbound message (fallback: the very last one)
        $target = end($messages);
        for ($i = count($messages) - 1; $i >= 0; $i--) {
            if ($messages[$i]['direction'] === 'in') {
                $target = $messages[$i];
                break;
            }
        }

        $toAddresses = [];
        $ccAddresses = [];
        if ($mode === 'reply' || $mode === 'replyall') {
            if ($target['direction'] === 'in') {
                // the sender may name another address for the answer (a web
                // form mails from the site's address, the visitor is in Reply-To)
                $toAddresses[] = trim((string)($target['reply_to'] ?? '')) ?: $target['from_email'];
            } else {
                foreach (json_decode((string)$target['to_addrs'], true) ?: [] as $address) {
                    $toAddresses[] = $address['email'];
                }
            }
        }
        if ($mode === 'replyall') {
            foreach (['to_addrs', 'cc_addrs'] as $column) {
                foreach (json_decode((string)$target[$column], true) ?: [] as $address) {
                    $ccAddresses[] = $address['email'];
                }
            }
        }
        $filterOwn = function (array $emails) use ($ownEmail, $toAddresses) {
            return array_values(array_unique(array_filter($emails, function ($email) use ($ownEmail, $toAddresses) {
                return $email !== '' && strcasecmp($email, $ownEmail) !== 0 && !in_array($email, $toAddresses, true);
            })));
        };
        $toAddresses = array_values(array_unique(array_filter($toAddresses)));
        $ccAddresses = $filterOwn($ccAddresses);

        $cleanSubject = trim(preg_replace('/^(\s*(re|fw|fwd|aw|odp|sv)\s*(\[\d+\])?\s*:\s*)+/iu', '', (string)$thread['subject']));
        $subject = ($mode === 'forward' ? 'Fwd: ' : 'Re: ') . $cleanSubject;

        // The original mail is NOT loaded into the editor (CKEditor mangles
        // real-world mail HTML) — it renders below in a sandboxed frame and
        // the send action appends the quote to the outgoing mail server-side
        $quoteHeader = $target['time_display'] . ', ' . $target['from_display']
            . ($target['from_email'] !== '' ? ' <' . $target['from_email'] . '>' : '') . ':';
        $quoteHtml = $target['body_html'] !== ''
            ? $target['body_html']
            : nl2br(htmlspecialchars((string)$target['body_plain'], ENT_QUOTES, 'UTF-8'));

        // Forward offers the original attachments (checked by default)
        $attachments = [];
        if ($mode === 'forward') {
            foreach ($target['attachments'] as $attachment) {
                $attachments[] = [
                    'attachmentid' => (int)$attachment['attachmentid'],
                    'filename' => $attachment['filename'],
                    'size_display' => $attachment['size_display'],
                    'checked' => true,
                ];
            }
        }

        return [
            'to' => implode(', ', $toAddresses),
            'cc' => implode(', ', $ccAddresses),
            'subject' => $subject,
            'quote_header' => $quoteHeader,
            'quote_html' => MailManager_ProMailText_Model::frameHtml($quoteHtml),
            'quote_message' => (int)$target['messageid'],
            'reply_message' => $mode === 'forward' ? 0 : (int)$target['messageid'],
            'draft_body' => '',
            'draft_message' => 0,
            'thread' => (int)$thread['threadid'],
            'attachments' => $attachments,
        ];
    }

    /**
     * Composer prefill for CONTINUING a saved draft — recipients/subject/body
     * from the cached draft row; the reply context (quote preview, threading)
     * is rebuilt from the draft's In-Reply-To header
     *
     * @param int $draftId cached messageid of the draft
     * @param int $mailboxId ownership check
     * @return array|null composer prefill or null when not a draft of this mailbox
     */
    public static function getDraftComposerPrefill($draftId, $mailboxId)
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            'SELECT * FROM df_mailmanager_message WHERE messageid = ? AND mailboxid = ? AND is_draft = 1',
            [$draftId, $mailboxId]
        );
        if (!$adb->num_rows($result)) {
            return null;
        }
        $draft = $adb->raw_query_result_rowdata($result, 0);

        $addresses = function ($json) {
            $emails = [];
            foreach (json_decode((string)$json, true) ?: [] as $address) {
                if (!empty($address['email'])) {
                    $emails[] = $address['email'];
                }
            }

            return implode(', ', $emails);
        };

        // The quoted mail: the stored composer context (a forward has no
        // In-Reply-To), otherwise the replied message by In-Reply-To —
        // restores the quote preview and the threading
        $context = json_decode((string)$draft['draft_context'], true) ?: [];
        $quoteHeader = '';
        $quoteHtml = '';
        $replyMessageId = 0;
        $quoteMessageId = 0;
        $target = null;
        if (!empty($context['quote_message'])) {
            $targetResult = $adb->pquery(
                'SELECT messageid, from_name, from_email, sent_at, body_html, body_plain, header_message_id
                 FROM df_mailmanager_message WHERE threadid = ? AND messageid = ?',
                [(int)$draft['threadid'], (int)$context['quote_message']]
            );
        } elseif ((string)$draft['header_in_reply_to'] !== '') {
            $targetResult = $adb->pquery(
                'SELECT messageid, from_name, from_email, sent_at, body_html, body_plain, header_message_id
                 FROM df_mailmanager_message WHERE threadid = ? AND header_message_id = ? LIMIT 1',
                [(int)$draft['threadid'], $draft['header_in_reply_to']]
            );
        }
        if (isset($targetResult) && $adb->num_rows($targetResult)) {
            $target = $adb->raw_query_result_rowdata($targetResult, 0);
            $quoteMessageId = (int)$target['messageid'];
            // a reply continues the thread; a forward threads nothing
            $isReply = (string)$draft['header_in_reply_to'] !== ''
                && $draft['header_in_reply_to'] === $target['header_message_id'];
            $replyMessageId = $isReply ? $quoteMessageId : 0;
            $quoteHeader = MailManager_ProMailFormat_Model::formatDateTime($target['sent_at']) . ', '
                . (trim((string)$target['from_name']) !== '' ? $target['from_name'] : $target['from_email'])
                . ($target['from_email'] !== '' ? ' <' . $target['from_email'] . '>' : '') . ':';
            $quoteHtml = (string)$target['body_html'] !== ''
                ? $target['body_html']
                : nl2br(htmlspecialchars((string)$target['body_plain'], ENT_QUOTES, 'UTF-8'));
        }

        // A forward offers the original attachments again — the ones picked
        // when the draft was saved stay checked
        $attachments = [];
        if ($quoteMessageId && !$replyMessageId) {
            $picked = array_map('intval', $context['forward_attachments'] ?? []);
            $attachmentResult = $adb->pquery(
                'SELECT attachmentid, filename, size_bytes FROM df_mailmanager_attachment
                 WHERE messageid = ? AND is_inline = 0',
                [$quoteMessageId]
            );
            for ($i = 0, $count = $adb->num_rows($attachmentResult); $i < $count; $i++) {
                $attachment = $adb->raw_query_result_rowdata($attachmentResult, $i);
                $attachments[] = [
                    'attachmentid' => (int)$attachment['attachmentid'],
                    'filename' => $attachment['filename'],
                    'size_display' => MailManager_ProMailFormat_Model::formatSize($attachment['size_bytes']),
                    'checked' => in_array((int)$attachment['attachmentid'], $picked, true),
                ];
            }
        }

        return [
            'to' => $addresses($draft['to_addrs']),
            'cc' => $addresses($draft['cc_addrs']),
            'bcc' => $addresses($draft['bcc_addrs']),
            'subject' => (string)$draft['subject'],
            'draft_body' => (string)$draft['body_html'],
            'draft_message' => (int)$draft['messageid'],
            'quote_header' => $quoteHeader,
            'quote_html' => MailManager_ProMailText_Model::frameHtml($quoteHtml),
            'quote_message' => $quoteMessageId,
            'reply_message' => $replyMessageId,
            'thread' => (int)$draft['threadid'],
            'attachments' => $attachments,
        ];
    }

    /**
     * Everything the inline composer template shows for the given mode and prefill.
     *
     * @param string $mode    reply | replyall | forward | new | draft
     * @param array  $mailbox accessible mailbox row
     * @param array  $prefill composer prefill of the mode
     * @param int    $userId  current user
     *
     * @return array template variables
     */
    public static function getViewData(string $mode, array $mailbox, array $prefill, int $userId): array
    {
        $isReply = in_array($mode, ['reply', 'replyall'], true);
        $replyThreadId = $isReply && !empty($prefill['thread']) ? (int)$prefill['thread'] : 0;
        $aiDraftEnabled = 'new' !== $mode
            && MailManager_ProAiConfig_Model::isFeatureEnabled(MailManager_ProAiConfig_Model::FEATURE_DRAFT);
        // a reply on a ticket-linked thread starts generating on its own
        $autoDraft = $aiDraftEnabled && $replyThreadId && MailManager_ProRelate_Model::hasTicketLink($replyThreadId);
        // a reply on a ticket-linked conversation may go out as a ticket comment
        $replyTicket = $replyThreadId ? MailManager_ProTicketReply_Model::getReplyTicket($replyThreadId) : null;
        // only a continued draft carries blind copies
        $prefill['bcc'] = $prefill['bcc'] ?? '';
        $prefill['draft_body'] = self::getDraftBodyWithSignature($prefill, $mode);

        return [
            'COMPOSE_MODE' => $mode,
            'REPLY_TICKET' => $replyTicket,
            // an answer that settles it – "Send and mark done" for a conversation with a state of its own
            'CAN_MARK_DONE' => $replyThreadId && MailManager_ProThreadState_Model::hasOwnState($replyThreadId),
            'COMMENT_WORKFLOW' => $replyTicket && MailManager_ProTicketReply_Model::hasCommentWorkflow(),
            'MAILBOX_NAME' => $mailbox['name'],
            'MAILBOX_EMAIL' => $mailbox['email'],
            'AI_DRAFT_ENABLED' => $aiDraftEnabled,
            // the AI check of the reply before it goes out (AI settings)
            'DRAFT_CHECK' => MailManager_ProDraftCheck_Model::isEnabled(),
            // EMAIL Maker templates for the record the conversation is about
            'EMAIL_TEMPLATES' => MailManager_ProTemplate_Model::getTemplates((int)($prefill['thread'] ?? 0)),
            // "Send later" opens at the next full hour, in the user's time zone
            'SCHEDULE_DEFAULT' => MailManager_ProMailFormat_Model::utcToUserInput(gmdate('Y-m-d H:00:00', time() + 3600)),
            // "Undo" after Send — the user's delay in seconds (My settings)
            'SEND_DELAY' => (int)MailManager_ProUserSetting_Model::get($userId, MailManager_ProUserSetting_Model::SEND_DELAY, '0'),
            // what "Auto" stands for – the stored language of the conversation
            'AUTO_LANGUAGE' => $aiDraftEnabled && !empty($prefill['thread'])
                ? MailManager_ProThreadLanguage_Model::getDisplayName((int)$prefill['thread'])
                : '',
            'AI_AUTO_DRAFT' => $autoDraft,
            // the composer checks uploads against the same limit the server enforces
            'UPLOAD_MAX_BYTES' => MailManager_ProOutgoingAttachment_Model::getUploadLimit(),
            'PREFILL' => $prefill,
        ];
    }

    /**
     * Composer prefill of a new mail – nothing filled in yet.
     *
     * @return array
     */
    public static function getNewComposerPrefill(): array
    {
        return [
            'to' => '',
            'cc' => '',
            'subject' => '',
            'quote_header' => '',
            'quote_html' => '',
            'quote_message' => 0,
            'reply_message' => 0,
            'draft_body' => '',
            'draft_message' => 0,
            'thread' => 0,
            'attachments' => [],
        ];
    }

    /**
     * A new mail, a reply and a forward start with the user's signature under the place
     * to write (a continued draft carries its own text). Line breaks, not paragraphs:
     * the core editor runs with enterMode BR, an empty paragraph collapses there.
     *
     * @param array  $prefill composer prefill
     * @param string $mode    composer mode
     *
     * @return string
     */
    public static function getDraftBodyWithSignature(array $prefill, string $mode): string
    {
        $draftBody = (string)($prefill['draft_body'] ?? '');
        $signature = 'draft' === $mode ? '' : (string)MailManager_ProMySettings_Model::getSignature();

        if ('' !== $signature && '' === trim($draftBody)) {
            return str_repeat('<br>', 3) . '<div class="its4you-signature">' . $signature . '</div>';
        }

        return $draftBody;
    }

    /**
     * Quote block appended to an OUTGOING mail at send time — the recipient
     * still gets the standard mail history below the reply
     *
     * @param int $messageId quoted message
     * @param int $mailboxId ownership check
     * @return string HTML block, '' when the message does not belong here
     */
    public static function getQuoteBlockForMessage($messageId, $mailboxId)
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            'SELECT from_name, from_email, sent_at, body_html, body_plain
             FROM df_mailmanager_message WHERE messageid = ? AND mailboxid = ?',
            [$messageId, $mailboxId]
        );
        if (!$adb->num_rows($result)) {
            return '';
        }
        $message = $adb->raw_query_result_rowdata($result, 0);
        // the mail's own time is UTC, the core field expects a CRM time
        $dateTime = new DateTimeField(MailManager_ProMailFormat_Model::utcToDbTime($message['sent_at']));
        $header = $dateTime->getDisplayDateTimeValue() . ', '
            . (trim((string)$message['from_name']) !== '' ? $message['from_name'] : $message['from_email'])
            . ($message['from_email'] !== '' ? ' <' . $message['from_email'] . '>' : '') . ':';
        $html = (string)$message['body_html'] !== ''
            ? self::quotableHtml($message['body_html'])
            : nl2br(htmlspecialchars((string)$message['body_plain'], ENT_QUOTES, 'UTF-8'));

        return '<br><br><div>' . htmlspecialchars($header, ENT_QUOTES, 'UTF-8') . '</div>'
            . '<blockquote style="border-left: 2px solid #ccc; padding-left: 10px; margin-left: 4px;">'
            . $html . '</blockquote>';
    }

    /**
     * The content of a mail to quote under a reply — its body only. A mail is
     * a whole HTML document; its style sheet ("P {margin:0}" of Outlook)
     * pasted into the reply ruled the WHOLE reply — the paragraphs of our own
     * text lost their gaps. The quote keeps its inline styles only
     *
     * @param string $html
     * @return string
     */
    private static function quotableHtml($html)
    {
        $html = (string)$html;
        if (preg_match('~<body\b[^>]*>(.*)</body>~is', $html, $body)) {
            $html = $body[1];
        }

        return preg_replace(
            ['~<style\b[^>]*>.*?</style>~is', '~<head\b[^>]*>.*?</head>~is', '~</?(html|body|head|title|meta|link)\b[^>]*>~i',
                '~<!DOCTYPE[^>]*>~i'],
            '',
            $html
        );
    }
}
