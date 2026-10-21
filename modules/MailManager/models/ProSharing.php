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
 * Deputies of a personal mailbox — the owner (or the admin) lets other
 * users work in it, e.g. over a holiday: they read, reply, move and link
 * the mail like the owner, but never see the owner's settings. The grants
 * live in df_mailmanager_mailbox_access next to the shared mailbox
 * members; an optional end date closes the access by itself
 */
class MailManager_ProSharing_Model
{
    /**
     * SQL condition: the user works in the mailbox — owns it (the personal
     * one, and the shared one as well: turning the own mailbox into a team
     * mailbox must not lock its owner out), or is a member of the shared
     * one / a deputy of the personal one whose access has not ended
     *
     * @param string $alias mailbox table alias
     * @param int $userId
     * @return array [SQL condition, parameters]
     */
    public static function accessCondition($alias, $userId)
    {
        return [
            "({$alias}.owner_userid = ?
              OR EXISTS (SELECT 1 FROM df_mailmanager_mailbox_access a
                         WHERE a.mailboxid = {$alias}.mailboxid AND a.member_type = 'user' AND a.member_id = ?
                           AND (a.valid_until IS NULL OR a.valid_until >= ?)))",
            [(int)$userId, (int)$userId, date('Y-m-d')],
        ];
    }

    /**
     * @param int $mailboxId
     * @return array deputies: userid, user_name, valid_until, valid_display, expired
     */
    public static function getDeputies($mailboxId)
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            "SELECT member_id, valid_until FROM df_mailmanager_mailbox_access
             WHERE mailboxid = ? AND member_type = 'user'",
            [$mailboxId]
        );

        $deputies = [];
        for ($i = 0, $count = $adb->num_rows($result); $i < $count; $i++) {
            $validUntil = (string)$adb->query_result($result, $i, 'valid_until');
            $deputies[] = [
                'userid' => (int)$adb->query_result($result, $i, 'member_id'),
                'user_name' => trim((string)getUserFullName((int)$adb->query_result($result, $i, 'member_id'))),
                'valid_until' => $validUntil,
                'valid_display' => $validUntil !== '' ? DateTimeField::convertToUserFormat($validUntil) : '',
                'expired' => $validUntil !== '' && $validUntil < date('Y-m-d'),
            ];
        }
        usort($deputies, function ($a, $b) {
            return strcasecmp($a['user_name'], $b['user_name']);
        });

        return $deputies;
    }

    /**
     * Lets a user work in the personal mailbox; granting it again changes the end date
     *
     * @param array $mailbox df_mailmanager_mailbox row (personal)
     * @param int $userId the deputy
     * @param string $validUntil last day in the user's date format, '' = until revoked
     * @return void
     * @throws Exception on an invalid deputy or date
     */
    public static function grant(array $mailbox, $userId, $validUntil)
    {
        $userId = (int)$userId;
        if ($mailbox['mailbox_type'] !== 'personal' || $userId === (int)$mailbox['owner_userid']
            || !array_key_exists($userId, get_user_array(false))) {
            throw new Exception(vtranslate('LBL_SHARING_INVALID_USER', 'Settings:MailManager'));
        }

        $dbDate = null;
        if (trim((string)$validUntil) !== '') {
            $dbDate = DateTimeField::convertToDBFormat(trim((string)$validUntil));
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)$dbDate) || $dbDate < date('Y-m-d')) {
                throw new Exception(vtranslate('LBL_SHARING_INVALID_DATE', 'Settings:MailManager'));
            }
        }

        PearDatabase::getInstance()->pquery(
            "INSERT INTO df_mailmanager_mailbox_access (mailboxid, member_id, member_type, valid_until)
             VALUES (?,?,'user',?)
             ON DUPLICATE KEY UPDATE valid_until = VALUES(valid_until)",
            [(int)$mailbox['mailboxid'], $userId, $dbDate]
        );
    }

    /**
     * @param int $mailboxId personal mailbox
     * @param int $userId the deputy
     * @return void
     */
    public static function revoke($mailboxId, $userId)
    {
        PearDatabase::getInstance()->pquery(
            "DELETE a FROM df_mailmanager_mailbox_access a
             INNER JOIN df_mailmanager_mailbox m ON m.mailboxid = a.mailboxid
             WHERE a.mailboxid = ? AND a.member_id = ? AND a.member_type = 'user' AND m.mailbox_type = 'personal'",
            [(int)$mailboxId, (int)$userId]
        );
    }

    /**
     * Users the owner can pick as a deputy — active users without the owner
     *
     * @param int $ownerId
     * @return array userid => name
     */
    public static function getUserOptions($ownerId)
    {
        $users = get_user_array(false);
        unset($users[(int)$ownerId]);
        asort($users);

        return $users;
    }

    /**
     * The sender name of an outgoing mail: a deputy writes from the owner's
     * mailbox (the replies come back there) under the own name
     *
     * @param array $mailbox df_mailmanager_mailbox row
     * @param int $userId the sending user
     * @return string
     */
    public static function getSenderName(array $mailbox, $userId)
    {
        if ($mailbox['mailbox_type'] === 'personal' && (int)$mailbox['owner_userid'] !== (int)$userId) {
            $name = trim((string)getUserFullName((int)$userId));
            if ($name !== '') {
                return $name;
            }
        }

        return (string)$mailbox['name'];
    }
}
