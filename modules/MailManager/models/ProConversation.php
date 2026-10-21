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
 * The opened conversation of the Pro inbox: its head (driving record, state, the folders
 * its mail lies in, junk) and the timeline of its mail and ticket replies.
 */
class MailManager_ProConversation_Model
{
    /**
     * Everything the conversation template shows.
     *
     * @param array $thread  thread row of an accessible mailbox
     * @param array $mailbox accessible mailbox row
     *
     * @return array template variables
     */
    public static function getViewData(array $thread, array $mailbox): array
    {
        $mailboxId = (int)$mailbox['mailboxid'];
        $thread = self::getHead($thread, $mailboxId);
        $threadId = (int)$thread['threadid'];
        $isJunkOnly = MailManager_ProThread_Model::isJunkOnly($threadId);
        $triageEnabled = MailManager_ProAiConfig_Model::isFeatureEnabled(MailManager_ProAiConfig_Model::FEATURE_TRIAGE);

        return [
            'THREAD' => $thread,
            // who the conversation can be handed to – a shared mailbox only
            'ASSIGNEES' => $thread['state'] ? MailManager_ProThreadState_Model::getAssignees($mailbox) : [],
            'JUNK_TYPE' => self::getJunkType($thread, $isJunkOnly),
            // the global labels and, for a personal mailbox, its owner's own
            'LABELS' => Settings_MailManager_Label_Model::getForMailbox($mailboxId),
            'PRIORITIES' => MailManager_ProAiTriage_Model::PRIORITIES,
            'FOLDERS' => MailManager_ProFolder_Model::getFolders($mailboxId),
            'TRIAGE_PENDING' => in_array($thread['triage_state'], ['none', 'skipped', 'failed'], true) && $triageEnabled && !$isJunkOnly,
            'AI_SUMMARY_ENABLED' => MailManager_ProAiConfig_Model::isFeatureEnabled(MailManager_ProAiConfig_Model::FEATURE_SUMMARY),
            // received messages offer a translation when the conversation is not in the reader's language
            'TRANSLATE_OFFERED' => MailManager_ProTranslation_Model::isOffered($thread),
            // newest first; the customer's latest message opens, newer mail of ours shows as bars above it
            'MESSAGES' => MailManager_ProConversation_Helper::prepareMessages(
                MailManager_ProMailText_Model::getFramedMessages(
                    array_reverse(self::getTimeline($threadId)),
                    (string)vglobal('site_URL')
                )
            ),
        ];
    }

    /**
     * The head of the conversation: the DRIVING record's state + owner (read-only) or,
     * without a record, the conversation's own state and owner, the folders outside the
     * Inbox its mail lies in and the folder it was split from.
     *
     * @param array $thread    thread row
     * @param int   $mailboxId
     *
     * @return array thread row with driving, state, locations and split_target
     */
    public static function getHead(array $thread, int $mailboxId): array
    {
        $threadId = (int)$thread['threadid'];
        // the head shows the DRIVING record's state + owner (read-only) ...
        $threadRows = [$thread];
        MailManager_ProThreadList_Model::attachDrivingRecords($threadRows);
        // ... or, without a record, the conversation's own state and owner
        MailManager_ProThreadState_Model::attach($threadRows);
        $thread = $threadRows[0];
        // ... and the folders outside the Inbox its mail lies in (a click opens one)
        $locations = MailManager_ProFolder_Model::getThreadLocations($mailboxId, [$threadId]);
        $thread['locations'] = $locations[$threadId] ?? [];
        // a conversation filed elsewhere got new mail into the Inbox – the head says so
        // and offers to move it to the rest
        $thread['split_target'] = $thread['locations']
            && !in_array($thread['locations'][0]['folder_type'], ['trash', 'spam'], true)
            && MailManager_ProFolder_Model::hasInboxMail($threadId)
            ? $thread['locations'][0]
            : null;

        return $thread;
    }

    /**
     * All of the conversation thrown away – the head offers the way back instead of "Spam".
     *
     * @param array $thread     thread row with locations
     * @param bool  $isJunkOnly all of its mail lies in Spam / Trash
     *
     * @return string spam | trash | '' when it is not junk only
     */
    public static function getJunkType(array $thread, bool $isJunkOnly): string
    {
        if ($thread['locations'] && $isJunkOnly) {
            return in_array('spam', array_column($thread['locations'], 'folder_type'), true) ? 'spam' : 'trash';
        }

        return '';
    }

    /**
     * The conversation in time order – its mail and the replies written as ticket
     * comments (they never pass through the mailbox).
     *
     * @param int $threadId
     *
     * @return array oldest first
     */
    public static function getTimeline(int $threadId): array
    {
        $timeline = array_merge(
            MailManager_ProThread_Model::getThreadMessages($threadId),
            MailManager_ProTicketReply_Model::getNotes($threadId)
        );
        // the position breaks ties – a stable order of rows with the same time
        $positions = array_keys($timeline);
        usort($positions, function ($a, $b) use ($timeline) {
            return [(string)$timeline[$a]['sent_at'], $a] <=> [(string)$timeline[$b]['sent_at'], $b];
        });

        return array_map(function ($position) use ($timeline) {
            return $timeline[$position];
        }, $positions);
    }
}
