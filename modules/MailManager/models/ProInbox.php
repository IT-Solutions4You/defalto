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
 * Access layer of the mail client — the mailboxes a user may reach and
 * a thread resolved through one of them; every action starts here
 */
class MailManager_ProInbox_Model
{
    /**
     * Our tables are utf8mb4 (emoji in subjects/bodies/names), but the vtiger
     * connection runs "SET NAMES utf8" (3-byte) which turns 4-byte characters
     * into '?' on both write and read. Requests touching mail data upgrade the
     * connection charset — utf8mb4 is a superset, the rest of the request is
     * unaffected
     *
     * @return void
     */
    public static function useUtf8mb4()
    {
        static $done = false;
        if (!$done) {
            PearDatabase::getInstance()->pquery('SET NAMES utf8mb4', []);
            $done = true;
        }
    }

    /**
     * Mailboxes visible to a user: own personal ones + shared where member +
     * personal ones the owner shared with the user (a deputy). Other
     * personal mailboxes are never listed (not even for admins).
     *
     * @param int $userId
     * @return array
     */
    public static function getMailboxesForUser($userId)
    {
        list($accessSql, $accessParams) = MailManager_ProSharing_Model::accessCondition('m', $userId);
        $adb = PearDatabase::getInstance();
        // the default goes first — the owner's flag does not count for a deputy
        $result = $adb->pquery(
            "SELECT m.* FROM df_mailmanager_mailbox m
             WHERE m.status = 'active' AND " . $accessSql . "
             ORDER BY (m.is_default = 1 AND (m.mailbox_type != 'personal' OR m.owner_userid = ?)) DESC, m.name",
            array_merge($accessParams, [$userId])
        );

        $rows = [];
        $count = $adb->num_rows($result);
        for ($i = 0; $i < $count; $i++) {
            $rows[] = $adb->raw_query_result_rowdata($result, $i);
        }

        return $rows;
    }

    /**
     * @param int $mailboxId
     * @param int $userId
     * @return array|null mailbox row when the user may access it
     */
    public static function getAccessibleMailbox($mailboxId, $userId)
    {
        foreach (self::getMailboxesForUser($userId) as $mailbox) {
            if ((int)$mailbox['mailboxid'] === (int)$mailboxId) {
                return $mailbox;
            }
        }

        return null;
    }

    /**
     * @param int $threadId
     * @param int $mailboxId safety scope
     * @return array|null thread row (+ label_name / label_color / label_by_rule)
     */
    public static function getThread($threadId, $mailboxId)
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            'SELECT t.*, l.name AS label_name, l.color AS label_color
             FROM df_mailmanager_thread t
             LEFT JOIN df_mailmanager_label l ON l.labelid = t.labelid
             WHERE t.threadid = ? AND t.mailboxid = ?',
            [$threadId, $mailboxId]
        );
        if (!$adb->num_rows($result)) {
            return null;
        }
        $thread = $adb->raw_query_result_rowdata($result, 0);
        // the current label came from a learned rule (not changed by hand since)
        $payload = json_decode((string)$thread['triage_payload'], true);
        $thread['label_by_rule'] = !empty($thread['labelid'])
            && (int)($payload['label_rule']['labelid'] ?? 0) === (int)$thread['labelid'];

        return $thread;
    }

    /**
     * Everything the Pro inbox page shows in its sidebar for the user: the mailboxes,
     * the selected one with its folders, labels, priorities and counters.
     *
     * @param int $userId      current user
     * @param int $requestedId mailbox id from the URL, 0 = none
     *
     * @return array template variables
     */
    public static function getIndexViewData(int $userId, int $requestedId): array
    {
        $mailboxes = self::getMailboxesForUser($userId);
        $selectedMailbox = self::getSelectedMailbox($mailboxes, $requestedId);
        $mailboxId = $selectedMailbox ? (int)$selectedMailbox['mailboxid'] : 0;
        $folders = $mailboxId ? MailManager_ProFolder_Model::getFolders($mailboxId) : [];

        return [
            'SETUP_PENDING' => Settings_MailManager_SetupWizard_Model::isShownToCurrentUser(),
            'MAILBOXES' => $mailboxes,
            'SELECTED_MAILBOX' => $selectedMailbox,
            'FOLDERS' => $folders,
            'SIDEBAR_LABELS' => $mailboxId ? MailManager_ProThreadList_Model::getSidebarLabels($mailboxId) : [],
            'PRIORITY_COUNTS' => $mailboxId ? MailManager_ProThreadList_Model::getPriorityCounts($mailboxId) : [],
            'DEFAULT_FOLDER_ID' => MailManager_ProFolder_Model::getDefaultFolderId($folders),
            'WORKSPACE_UNREAD' => $mailboxId ? MailManager_ProThreadList_Model::getWorkspaceUnreadCount($mailboxId, $userId) : 0,
            // the list tabs and the one the user opens the inbox with (My settings)
            'LIST_TABS' => MailManager_ProThreadList_Model::LIST_FILTERS,
            'DEFAULT_LIST_TAB' => MailManager_ProThreadList_Model::getDefaultListTab($userId),
            // mail waiting for "Send later" — the sidebar entry
            'SCHEDULED_COUNT' => MailManager_ProScheduled_Model::countForUser($userId),
            // the AI assistant tab of the right column
            'AI_ASSISTANT_ENABLED' => MailManager_ProAssistant_Model::isEnabled(),
            // the AI sorts new mail – Refresh shows its sorting window
            'TRIAGE_ENABLED' => MailManager_ProAiConfig_Model::isFeatureEnabled(MailManager_ProAiConfig_Model::FEATURE_TRIAGE),
            'MY_SETTINGS_TABS' => MailManager_ProMySettings_Model::getTabs($userId),
            // the main AI is down and the backup runs (I17)
            'AI_OUTAGE' => MailManager_ProAiFailover_Model::describeOutage(),
        ];
    }

    /**
     * Returns the mailbox requested in the URL when the user may open it, otherwise
     * the first (default) one.
     *
     * @param array $mailboxes   mailboxes of the user (default first)
     * @param int   $requestedId mailbox id from the request, 0 = none
     *
     * @return array|null
     */
    public static function getSelectedMailbox(array $mailboxes, int $requestedId): ?array
    {
        foreach ($mailboxes as $mailbox) {
            if ($requestedId && (int)$mailbox['mailboxid'] === $requestedId) {
                return $mailbox;
            }
        }

        return $mailboxes[0] ?? null;
    }
}
