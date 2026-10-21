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
 * Browser notifications of new mail (ITS4YouMailManager 4.76) — the user's own
 * switch (My settings) and what the global header script asks about once a
 * minute: the received mail of the user's Workspace that came after the newest
 * message the browser knows. Read only, straight from the cache the sync fills.
 */
class MailManager_ProNotifier_Model
{
    /** Messages one answer names one by one — more come as one summary */
    public const SHOWN_ONE_BY_ONE = 3;

    /** Messages one answer looks at */
    private const LIMIT = 20;

    /**
     * @param int $userId
     *
     * @return bool the user switched the notifications on
     */
    public static function isEnabled(int $userId): bool
    {
        return '1' === MailManager_ProUserSetting_Model::get($userId, MailManager_ProUserSetting_Model::NOTIFY, '0');
    }

    /**
     * @param int  $userId
     * @param bool $enabled
     *
     * @return void
     */
    public static function setEnabled(int $userId, bool $enabled): void
    {
        MailManager_ProUserSetting_Model::set($userId, MailManager_ProUserSetting_Model::NOTIFY, $enabled ? '1' : '0');
    }

    /**
     * What the browser shows. The first question of a browser ($afterMessageId 0)
     * only learns where the mail stands now — the mail of yesterday is no news
     * after a login.
     *
     * @param int  $userId
     * @param int  $afterMessageId the newest message the browser already knows
     * @param bool $withCount      the unread count of the Workspace too (the inbox tab title)
     *
     * @return array ['enabled' => bool, 'cursor' => int, 'messages' => array, 'more' => string, 'unread' => int]
     */
    public static function getNews(int $userId, int $afterMessageId, bool $withCount): array
    {
        if (!self::isEnabled($userId)) {
            return ['enabled' => false];
        }

        $news = [
            'enabled' => true,
            'cursor' => self::getLatestMessageId(),
            'messages' => [],
            'more' => '',
        ];
        $messages = $afterMessageId > 0
            ? MailManager_ProThreadList_Model::getWorkspaceNewMessages($userId, $afterMessageId, self::LIMIT)
            : [];

        if (count($messages) > self::SHOWN_ONE_BY_ONE) {
            $news['more'] = sprintf(vtranslate('LBL_NOTIFY_MORE', 'Settings:MailManager'), count($messages));
        } else {
            foreach ($messages as $message) {
                $news['messages'][] = self::getNotification($message);
            }
        }

        if ($withCount) {
            $news['unread'] = MailManager_ProThreadList_Model::getWorkspaceUnreadCount(0, $userId);
        }

        return $news;
    }

    /**
     * @param array $message message row
     *
     * @return array ['id' => int, 'title' => string, 'body' => string, 'url' => string]
     */
    private static function getNotification(array $message): array
    {
        $sender = trim(decode_html((string)$message['from_name']));
        $subject = trim(decode_html((string)$message['subject']));

        return [
            'id' => (int)$message['messageid'],
            'title' => '' !== $sender ? $sender : (string)$message['from_email'],
            'body' => '' !== $subject ? $subject : vtranslate('LBL_NO_SUBJECT', 'MailManager'),
            'url' => 'index.php?module=MailManager&view=List&app=TOOLS&thread=' . (int)$message['threadid'],
        ];
    }

    /**
     * @return int the newest cached message of all mailboxes — where the browser starts
     */
    private static function getLatestMessageId(): int
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery('SELECT MAX(messageid) AS latest FROM df_mailmanager_message', []);

        return (int)$adb->query_result($result, 0, 'latest');
    }
}
